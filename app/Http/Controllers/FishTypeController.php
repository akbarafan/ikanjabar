<?php

namespace App\Http\Controllers;

use App\Models\FishType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FishTypeController extends Controller
{
    public function index()
    {
        $fishTypes = FishType::query()
            ->when(request('search'), function($query) {
                $query->where('name', 'like', '%' . request('search') . '%')
                      ->orWhere('description', 'like', '%' . request('search') . '%');
            })
            ->withCount('fishBatches')
            ->latest()
            ->paginate(15);

        // Tambahkan statistik untuk setiap fish type
        foreach ($fishTypes as $fishType) {
            $fishType->statistics = [
                'total_batches' => $fishType->total_batches,
                'average_growth_rate' => $fishType->average_growth_rate,
                'mortality_rate' => $fishType->mortality_rate,
            ];
        }

        // Statistik ringkasan
        $statistics = [
            'total_fish_types' => FishType::count(),
            'total_active_batches' => FishType::withCount(['fishBatches' => function($query) {
                $query->whereDoesntHave('sales', function($salesQuery) {
                    $salesQuery->whereRaw('quantity_fish >= (SELECT initial_count FROM fish_batches WHERE fish_batches.id = sales.fish_batch_id)');
                });
            }])->get()->sum('fish_batches_count'),
            'average_mortality_rate' => $this->calculateAverageMortalityRate(),
            'most_popular_type' => $this->getMostPopularFishType(),
        ];

        return view('fish-types.index', compact('fishTypes', 'statistics'));
    }

    public function create()
    {
        return view('fish-types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:fish_types,name',
            'description' => 'nullable|string',
        ]);

        $fishType = FishType::create($validated);

        return redirect()->route('fish-types.index')
            ->with('success', 'Jenis ikan berhasil ditambahkan');
    }

    public function show(FishType $fishType)
    {
        $fishType->load(['fishBatches.pond.branch', 'fishBatches.fishGrowthLogs', 'fishBatches.mortalities', 'fishBatches.sales']);

        // Analisis jenis ikan
        $analysis = [
            'basic_info' => [
                'id' => $fishType->id,
                'name' => $fishType->name,
                'description' => $fishType->description,
                'created_at' => $fishType->created_at,
            ],
            'batch_statistics' => [
                'total_batches' => $fishType->total_batches,
                'total_initial_fish' => $fishType->fishBatches->sum('initial_count'),
                'active_batches' => $fishType->fishBatches->filter(function($batch) {
                    return $batch->sales->sum('quantity_fish') < $batch->initial_count;
                })->count(),
            ],
            'growth_statistics' => $fishType->average_growth_rate,
            'mortality_statistics' => [
                'mortality_rate_percentage' => $fishType->mortality_rate,
                'total_deaths' => $fishType->fishBatches->sum(function ($batch) {
                    return $batch->mortalities->sum('dead_count');
                }),
            ],
            'sales_statistics' => [
                'total_sales_count' => $fishType->fishBatches->sum(function ($batch) {
                    return $batch->sales->count();
                }),
                'total_fish_sold' => $fishType->fishBatches->sum(function ($batch) {
                    return $batch->sales->sum('quantity_fish');
                }),
                'total_revenue' => $fishType->fishBatches->sum(function ($batch) {
                    return $batch->sales->sum('total_price');
                }),
            ]
        ];

        // Riwayat batch
        $batchHistory = $fishType->fishBatches()
            ->with(['pond.branch', 'mortalities', 'sales'])
            ->orderBy('date_start', 'desc')
            ->limit(10)
            ->get();

        // Trend pertumbuhan
        $growthTrend = $this->getGrowthTrend($fishType);

        return view('fish-types.show', compact('fishType', 'analysis', 'batchHistory', 'growthTrend'));
    }

    public function edit(FishType $fishType)
    {
        return view('fish-types.edit', compact('fishType'));
    }

    public function update(Request $request, FishType $fishType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:fish_types,name,' . $fishType->id,
            'description' => 'nullable|string',
        ]);

        $fishType->update($validated);

        return redirect()->route('fish-types.show', $fishType)
            ->with('success', 'Jenis ikan berhasil diperbarui');
    }

    public function destroy(FishType $fishType)
    {
        // Check if fish type has associated batches
        if ($fishType->fishBatches()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Tidak dapat menghapus jenis ikan yang memiliki batch terkait');
        }

        $fishType->delete();

        return redirect()->route('fish-types.index')
            ->with('success', 'Jenis ikan berhasil dihapus');
    }

    public function analytics()
    {
        // Data untuk dashboard analitik jenis ikan
        $analytics = [
            'overview' => [
                'total_fish_types' => FishType::count(),
                'total_batches' => FishType::withCount('fishBatches')->get()->sum('fish_batches_count'),
                'average_mortality_rate' => $this->calculateAverageMortalityRate(),
                'best_performing_type' => $this->getBestPerformingFishType(),
            ],
            'performance' => [
                'mortality_comparison' => $this->getMortalityComparison(),
                'growth_comparison' => $this->getGrowthComparison(),
                'sales_comparison' => $this->getSalesComparison(),
            ],
            'trends' => [
                'monthly_batch_trend' => $this->getMonthlyBatchTrend(),
                'seasonal_performance' => $this->getSeasonalPerformance(),
            ],
            'recommendations' => $this->getRecommendations(),
        ];

        return view('fish-types.analytics', compact('analytics'));
    }

    // Helper methods
    private function calculateAverageMortalityRate()
    {
        $fishTypes = FishType::with('fishBatches.mortalities')->get();
        $totalRate = 0;
        $count = 0;

        foreach ($fishTypes as $fishType) {
            if ($fishType->fishBatches->count() > 0) {
                $totalRate += $fishType->mortality_rate;
                $count++;
            }
        }

        return $count > 0 ? round($totalRate / $count, 2) : 0;
    }

    private function getMostPopularFishType()
    {
        return FishType::withCount('fishBatches')
            ->orderBy('fish_batches_count', 'desc')
            ->first();
    }

    private function getGrowthTrend($fishType)
    {
        $growthLogs = collect();
        foreach ($fishType->fishBatches as $batch) {
            $growthLogs = $growthLogs->merge($batch->fishGrowthLogs);
        }

        $trend = $growthLogs->groupBy('week_number')->map(function ($logs, $week) {
            return [
                'week' => $week,
                'avg_weight' => round($logs->avg('avg_weight_gram'), 2),
                'avg_length' => round($logs->avg('avg_length_cm'), 2),
                'sample_count' => $logs->count(),
            ];
        })->sortBy('week')->values();

        return $trend;
    }

    private function getBestPerformingFishType()
    {
        return FishType::with('fishBatches')
            ->get()
            ->sortBy('mortality_rate')
            ->first();
    }

    private function getMortalityComparison()
    {
        return FishType::with('fishBatches.mortalities')
            ->get()
            ->map(function ($fishType) {
                return [
                    'name' => $fishType->name,
                    'mortality_rate' => $fishType->mortality_rate,
                    'total_batches' => $fishType->total_batches,
                ];
            })
            ->sortBy('mortality_rate');
    }

    private function getGrowthComparison()
    {
        return FishType::with('fishBatches.fishGrowthLogs')
            ->get()
            ->map(function ($fishType) {
                $growthRate = $fishType->average_growth_rate;
                return [
                    'name' => $fishType->name,
                    'avg_weight_growth' => $growthRate['avg_weight_growth'] ?? 0,
                    'avg_length_growth' => $growthRate['avg_length_growth'] ?? 0,
                    'total_batches' => $fishType->total_batches,
                ];
            })
            ->sortByDesc('avg_weight_growth');
    }

    private function getSalesComparison()
    {
        return FishType::with('fishBatches.sales')
            ->get()
            ->map(function ($fishType) {
                $totalRevenue = $fishType->fishBatches->sum(function ($batch) {
                    return $batch->sales->sum('total_price');
                });
                $totalSold = $fishType->fishBatches->sum(function ($batch) {
                    return $batch->sales->sum('quantity_fish');
                });

                return [
                    'name' => $fishType->name,
                    'total_revenue' => $totalRevenue,
                    'total_fish_sold' => $totalSold,
                    'avg_price_per_fish' => $totalSold > 0 ? round($totalRevenue / $totalSold, 2) : 0,
                ];
            })
            ->sortByDesc('total_revenue');
    }

    private function getMonthlyBatchTrend()
    {
        $trend = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $batchCount = FishType::withCount(['fishBatches' => function($query) use ($month) {
                $query->whereYear('date_start', $month->year)
                      ->whereMonth('date_start', $month->month);
            }])->get()->sum('fish_batches_count');

            $trend[] = [
                'month' => $month->format('M Y'),
                'batch_count' => $batchCount,
            ];
        }

        return $trend;
    }

    private function getSeasonalPerformance()
    {
        $seasons = [
            'Q1' => [1, 2, 3],
            'Q2' => [4, 5, 6],
            'Q3' => [7, 8, 9],
            'Q4' => [10, 11, 12],
        ];

        $performance = [];

        foreach ($seasons as $season => $months) {
            $fishTypes = FishType::with(['fishBatches' => function($query) use ($months) {
                $query->whereIn(\DB::raw('MONTH(date_start)'), $months);
            }])->get();

            $avgMortality = $fishTypes->avg('mortality_rate');
            $totalBatches = $fishTypes->sum('total_batches');

            $performance[] = [
                'season' => $season,
                'avg_mortality_rate' => round($avgMortality, 2),
                'total_batches' => $totalBatches,
            ];
        }

        return $performance;
    }

    private function getRecommendations()
    {
        $recommendations = [];
        $fishTypes = FishType::with('fishBatches')->get();

        foreach ($fishTypes as $fishType) {
            if ($fishType->mortality_rate > 15) {
                $recommendations[] = [
                    'type' => 'warning',
                    'fish_type' => $fishType->name,
                    'message' => 'Tingkat kematian tinggi (' . $fishType->mortality_rate . '%). Perlu evaluasi manajemen.',
                ];
            }

            if ($fishType->total_batches == 0) {
                $recommendations[] = [
                    'type' => 'info',
                    'fish_type' => $fishType->name,
                    'message' => 'Belum ada batch untuk jenis ikan ini. Pertimbangkan untuk memulai budidaya.',
                ];
            }
        }

        return collect($recommendations);
    }
}
