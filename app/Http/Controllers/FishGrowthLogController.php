<?php

namespace App\Http\Controllers;

use App\Models\FishGrowthLog;
use App\Models\FishBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FishGrowthLogController extends Controller
{
    public function index()
    {
        $growthLogs = FishGrowthLog::with(['fishBatch.pond.branch', 'fishBatch.fishType', 'creator'])
            ->when(request('search'), function($query) {
                $query->whereHas('fishBatch.pond', function($q) {
                    $q->where('name', 'like', '%' . request('search') . '%');
                })->orWhereHas('fishBatch.fishType', function($q) {
                    $q->where('name', 'like', '%' . request('search') . '%');
                });
            })
            ->when(request('batch_id'), function($query) {
                $query->where('fish_batch_id', request('batch_id'));
            })
            ->when(request('week_number'), function($query) {
                $query->where('week_number', request('week_number'));
            })
            ->latest('date_recorded')
            ->paginate(15);

        // Tambahkan perhitungan pertumbuhan
        foreach ($growthLogs as $log) {
            $log->growth_data = [
                'weight_growth_from_previous' => $log->weight_growth_from_previous,
                'growth_percentage' => $log->growth_percentage,
                'age_in_days' => $log->fishBatch->age_in_days,
                'current_stock' => $log->fishBatch->current_stock,
            ];
        }

        $batches = FishBatch::with(['pond', 'fishType'])->get();
        $weekNumbers = range(1, 52);

        return view('fish-growth-logs.index', compact('growthLogs', 'batches', 'weekNumbers'));
    }

    public function create()
    {
        $batches = FishBatch::with(['pond.branch', 'fishType'])
            ->where(function($query) {
                $query->whereRaw('
                    (initial_count -
                     COALESCE((SELECT SUM(dead_count) FROM mortalities WHERE fish_batch_id = fish_batches.id AND deleted_at IS NULL), 0) -
                     COALESCE((SELECT SUM(quantity_fish) FROM sales WHERE fish_batch_id = fish_batches.id AND deleted_at IS NULL), 0)
                    ) > 0
                ');
            })
            ->get();

        return view('fish-growth-logs.create', compact('batches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fish_batch_id' => 'required|exists:fish_batches,id',
            'week_number' => 'required|integer|min:1|max:52',
            'avg_weight_gram' => 'required|numeric|min:0',
            'avg_length_cm' => 'required|numeric|min:0',
            'date_recorded' => 'required|date',
            'documentation_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        // Cek apakah sudah ada log untuk minggu yang sama
        $existingLog = FishGrowthLog::where('fish_batch_id', $validated['fish_batch_id'])
            ->where('week_number', $validated['week_number'])
            ->first();

        if ($existingLog) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Log pertumbuhan untuk minggu ke-' . $validated['week_number'] . ' sudah ada');
        }

        if ($request->hasFile('documentation_file')) {
            $validated['documentation_file'] = $request->file('documentation_file')
                ->store('growth-documentation', 'public');
        }

        $validated['created_by'] = Auth::id();

        FishGrowthLog::create($validated);

        return redirect()->route('fish-growth-logs.index')
            ->with('success', 'Log pertumbuhan berhasil ditambahkan');
    }

    public function show(FishGrowthLog $fishGrowthLog)
    {
        $fishGrowthLog->load(['fishBatch.pond.branch', 'fishBatch.fishType', 'creator']);

        // Data perbandingan dengan log sebelumnya dan sesudahnya
        $previousLog = FishGrowthLog::where('fish_batch_id', $fishGrowthLog->fish_batch_id)
            ->where('week_number', '<', $fishGrowthLog->week_number)
            ->orderBy('week_number', 'desc')
            ->first();

        $nextLog = FishGrowthLog::where('fish_batch_id', $fishGrowthLog->fish_batch_id)
            ->where('week_number', '>', $fishGrowthLog->week_number)
            ->orderBy('week_number', 'asc')
            ->first();

        // Perhitungan detail pertumbuhan
        $growthAnalysis = [
            'current' => [
                'weight' => $fishGrowthLog->avg_weight_gram,
                'length' => $fishGrowthLog->avg_length_cm,
                'week' => $fishGrowthLog->week_number,
                'age_days' => $fishGrowthLog->fishBatch->age_in_days,
            ],
            'comparison' => [
                'previous_weight' => $previousLog?->avg_weight_gram ?? 0,
                'previous_length' => $previousLog?->avg_length_cm ?? 0,
                'weight_growth' => $fishGrowthLog->weight_growth_from_previous,
                'length_growth' => $fishGrowthLog->length_growth_from_previous,
                'growth_percentage' => $fishGrowthLog->growth_percentage,
                'weekly_growth_rate' => $fishGrowthLog->weekly_growth_rate,
            ],
            'projections' => [
                'next_week_weight' => $nextLog?->avg_weight_gram ?? null,
                'projected_harvest_weight' => $this->calculateProjectedHarvestWeight($fishGrowthLog),
                'days_to_harvest' => $this->calculateDaysToHarvest($fishGrowthLog),
                'growth_trend' => $this->calculateGrowthTrend($fishGrowthLog),
            ]
        ];

        // Data untuk chart pertumbuhan batch
        $batchGrowthData = $this->getBatchGrowthData($fishGrowthLog->fishBatch);

        return view('fish-growth-logs.show', compact('fishGrowthLog', 'previousLog', 'nextLog', 'growthAnalysis', 'batchGrowthData'));
    }

    public function edit(FishGrowthLog $fishGrowthLog)
    {
        $batches = FishBatch::with(['pond.branch', 'fishType'])->get();

        return view('fish-growth-logs.edit', compact('fishGrowthLog', 'batches'));
    }

    public function update(Request $request, FishGrowthLog $fishGrowthLog)
    {
        $validated = $request->validate([
            'fish_batch_id' => 'required|exists:fish_batches,id',
            'week_number' => 'required|integer|min:1|max:52',
            'avg_weight_gram' => 'required|numeric|min:0',
            'avg_length_cm' => 'required|numeric|min:0',
            'date_recorded' => 'required|date',
            'documentation_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        // Cek duplikasi minggu (kecuali record saat ini)
        $existingLog = FishGrowthLog::where('fish_batch_id', $validated['fish_batch_id'])
            ->where('week_number', $validated['week_number'])
            ->where('id', '!=', $fishGrowthLog->id)
            ->first();

        if ($existingLog) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Log pertumbuhan untuk minggu ke-' . $validated['week_number'] . ' sudah ada');
        }

        if ($request->hasFile('documentation_file')) {
            // Hapus file lama jika ada
            if ($fishGrowthLog->documentation_file) {
                Storage::disk('public')->delete($fishGrowthLog->documentation_file);
            }

            $validated['documentation_file'] = $request->file('documentation_file')
                ->store('growth-documentation', 'public');
        }

        $fishGrowthLog->update($validated);

        return redirect()->route('fish-growth-logs.show', $fishGrowthLog)
            ->with('success', 'Log pertumbuhan berhasil diperbarui');
    }

    public function destroy(FishGrowthLog $fishGrowthLog)
    {
        // Hapus file dokumentasi jika ada
        if ($fishGrowthLog->documentation_file) {
            Storage::disk('public')->delete($fishGrowthLog->documentation_file);
        }

        $fishGrowthLog->delete();

        return redirect()->route('fish-growth-logs.index')
            ->with('success', 'Log pertumbuhan berhasil dihapus');
    }

    public function bulkCreate(Request $request)
    {
        $validated = $request->validate([
            'batch_ids' => 'required|array',
            'batch_ids.*' => 'exists:fish_batches,id',
            'week_number' => 'required|integer|min:1|max:52',
            'date_recorded' => 'required|date',
        ]);

        $batches = FishBatch::whereIn('id', $validated['batch_ids'])->get();

        return view('fish-growth-logs.bulk-create', compact('batches', 'validated'));
    }

    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'logs' => 'required|array',
            'logs.*.fish_batch_id' => 'required|exists:fish_batches,id',
            'logs.*.week_number' => 'required|integer|min:1|max:52',
            'logs.*.avg_weight_gram' => 'required|numeric|min:0',
            'logs.*.avg_length_cm' => 'required|numeric|min:0',
            'logs.*.date_recorded' => 'required|date',
        ]);

        $created = 0;
        $errors = [];

        foreach ($validated['logs'] as $logData) {
            // Cek duplikasi
            $existing = FishGrowthLog::where('fish_batch_id', $logData['fish_batch_id'])
                ->where('week_number', $logData['week_number'])
                ->first();

            if ($existing) {
                $batch = FishBatch::find($logData['fish_batch_id']);
                $errors[] = "Batch {$batch->pond->name} - minggu {$logData['week_number']} sudah ada";
                continue;
            }

            $logData['created_by'] = Auth::id();
            FishGrowthLog::create($logData);
            $created++;
        }

        $message = "{$created} log pertumbuhan berhasil ditambahkan";
        if (!empty($errors)) {
            $message .= ". Errors: " . implode(', ', $errors);
        }

        return redirect()->route('fish-growth-logs.index')
            ->with('success', $message);
    }

    // Helper methods
    private function calculateProjectedHarvestWeight($growthLog)
    {
        $batch = $growthLog->fishBatch;
        $currentWeight = $growthLog->avg_weight_gram;
        $weeklyGrowthRate = $growthLog->weekly_growth_rate;

        // Asumsi panen pada minggu ke-20 (sekitar 5 bulan)
        $targetWeek = 20;
        $weeksRemaining = max(0, $targetWeek - $growthLog->week_number);

        if ($weeklyGrowthRate <= 0) return $currentWeight;

        // Proyeksi dengan pertumbuhan eksponensial yang melambat
        $projectedWeight = $currentWeight;
        for ($i = 0; $i < $weeksRemaining; $i++) {
            $growthFactor = max(0.5, 1 - ($i * 0.05)); // Pertumbuhan melambat seiring waktu
            $projectedWeight += ($projectedWeight * ($weeklyGrowthRate / 100) * $growthFactor);
        }

        return round($projectedWeight, 2);
    }

    private function calculateDaysToHarvest($growthLog)
    {
        $targetWeight = 500; // gram, target berat panen
        $currentWeight = $growthLog->avg_weight_gram;
        $weeklyGrowthRate = $growthLog->weekly_growth_rate;

        if ($currentWeight >= $targetWeight) return 0;
        if ($weeklyGrowthRate <= 0) return null;

        // Perhitungan sederhana berdasarkan growth rate
        $weightNeeded = $targetWeight - $currentWeight;
        $weeklyGrowth = $currentWeight * ($weeklyGrowthRate / 100);

        if ($weeklyGrowth <= 0) return null;

        $weeksNeeded = $weightNeeded / $weeklyGrowth;
        return round($weeksNeeded * 7); // konversi ke hari
    }

    private function calculateGrowthTrend($growthLog)
    {
        $recentLogs = FishGrowthLog::where('fish_batch_id', $growthLog->fish_batch_id)
            ->where('week_number', '<=', $growthLog->week_number)
            ->orderBy('week_number', 'desc')
            ->limit(4)
            ->get();

        if ($recentLogs->count() < 2) return 'insufficient_data';

        $growthRates = [];
        for ($i = 0; $i < $recentLogs->count() - 1; $i++) {
            $current = $recentLogs[$i];
            $previous = $recentLogs[$i + 1];

            if ($previous->avg_weight_gram > 0) {
                $rate = (($current->avg_weight_gram - $previous->avg_weight_gram) / $previous->avg_weight_gram) * 100;
                $growthRates[] = $rate;
            }
        }

        if (empty($growthRates)) return 'insufficient_data';

        $avgGrowthRate = array_sum($growthRates) / count($growthRates);

        if ($avgGrowthRate > 15) return 'excellent';
        if ($avgGrowthRate > 10) return 'good';
        if ($avgGrowthRate > 5) return 'moderate';
        if ($avgGrowthRate > 0) return 'slow';
        return 'declining';
    }

    private function getBatchGrowthData($batch)
    {
        return $batch->fishGrowthLogs()
            ->orderBy('week_number')
            ->get()
            ->map(function ($log) {
                return [
                    'week' => $log->week_number,
                    'weight' => $log->avg_weight_gram,
                    'length' => $log->avg_length_cm,
                    'date' => $log->date_recorded->format('M d'),
                    'growth_rate' => $log->weekly_growth_rate,
                ];
            });
    }
}
