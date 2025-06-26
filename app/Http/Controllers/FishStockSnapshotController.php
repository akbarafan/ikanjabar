<?php

namespace App\Http\Controllers;

use App\Models\FishStockSnapshot;
use App\Models\FishBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FishStockSnapshotController extends Controller
{
    public function index()
    {git remote remove origin

        $snapshots = FishStockSnapshot::with(['fishBatch.pond.branch', 'fishBatch.fishType'])
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
            ->when(request('branch_id'), function($query) {
                $query->whereHas('fishBatch.pond', function($q) {
                    $q->where('branch_id', request('branch_id'));
                });
            })
            ->when(request('date_from'), function($query) {
                $query->whereDate('created_at', '>=', request('date_from'));
            })
            ->when(request('date_to'), function($query) {
                $query->whereDate('created_at', '<=', request('date_to'));
            })
            ->latest()
            ->paginate(15);

        // Tambahkan perhitungan stock variance dan status
        foreach ($snapshots as $snapshot) {
            $snapshot->stock_data = [
                'current_stock' => $snapshot->current_stock,
                'initial_stock' => $snapshot->fishBatch->initial_count,
                'stock_variance' => $snapshot->current_stock - $snapshot->fishBatch->initial_count,
                'stock_percentage' => $snapshot->fishBatch->initial_count > 0
                    ? round(($snapshot->current_stock / $snapshot->fishBatch->initial_count) * 100, 2)
                    : 0,
                'calculated_stock' => $snapshot->fishBatch->current_stock,
                'variance_from_calculated' => $snapshot->current_stock - $snapshot->fishBatch->current_stock,
                'batch_age_days' => $snapshot->fishBatch->age_in_days,
            ];
        }

        $batches = FishBatch::with(['pond.branch', 'fishType'])->get();
        $branches = DB::table('branches')->get();

        // Statistik ringkasan
        $statistics = [
            'total_snapshots_today' => FishStockSnapshot::whereDate('created_at', today())->count(),
            'total_snapshots_this_week' => FishStockSnapshot::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'total_snapshots_this_month' => FishStockSnapshot::whereMonth('created_at', now()->month)->count(),
            'total_current_stock' => FishStockSnapshot::latest('created_at')->get()->groupBy('fish_batch_id')->map->first()->sum('current_stock'),
            'stock_variance_alerts' => $this->getStockVarianceAlerts(),
        ];

        return view('fish-stock-snapex', compact('snapshots', 'batches', 'branches', 'statistics'));
    }shots.ind

    public function create()
    {
        $batches = FishBatch::with(['pond.branch', 'fishType'])
            ->whereRaw('
                (initial_count -
                 COALESCE((SELECT SUM(dead_count) FROM mortalities WHERE fish_batch_id = fish_batches.id AND deleted_at IS NULL), 0) -
                 COALESCE((SELECT SUM(quantity_fish) FROM sales WHERE fish_batch_id = fish_batches.id AND deleted_at IS NULL), 0)
                ) > 0
            ')
            ->get();

        return view('fish-stock-snapshots.create', compact('batches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fish_batch_id' => 'required|exists:fish_batches,id',
            'current_stock' => 'required|integer|min:0',
        ]);

        // Validasi current_stock tidak melebihi initial_count
        $batch = FishBatch::find($validated['fish_batch_id']);
        if ($validated['current_stock'] > $batch->initial_count) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Stok saat ini ({$validated['current_stock']}) tidak boleh melebihi stok awal ({$batch->initial_count})");
        }

        $snapshot = FishStockSnapshot::create($validated);

        // Alert jika ada perbedaan signifikan dengan perhitungan otomatis
        $calculatedStock = $batch->current_stock;
        $variance = abs($validated['current_stock'] - $calculatedStock);
        $variancePercentage = $calculatedStock > 0 ? ($variance / $calculatedStock) * 100 : 0;

        $alertMessage = 'Snapshot stok berhasil ditambahkan';
        $alertType = 'success';

        if ($variancePercentage > 10) {
            $alertMessage .= '. PERINGATAN: Perbedaan signifikan dengan perhitungan otomatis (' . round($variancePercentage, 2) . '%)!';
            $alertType = 'warning';
        }

        return redirect()->route('fish-stock-snapshots.index')
            ->with($alertType, $alertMessage);
    }

    public function show(FishStockSnapshot $fishStockSnapshot)
    {
        $fishStockSnapshot->load(['fishBatch.pond.branch', 'fishBatch.fishType']);

        // Analisis snapshot
        $analysis = [
            'snapshot_data' => [
                'current_stock' => $fishStockSnapshot->current_stock,
                'snapshot_date' => $fishStockSnapshot->created_at,
                'batch_age_at_snapshot' => $fishStockSnapshot->fishBatch->age_in_days,
            ],
            'batch_context' => [
                'initial_count' => $fishStockSnapshot->fishBatch->initial_count,
                'calculated_current_stock' => $fishStockSnapshot->fishBatch->current_stock,
                'total_deaths' => $fishStockSnapshot->fishBatch->mortalities()->sum('dead_count'),
                'total_sold' => $fishStockSnapshot->fishBatch->sales()->sum('quantity_fish'),
                'survival_rate' => $fishStockSnapshot->fishBatch->survival_rate,
            ],
            'variance_analysis' => [
                'stock_variance_from_initial' => $fishStockSnapshot->current_stock - $fishStockSnapshot->fishBatch->initial_count,
                'stock_percentage_of_initial' => $fishStockSnapshot->fishBatch->initial_count > 0
                    ? round(($fishStockSnapshot->current_stock / $fishStockSnapshot->fishBatch->initial_count) * 100, 2)
                    : 0,
                'variance_from_calculated' => $fishStockSnapshot->current_stock - $fishStockSnapshot->fishBatch->current_stock,
                'variance_percentage' => $fishStockSnapshot->fishBatch->current_stock > 0
                    ? round((abs($fishStockSnapshot->current_stock - $fishStockSnapshot->fishBatch->current_stock) / $fishStockSnapshot->fishBatch->current_stock) * 100, 2)
                    : 0,
            ]
        ];

        // Riwayat snapshot batch
        $snapshotHistory = $fishStockSnapshot->fishBatch->fishStockSnapshots()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Trend stok
        $stockTrend = $this->getStockTrend($fishStockSnapshot->fishBatch);

        return view('fish-stock-snapshots.show', compact('fishStockSnapshot', 'analysis', 'snapshotHistory', 'stockTrend'));
    }

    public function edit(FishStockSnapshot $fishStockSnapshot)
    {
        $batches = FishBatch::with(['pond.branch', 'fishType'])->get();

        return view('fish-stock-snapshots.edit', compact('fishStockSnapshot', 'batches'));
    }

    public function update(Request $request, FishStockSnapshot $fishStockSnapshot)
    {
        $validated = $request->validate([
            'fish_batch_id' => 'required|exists:fish_batches,id',
            'current_stock' => 'required|integer|min:0',
        ]);

        // Validasi current_stock tidak melebihi initial_count
        $batch = FishBatch::find($validated['fish_batch_id']);
        if ($validated['current_stock'] > $batch->initial_count) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Stok saat ini ({$validated['current_stock']}) tidak boleh melebihi stok awal ({$batch->initial_count})");
        }

        $fishStockSnapshot->update($validated);

        return redirect()->route('fish-stock-snapshots.show', $fishStockSnapshot)
            ->with('success', 'Snapshot stok berhasil diperbarui');
    }

    public function destroy(FishStockSnapshot $fishStockSnapshot)
    {
        $fishStockSnapshot->delete();

        return redirect()->route('fish-stock-snapshots.index')
            ->with('success', 'Snapshot stok berhasil dihapus');
    }

    public function analytics()
    {
        // Data untuk dashboard analitik snapshot stok
        $analytics = [
            'overview' => [
                'total_snapshots_today' => FishStockSnapshot::whereDate('created_at', today())->count(),
                'total_snapshots_week' => FishStockSnapshot::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'total_snapshots_month' => FishStockSnapshot::whereMonth('created_at', now()->month)->count(),
                'average_stock_accuracy' => $this->calculateAverageStockAccuracy(),
            ],
            'trends' => [
                'daily_snapshot_trend' => $this->getDailySnapshotTrend(),
                'stock_level_trends' => $this->getStockLevelTrends(),
                'variance_trends' => $this->getVarianceTrends(),
            ],
            'accuracy' => [
                'high_variance_snapshots' => $this->getHighVarianceSnapshots(),
                'accuracy_by_batch_age' => $this->getAccuracyByBatchAge(),
                'accuracy_by_fish_type' => $this->getAccuracyByFishType(),
            ],
            'alerts' => [
                'stock_variance_alerts' => $this->getStockVarianceAlerts(),
                'low_stock_alerts' => $this->getLowStockAlerts(),
            ]
        ];

        return view('fish-stock-snapshots.analytics', compact('analytics'));
    }

    public function batchSnapshots($batchId)
    {
        $batch = FishBatch::with(['pond.branch', 'fishType'])->findOrFail($batchId);

        $snapshots = FishStockSnapshot::where('fish_batch_id', $batchId)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $trend = $this->getStockTrend($batch);

        return view('fish-stock-snapshots.batch-snapshots', compact('batch', 'snapshots', 'trend'));
    }

    // Helper methods
    private function getStockVarianceAlerts()
    {
        return FishStockSnapshot::with(['fishBatch.pond', 'fishBatch.fishType'])
            ->get()
            ->filter(function ($snapshot) {
                $calculatedStock = $snapshot->fishBatch->current_stock;
                $variance = abs($snapshot->current_stock - $calculatedStock);
                $variancePercentage = $calculatedStock > 0 ? ($variance / $calculatedStock) * 100 : 0;
                return $variancePercentage > 10;
            })
            ->take(10);
    }

    private function getLowStockAlerts()
    {
        return FishStockSnapshot::with(['fishBatch.pond', 'fishBatch.fishType'])
            ->latest('created_at')
            ->get()
            ->groupBy('fish_batch_id')
            ->map->first()
            ->filter(function ($snapshot) {
                $stockPercentage = $snapshot->fishBatch->initial_count > 0
                    ? ($snapshot->current_stock / $snapshot->fishBatch->initial_count) * 100
                    : 0;
                return $stockPercentage < 20; // Alert jika stok < 20% dari awal
            })
            ->take(10);
    }

    private function getStockTrend($batch)
    {
        $snapshots = $batch->fishStockSnapshots()->orderBy('created_at')->get();
        $trend = [];

        foreach ($snapshots as $snapshot) {
            $stockPercentage = $batch->initial_count > 0
                ? round(($snapshot->current_stock / $batch->initial_count) * 100, 2)
                : 0;

            $trend[] = [
                'date' => $snapshot->created_at->format('M d'),
                'current_stock' => $snapshot->current_stock,
                'stock_percentage' => $stockPercentage,
                'calculated_stock' => $batch->current_stock,
                'variance' => $snapshot->current_stock - $batch->current_stock,
            ];
        }

        return collect($trend);
    }

    private function calculateAverageStockAccuracy()
    {
        $snapshots = FishStockSnapshot::with('fishBatch')->get();
        $totalAccuracy = 0;
        $count = 0;

        foreach ($snapshots as $snapshot) {
            $calculatedStock = $snapshot->fishBatch->current_stock;
            if ($calculatedStock > 0) {
                $accuracy = 100 - (abs($snapshot->current_stock - $calculatedStock) / $calculatedStock * 100);
                $totalAccuracy += max(0, $accuracy);
                $count++;
            }
        }

        return $count > 0 ? round($totalAccuracy / $count, 2) : 0;
    }

    private function getDailySnapshotTrend()
    {
        $trend = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = FishStockSnapshot::whereDate('created_at', $date)->count();

            $trend[] = [
                'date' => $date->format('M d'),
                'count' => $count,
            ];
        }

        return $trend;
    }
        private function getStockLevelTrends()
    {
        $trends = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);

            $snapshots = FishStockSnapshot::with('fishBatch')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->get();

            $avgStockLevel = $snapshots->avg(function($snapshot) {
                return $snapshot->fishBatch->initial_count > 0
                    ? ($snapshot->current_stock / $snapshot->fishBatch->initial_count) * 100
                    : 0;
            });

            $trends[] = [
                'month' => $month->format('M Y'),
                'avg_stock_level' => round($avgStockLevel, 2),
                'snapshot_count' => $snapshots->count(),
            ];
        }

        return $trends;
    }

    private function getVarianceTrends()
    {
        $trends = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);

            $snapshots = FishStockSnapshot::with('fishBatch')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->get();

            $avgVariance = $snapshots->avg(function($snapshot) {
                $calculatedStock = $snapshot->fishBatch->current_stock;
                return $calculatedStock > 0
                    ? abs($snapshot->current_stock - $calculatedStock) / $calculatedStock * 100
                    : 0;
            });

            $trends[] = [
                'month' => $month->format('M Y'),
                'avg_variance_percentage' => round($avgVariance, 2),
                'snapshot_count' => $snapshots->count(),
            ];
        }

        return $trends;
    }

    private function getHighVarianceSnapshots()
    {
        return FishStockSnapshot::with(['fishBatch.pond.branch', 'fishBatch.fishType'])
            ->get()
            ->map(function ($snapshot) {
                $calculatedStock = $snapshot->fishBatch->current_stock;
                $variance = abs($snapshot->current_stock - $calculatedStock);
                $variancePercentage = $calculatedStock > 0 ? ($variance / $calculatedStock) * 100 : 0;

                return [
                    'snapshot_id' => $snapshot->id,
                    'pond_name' => $snapshot->fishBatch->pond->name,
                    'branch_name' => $snapshot->fishBatch->pond->branch->name,
                    'fish_type' => $snapshot->fishBatch->fishType->name,
                    'snapshot_stock' => $snapshot->current_stock,
                    'calculated_stock' => $calculatedStock,
                    'variance' => $variance,
                    'variance_percentage' => round($variancePercentage, 2),
                    'created_at' => $snapshot->created_at,
                ];
            })
            ->filter(function ($item) {
                return $item['variance_percentage'] > 10;
            })
            ->sortByDesc('variance_percentage')
            ->take(10);
    }

    private function getAccuracyByBatchAge()
    {
        $ageGroups = [
            '0-30 days' => [0, 30],
            '31-60 days' => [31, 60],
            '61-90 days' => [61, 90],
            '91-120 days' => [91, 120],
            '120+ days' => [121, 999],
        ];

        $accuracyByAge = [];

        foreach ($ageGroups as $group => $range) {
            $snapshots = FishStockSnapshot::with('fishBatch')
                ->whereHas('fishBatch', function($query) use ($range) {
                    $query->whereRaw('DATEDIFF(NOW(), date_start) BETWEEN ? AND ?', $range);
                })
                ->get();

            $totalAccuracy = 0;
            $count = 0;

            foreach ($snapshots as $snapshot) {
                $calculatedStock = $snapshot->fishBatch->current_stock;
                if ($calculatedStock > 0) {
                    $accuracy = 100 - (abs($snapshot->current_stock - $calculatedStock) / $calculatedStock * 100);
                    $totalAccuracy += max(0, $accuracy);
                    $count++;
                }
            }

            $avgAccuracy = $count > 0 ? round($totalAccuracy / $count, 2) : 0;

            $accuracyByAge[] = [
                'age_group' => $group,
                'avg_accuracy' => $avgAccuracy,
                'snapshot_count' => $count,
            ];
        }

        return $accuracyByAge;
    }

    private function getAccuracyByFishType()
    {
        $fishTypes = DB::table('fish_types')->get();
        $accuracyByType = [];

        foreach ($fishTypes as $fishType) {
            $snapshots = FishStockSnapshot::with('fishBatch')
                ->whereHas('fishBatch', function($query) use ($fishType) {
                    $query->where('fish_type_id', $fishType->id);
                })
                ->get();

            $totalAccuracy = 0;
            $count = 0;

            foreach ($snapshots as $snapshot) {
                $calculatedStock = $snapshot->fishBatch->current_stock;
                if ($calculatedStock > 0) {
                    $accuracy = 100 - (abs($snapshot->current_stock - $calculatedStock) / $calculatedStock * 100);
                    $totalAccuracy += max(0, $accuracy);
                    $count++;
                }
            }

            $avgAccuracy = $count > 0 ? round($totalAccuracy / $count, 2) : 0;

            if ($count > 0) {
                $accuracyByType[] = [
                    'fish_type' => $fishType->name,
                    'avg_accuracy' => $avgAccuracy,
                    'snapshot_count' => $count,
                ];
            }
        }

        return collect($accuracyByType)->sortByDesc('avg_accuracy');
    }

    public function generateSnapshot(Request $request)
    {
        $validated = $request->validate([
            'batch_ids' => 'required|array',
            'batch_ids.*' => 'exists:fish_batches,id'
        ]);

        $created = 0;
        $errors = [];

        foreach ($validated['batch_ids'] as $batchId) {
            try {
                $batch = FishBatch::find($batchId);
                $calculatedStock = $batch->current_stock;

                // Cek apakah sudah ada snapshot hari ini
                $existingSnapshot = FishStockSnapshot::where('fish_batch_id', $batchId)
                    ->whereDate('created_at', today())
                    ->first();

                if (!$existingSnapshot) {
                    FishStockSnapshot::create([
                        'fish_batch_id' => $batchId,
                        'current_stock' => $calculatedStock,
                    ]);
                    $created++;
                }
            } catch (\Exception $e) {
                $errors[] = "Batch ID {$batchId}: " . $e->getMessage();
            }
        }

        $message = "Berhasil membuat {$created} snapshot stok";
        if (!empty($errors)) {
            $message .= ". Errors: " . implode(', ', $errors);
        }

        return redirect()->route('fish-stock-snapshots.index')
            ->with($created > 0 ? 'success' : 'error', $message);
    }

    public function bulkGenerate()
    {
        // Generate snapshot untuk semua batch aktif
        $activeBatches = FishBatch::whereRaw('
            (initial_count -
             COALESCE((SELECT SUM(dead_count) FROM mortalities WHERE fish_batch_id = fish_batches.id AND deleted_at IS NULL), 0) -
             COALESCE((SELECT SUM(quantity_fish) FROM sales WHERE fish_batch_id = fish_batches.id AND deleted_at IS NULL), 0)
            ) > 0
        ')->get();

        $created = 0;
        $skipped = 0;

        foreach ($activeBatches as $batch) {
            // Cek apakah sudah ada snapshot hari ini
            $existingSnapshot = FishStockSnapshot::where('fish_batch_id', $batch->id)
                ->whereDate('created_at', today())
                ->first();

            if (!$existingSnapshot) {
                FishStockSnapshot::create([
                    'fish_batch_id' => $batch->id,
                    'current_stock' => $batch->current_stock,
                ]);
                $created++;
            } else {
                $skipped++;
            }
        }

        $message = "Bulk generate selesai. Dibuat: {$created} snapshot, Dilewati: {$skipped} (sudah ada)";

        return redirect()->route('fish-stock-snapshots.index')
            ->with('success', $message);
    }

    public function compare(Request $request)
    {
        $validated = $request->validate([
            'batch_id' => 'required|exists:fish_batches,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $batch = FishBatch::with(['pond.branch', 'fishType'])->find($validated['batch_id']);

        $snapshots = FishStockSnapshot::where('fish_batch_id', $validated['batch_id'])
            ->whereBetween('created_at', [$validated['date_from'], $validated['date_to']])
            ->orderBy('created_at')
            ->get();

        $comparison = [
            'batch_info' => [
                'id' => $batch->id,
                'pond_name' => $batch->pond->name,
                'branch_name' => $batch->pond->branch->name,
                'fish_type' => $batch->fishType->name,
                'initial_count' => $batch->initial_count,
                'current_calculated_stock' => $batch->current_stock,
            ],
            'snapshots' => $snapshots->map(function($snapshot) use ($batch) {
                $variance = $snapshot->current_stock - $batch->current_stock;
                $variancePercentage = $batch->current_stock > 0
                    ? round(($variance / $batch->current_stock) * 100, 2)
                    : 0;

                return [
                    'date' => $snapshot->created_at->format('Y-m-d H:i'),
                    'snapshot_stock' => $snapshot->current_stock,
                    'calculated_stock' => $batch->current_stock,
                    'variance' => $variance,
                    'variance_percentage' => $variancePercentage,
                    'stock_percentage_of_initial' => $batch->initial_count > 0
                        ? round(($snapshot->current_stock / $batch->initial_count) * 100, 2)
                        : 0,
                ];
            }),
            'summary' => [
                'total_snapshots' => $snapshots->count(),
                'avg_variance' => $snapshots->avg(function($snapshot) use ($batch) {
                    return abs($snapshot->current_stock - $batch->current_stock);
                }),
                'max_variance' => $snapshots->max(function($snapshot) use ($batch) {
                    return abs($snapshot->current_stock - $batch->current_stock);
                }),
                'accuracy_percentage' => $this->calculateBatchAccuracy($snapshots, $batch),
            ]
        ];

        return view('fish-stock-snapshots.compare', compact('comparison', 'validated'));
    }

    private function calculateBatchAccuracy($snapshots, $batch)
    {
        if ($snapshots->isEmpty() || $batch->current_stock == 0) {
            return 0;
        }

        $totalAccuracy = 0;
        $count = 0;

        foreach ($snapshots as $snapshot) {
            $accuracy = 100 - (abs($snapshot->current_stock - $batch->current_stock) / $batch->current_stock * 100);
            $totalAccuracy += max(0, $accuracy);
            $count++;
        }

        return $count > 0 ? round($totalAccuracy / $count, 2) : 0;
    }
}
