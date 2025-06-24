<?php

namespace App\Http\Controllers;

use App\Models\Feeding;
use App\Models\FishBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FeedingController extends Controller
{
    public function index()
    {
        $feedings = Feeding::with(['fishBatch.pond.branch', 'fishBatch.fishType', 'creator'])
            ->when(request('search'), function($query) {
                $query->whereHas('fishBatch.pond', function($q) {
                    $q->where('name', 'like', '%' . request('search') . '%');
                })->orWhere('feed_type', 'like', '%' . request('search') . '%');
            })
            ->when(request('batch_id'), function($query) {
                $query->where('fish_batch_id', request('batch_id'));
            })
            ->when(request('feed_type'), function($query) {
                $query->where('feed_type', request('feed_type'));
            })
            ->when(request('date_from'), function($query) {
                $query->whereDate('date', '>=', request('date_from'));
            })
            ->when(request('date_to'), function($query) {
                $query->whereDate('date', '<=', request('date_to'));
            })
            ->latest('date')
            ->paginate(15);

        // Tambahkan perhitungan feeding data
        foreach ($feedings as $feeding) {
            $feeding->feeding_data = [
                'feeding_rate' => $feeding->feeding_rate,
                'feed_per_fish' => $feeding->feed_per_fish,
                'feed_cost' => $feeding->feed_cost,
                'batch_age_days' => $feeding->fishBatch->age_in_days,
                'current_stock' => $feeding->fishBatch->current_stock,
            ];
        }

        $batches = FishBatch::with(['pond', 'fishType'])->get();
        $feedTypes = Feeding::distinct('feed_type')->pluck('feed_type')->filter();

        // Statistik ringkasan
        $statistics = [
            'total_feed_today' => Feeding::whereDate('date', today())->sum('feed_amount_kg'),
            'total_feed_this_week' => Feeding::whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])->sum('feed_amount_kg'),
            'total_feed_this_month' => Feeding::whereMonth('date', now()->month)->sum('feed_amount_kg'),
            'total_feed_cost_month' => Feeding::whereMonth('date', now()->month)->sum('feed_cost'),
            'average_fcr' => $this->calculateAverageFCR(),
        ];

        return view('feedings.index', compact('feedings', 'batches', 'feedTypes', 'statistics'));
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

        $feedTypes = [
            'Pelet Starter',
            'Pelet Grower',
            'Pelet Finisher',
            'Pakan Alami',
            'Pakan Tambahan'
        ];

        return view('feedings.create', compact('batches', 'feedTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fish_batch_id' => 'required|exists:fish_batches,id',
            'date' => 'required|date|before_or_equal:today',
            'feed_type' => 'required|string|max:100',
            'feed_amount_kg' => 'required|numeric|min:0.1',
            'feed_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'documentation_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        if ($request->hasFile('documentation_file')) {
            $validated['documentation_file'] = $request->file('documentation_file')
                ->store('feeding-documentation', 'public');
        }

        $validated['created_by'] = Auth::id();

        Feeding::create($validated);

        return redirect()->route('feedings.index')
            ->with('success', 'Data pemberian pakan berhasil ditambahkan');
    }

    public function show(Feeding $feeding)
    {
        $feeding->load(['fishBatch.pond.branch', 'fishBatch.fishType', 'creator']);

        // Analisis pemberian pakan
        $analysis = [
            'feeding_data' => [
                'feed_amount_kg' => $feeding->feed_amount_kg,
                'feeding_rate' => $feeding->feeding_rate,
                'feed_per_fish' => $feeding->feed_per_fish,
                'feed_cost' => $feeding->feed_cost,
                'cost_per_kg' => $feeding->cost_per_kg,
            ],
            'batch_context' => [
                'batch_age_days' => $feeding->fishBatch->age_in_days,
                'current_stock' => $feeding->fishBatch->current_stock,
                'total_feed_to_date' => $feeding->fishBatch->total_feed_given,
                'current_fcr' => $feeding->fishBatch->fcr,
                'estimated_biomass' => $feeding->fishBatch->current_biomass,
            ],
            'recommendations' => $this->getFeedingRecommendations($feeding),
        ];

        // Riwayat pemberian pakan batch
        $feedingHistory = $feeding->fishBatch->feedings()
            ->orderBy('date', 'desc')
            ->limit(10)
            ->get();

        // Trend pemberian pakan
        $feedingTrend = $this->getFeedingTrend($feeding->fishBatch);

        return view('feedings.show', compact('feeding', 'analysis', 'feedingHistory', 'feedingTrend'));
    }

    public function edit(Feeding $feeding)
    {
        $batches = FishBatch::with(['pond.branch', 'fishType'])->get();
        $feedTypes = [
            'Pelet Starter',
            'Pelet Grower',
            'Pelet Finisher',
            'Pakan Alami',
            'Pakan Tambahan'
        ];

        return view('feedings.edit', compact('feeding', 'batches', 'feedTypes'));
    }

    public function update(Request $request, Feeding $feeding)
    {
        $validated = $request->validate([
            'fish_batch_id' => 'required|exists:fish_batches,id',
            'date' => 'required|date|before_or_equal:today',
            'feed_type' => 'required|string|max:100',
            'feed_amount_kg' => 'required|numeric|min:0.1',
            'feed_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'documentation_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        if ($request->hasFile('documentation_file')) {
            // Hapus file lama jika ada
            if ($feeding->documentation_file) {
                Storage::disk('public')->delete($feeding->documentation_file);
            }

            $validated['documentation_file'] = $request->file('documentation_file')
                ->store('feeding-documentation', 'public');
        }

        $feeding->update($validated);

        return redirect()->route('feedings.show', $feeding)
            ->with('success', 'Data pemberian pakan berhasil diperbarui');
    }

    public function destroy(Feeding $feeding)
    {
        // Hapus file dokumentasi jika ada
        if ($feeding->documentation_file) {
            Storage::disk('public')->delete($feeding->documentation_file);
        }

        $feeding->delete();

        return redirect()->route('feedings.index')
            ->with('success', 'Data pemberian pakan berhasil dihapus');
    }

    public function bulkCreate(Request $request)
    {
        $validated = $request->validate([
            'batch_ids' => 'required|array',
            'batch_ids.*' => 'exists:fish_batches,id',
            'date' => 'required|date|before_or_equal:today',
            'feed_type' => 'required|string|max:100',
        ]);

        $batches = FishBatch::whereIn('id', $validated['batch_ids'])
            ->with(['pond.branch', 'fishType'])
            ->get();

        return view('feedings.bulk-create', compact('batches', 'validated'));
    }

    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'feedings' => 'required|array',
            'feedings.*.fish_batch_id' => 'required|exists:fish_batches,id',
            'feedings.*.date' => 'required|date|before_or_equal:today',
            'feedings.*.feed_type' => 'required|string|max:100',
            'feedings.*.feed_amount_kg' => 'required|numeric|min:0.1',
            'feedings.*.feed_cost' => 'nullable|numeric|min:0',
            'feedings.*.notes' => 'nullable|string',
        ]);

        $created = 0;

        foreach ($validated['feedings'] as $feedingData) {
            $feedingData['created_by'] = Auth::id();
            Feeding::create($feedingData);
            $created++;
        }

        return redirect()->route('feedings.index')
            ->with('success', "{$created} data pemberian pakan berhasil ditambahkan");
    }

    public function analytics()
    {
        // Data untuk dashboard analitik pemberian pakan
        $analytics = [
            'overview' => [
                'total_feed_today' => Feeding::whereDate('date', today())->sum('feed_amount_kg'),
                'total_feed_week' => Feeding::whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])->sum('feed_amount_kg'),
                'total_feed_month' => Feeding::whereMonth('date', now()->month)->sum('feed_amount_kg'),
                'total_cost_month' => Feeding::whereMonth('date', now()->month)->sum('feed_cost'),
                'average_fcr' => $this->calculateAverageFCR(),
            ],
            'trends' => [
                'daily_consumption' => $this->getDailyFeedConsumption(),
                'weekly_consumption' => $this->getWeeklyFeedConsumption(),
                'monthly_consumption' => $this->getMonthlyFeedConsumption(),
            ],
            'feed_types' => [
                'consumption_by_type' => $this->getFeedConsumptionByType(),
                'cost_by_type' => $this->getFeedCostByType(),
            ],
            'efficiency' => [
                'fcr_trends' => $this->getFCRTrends(),
                'feed_efficiency_by_batch' => $this->getFeedEfficiencyByBatch(),
            ],
            'costs' => [
                'monthly_costs' => $this->getMonthlyFeedCosts(),
                'cost_per_kg_trends' => $this->getCostPerKgTrends(),
            ]
        ];

        return view('feedings.analytics', compact('analytics'));
    }

    // Helper methods
    private function calculateAverageFCR()
    {
        $batches = FishBatch::with(['feedings', 'sales'])->get();
        $totalFCR = 0;
        $count = 0;

        foreach ($batches as $batch) {
            $fcr = $batch->fcr;
            if ($fcr > 0 && $fcr < 10) { // Filter FCR yang masuk akal
                $totalFCR += $fcr;
                $count++;
            }
        }

        return $count > 0 ? round($totalFCR / $count, 2) : 0;
    }

    private function getFeedingRecommendations($feeding)
    {
        $batch = $feeding->fishBatch;
        $recommendations = [];

        // Rekomendasi berdasarkan umur batch
        $ageInDays = $batch->age_in_days;
        if ($ageInDays < 30) {
            $recommendations[] = [
                'type' => 'age',
                'message' => 'Batch masih juvenile, gunakan pelet starter dengan protein tinggi (35-40%)',
                'priority' => 'info'
            ];
        } elseif ($ageInDays < 90) {
            $recommendations[] = [
                'type' => 'age',
                'message' => 'Batch dalam fase pertumbuhan, gunakan pelet grower dengan protein 28-32%',
                'priority' => 'info'
            ];
        } else {
            $recommendations[] = [
                'type' => 'age',
                'message' => 'Batch mendekati panen, gunakan pelet finisher dengan protein 25-28%',
                'priority' => 'info'
            ];
        }

        // Rekomendasi berdasarkan feeding rate
        $feedingRate = $feeding->feeding_rate;
        if ($feedingRate > 5) {
            $recommendations[] = [
                'type' => 'feeding_rate',
                'message' => 'Feeding rate tinggi (' . $feedingRate . '%), perhatikan kualitas air dan risiko overfeeding',
                'priority' => 'warning'
            ];
        } elseif ($feedingRate < 1) {
            $recommendations[] = [
                'type' => 'feeding_rate',
                'message' => 'Feeding rate rendah (' . $feedingRate . '%), pertimbangkan untuk meningkatkan pemberian pakan',
                'priority' => 'info'
            ];
        }

        // Rekomendasi berdasarkan FCR
        $fcr = $batch->fcr;
        if ($fcr > 2.5) {
            $recommendations[] = [
                'type' => 'fcr',
                'message' => 'FCR tinggi (' . $fcr . '), evaluasi kualitas pakan dan strategi pemberian pakan',
                'priority' => 'warning'
            ];
        } elseif ($fcr > 0 && $fcr < 1.2) {
            $recommendations[] = [
                'type' => 'fcr',
                'message' => 'FCR sangat baik (' . $fcr . '), pertahankan strategi pemberian pakan saat ini',
                'priority' => 'success'
            ];
        }

        // Rekomendasi berdasarkan biaya pakan
        $costPerKg = $feeding->cost_per_kg;
        if ($costPerKg > 15000) {
            $recommendations[] = [
                'type' => 'cost',
                'message' => 'Biaya pakan tinggi (Rp ' . number_format($costPerKg) . '/kg), evaluasi supplier alternatif',
                'priority' => 'warning'
            ];
        }

        return $recommendations;
    }

    private function getFeedingTrend($batch)
    {
        $feedings = $batch->feedings()->orderBy('date')->get();
        $trend = [];
        $cumulativeFeed = 0;

        foreach ($feedings as $feeding) {
            $cumulativeFeed += $feeding->feed_amount_kg;
            $trend[] = [
                'date' => $feeding->date->format('M d'),
                'daily_feed' => $feeding->feed_amount_kg,
                'cumulative_feed' => round($cumulativeFeed, 2),
                'feeding_rate' => $feeding->feeding_rate,
                'feed_type' => $feeding->feed_type,
                'cost' => $feeding->feed_cost,
            ];
        }

        return collect($trend);
    }

    private function getDailyFeedConsumption()
    {
        $consumption = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $amount = Feeding::whereDate('date', $date)->sum('feed_amount_kg');
            $cost = Feeding::whereDate('date', $date)->sum('feed_cost');

            $consumption[] = [
                'date' => $date->format('M d'),
                'amount' => round($amount, 2),
                'cost' => $cost,
            ];
        }

        return $consumption;
    }

    private function getWeeklyFeedConsumption()
    {
        $consumption = [];
        for ($i = 11; $i >= 0; $i--) {
            $startOfWeek = now()->subWeeks($i)->startOfWeek();
            $endOfWeek = now()->subWeeks($i)->endOfWeek();

            $amount = Feeding::whereBetween('date', [$startOfWeek, $endOfWeek])->sum('feed_amount_kg');
            $cost = Feeding::whereBetween('date', [$startOfWeek, $endOfWeek])->sum('feed_cost');

            $consumption[] = [
                'week' => $startOfWeek->format('M d') . ' - ' . $endOfWeek->format('M d'),
                'amount' => round($amount, 2),
                'cost' => $cost,
            ];
        }

        return $consumption;
    }

    private function getMonthlyFeedConsumption()
    {
        $consumption = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $amount = Feeding::whereYear('date', $month->year)
                ->whereMonth('date', $month->month)
                ->sum('feed_amount_kg');
            $cost = Feeding::whereYear('date', $month->year)
                ->whereMonth('date', $month->month)
                ->sum('feed_cost');

            $consumption[] = [
                'month' => $month->format('M Y'),
                'amount' => round($amount, 2),
                'cost' => $cost,
            ];
        }

        return $consumption;
    }

    private function getFeedConsumptionByType()
    {
        return Feeding::selectRaw('feed_type, SUM(feed_amount_kg) as total_amount, COUNT(*) as feeding_sessions')
            ->groupBy('feed_type')
            ->orderBy('total_amount', 'desc')
            ->get();
    }

    private function getFeedCostByType()
    {
        return Feeding::selectRaw('feed_type, SUM(feed_cost) as total_cost, AVG(feed_cost/feed_amount_kg) as avg_cost_per_kg')
            ->whereNotNull('feed_cost')
            ->where('feed_cost', '>', 0)
            ->groupBy('feed_type')
            ->orderBy('total_cost', 'desc')
            ->get();
    }

    private function getFCRTrends()
    {
        $trends = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);

            $batches = FishBatch::with(['feedings', 'sales'])
                ->whereHas('feedings', function ($query) use ($month) {
                    $query->whereYear('date', $month->year)
                        ->whereMonth('date', $month->month);
                })
                ->get();

            $totalFCR = 0;
            $count = 0;

            foreach ($batches as $batch) {
                $fcr = $batch->fcr;
                if ($fcr > 0 && $fcr < 10) {
                    $totalFCR += $fcr;
                    $count++;
                }
            }

            $avgFCR = $count > 0 ? round($totalFCR / $count, 2) : 0;

            $trends[] = [
                'month' => $month->format('M Y'),
                'avg_fcr' => $avgFCR,
                'batch_count' => $count,
            ];
        }

        return $trends;
    }

    private function getFeedEfficiencyByBatch()
    {
        return FishBatch::with(['pond.branch', 'fishType', 'feedings', 'sales'])
            ->get()
            ->filter(function ($batch) {
                return $batch->fcr > 0 && $batch->fcr < 10;
            })
            ->sortBy('fcr')
            ->take(20)
            ->map(function ($batch) {
                return [
                    'batch_id' => $batch->id,
                    'pond_name' => $batch->pond->name,
                    'branch_name' => $batch->pond->branch->name,
                    'fish_type' => $batch->fishType->name,
                    'fcr' => $batch->fcr,
                    'total_feed' => $batch->total_feed_given,
                    'total_harvest' => $batch->sales->sum('quantity_fish'),
                    'age_days' => $batch->age_in_days,
                ];
            });
    }

    private function getMonthlyFeedCosts()
    {
        $costs = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $totalCost = Feeding::whereYear('date', $month->year)
                ->whereMonth('date', $month->month)
                ->sum('feed_cost');
            $totalAmount = Feeding::whereYear('date', $month->year)
                ->whereMonth('date', $month->month)
                ->sum('feed_amount_kg');

            $avgCostPerKg = $totalAmount > 0 ? round($totalCost / $totalAmount, 2) : 0;

            $costs[] = [
                'month' => $month->format('M Y'),
                'total_cost' => $totalCost,
                'total_amount' => round($totalAmount, 2),
                'avg_cost_per_kg' => $avgCostPerKg,
            ];
        }

        return $costs;
    }

    private function getCostPerKgTrends()
    {
        $trends = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);

            $feedTypes = Feeding::selectRaw('feed_type, AVG(feed_cost/feed_amount_kg) as avg_cost_per_kg')
                ->whereYear('date', $month->year)
                ->whereMonth('date', $month->month)
                ->whereNotNull('feed_cost')
                ->where('feed_cost', '>', 0)
                ->groupBy('feed_type')
                ->get();

            $monthData = [
                'month' => $month->format('M Y'),
                'feed_types' => []
            ];

            foreach ($feedTypes as $feedType) {
                $monthData['feed_types'][$feedType->feed_type] = round($feedType->avg_cost_per_kg, 2);
            }

            $trends[] = $monthData;
        }

        return $trends;
    }
}
