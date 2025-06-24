<?php

namespace App\Http\Controllers;

use App\Models\Mortality;
use App\Models\FishBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MortalityController extends Controller
{
    public function index()
    {
        $mortalities = Mortality::with(['fishBatch.pond.branch', 'fishBatch.fishType', 'creator'])
            ->when(request('search'), function($query) {
                $query->whereHas('fishBatch.pond', function($q) {
                    $q->where('name', 'like', '%' . request('search') . '%');
                })->orWhere('cause', 'like', '%' . request('search') . '%');
            })
            ->when(request('batch_id'), function($query) {
                $query->where('fish_batch_id', request('batch_id'));
            })
            ->when(request('cause'), function($query) {
                $query->where('cause', request('cause'));
            })
            ->when(request('date_from'), function($query) {
                $query->whereDate('date', '>=', request('date_from'));
            })
            ->when(request('date_to'), function($query) {
                $query->whereDate('date', '<=', request('date_to'));
            })
            ->latest('date')
            ->paginate(15);

        // Tambahkan perhitungan mortality rate
        foreach ($mortalities as $mortality) {
            $mortality->mortality_data = [
                'mortality_percentage' => $mortality->mortality_percentage,
                'mortality_level' => $mortality->mortality_level,
                'stock_before_death' => $mortality->stock_before_death,
                'batch_age_days' => $mortality->fishBatch->age_in_days,
            ];
        }

        $batches = FishBatch::with(['pond', 'fishType'])->get();
        $causes = Mortality::distinct('cause')->pluck('cause')->filter();

        // Statistik ringkasan
        $statistics = [
            'total_deaths_today' => Mortality::whereDate('date', today())->sum('dead_count'),
            'total_deaths_this_week' => Mortality::whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])->sum('dead_count'),
            'total_deaths_this_month' => Mortality::whereMonth('date', now()->month)->sum('dead_count'),
            'average_mortality_rate' => $this->calculateAverageMortalityRate(),
            'top_causes' => $this->getTopMortalityCauses(),
        ];

        return view('mortalities.index', compact('mortalities', 'batches', 'causes', 'statistics'));
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

        $commonCauses = [
            'Penyakit',
            'Kualitas Air Buruk',
            'Kekurangan Oksigen',
            'Suhu Ekstrem',
            'Kepadatan Tinggi',
            'Stres',
            'Predator',
            'Tidak Diketahui'
        ];

        return view('mortalities.create', compact('batches', 'commonCauses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fish_batch_id' => 'required|exists:fish_batches,id',
            'date' => 'required|date|before_or_equal:today',
            'dead_count' => 'required|integer|min:1',
            'cause' => 'required|string|max:100',
            'description' => 'nullable|string',
            'action_taken' => 'nullable|string',
            'documentation_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        // Validasi jumlah kematian tidak melebihi stok
        $batch = FishBatch::find($validated['fish_batch_id']);
        $currentStock = $batch->current_stock;

        if ($validated['dead_count'] > $currentStock) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Jumlah kematian ({$validated['dead_count']}) melebihi stok saat ini ({$currentStock})");
        }

        if ($request->hasFile('documentation_file')) {
            $validated['documentation_file'] = $request->file('documentation_file')
                ->store('mortality-documentation', 'public');
        }

        $validated['created_by'] = Auth::id();

        $mortality = Mortality::create($validated);

        // Alert jika mortality rate tinggi
        $mortalityRate = $mortality->mortality_percentage;
        $alertMessage = 'Laporan kematian berhasil ditambahkan';

        if ($mortalityRate > 10) {
            $alertMessage .= '. PERINGATAN: Tingkat kematian tinggi (' . $mortalityRate . '%)!';
        }

        return redirect()->route('mortalities.index')
            ->with($mortalityRate > 10 ? 'warning' : 'success', $alertMessage);
    }

    public function show(Mortality $mortality)
    {
        $mortality->load(['fishBatch.pond.branch', 'fishBatch.fishType', 'creator']);

        // Analisis kematian
        $analysis = [
            'mortality_data' => [
                'dead_count' => $mortality->dead_count,
                'mortality_percentage' => $mortality->mortality_percentage,
                'mortality_level' => $mortality->mortality_level,
                'stock_before' => $mortality->stock_before_death,
                'stock_after' => $mortality->stock_after_death,
            ],
            'batch_context' => [
                'batch_age_days' => $mortality->fishBatch->age_in_days,
                'batch_age_weeks' => $mortality->fishBatch->age_in_weeks,
                'total_deaths_to_date' => $mortality->fishBatch->mortalities()->sum('dead_count'),
                'cumulative_mortality_rate' => $mortality->fishBatch->mortality_rate,
                'survival_rate' => $mortality->fishBatch->survival_rate,
            ],
            'environmental_factors' => [
                'latest_water_quality' => $mortality->fishBatch->pond->latest_water_quality,
                'recent_feedings' => $mortality->fishBatch->feedings()
                    ->whereBetween('date', [now()->subDays(7), now()])
                    ->count(),
                'recent_growth_logs' => $mortality->fishBatch->fishGrowthLogs()
                    ->whereBetween('date_recorded', [now()->subDays(14), now()])
                    ->count(),
            ]
        ];

        // Riwayat kematian batch
        $mortalityHistory = $mortality->fishBatch->mortalities()
            ->orderBy('date', 'desc')
            ->limit(10)
            ->get();

        // Trend kematian
        $mortalityTrend = $this->getMortalityTrend($mortality->fishBatch);

        return view('mortalities.show', compact('mortality', 'analysis', 'mortalityHistory', 'mortalityTrend'));
    }

    public function edit(Mortality $mortality)
    {
        $batches = FishBatch::with(['pond.branch', 'fishType'])->get();
        $commonCauses = [
            'Penyakit',
            'Kualitas Air Buruk',
            'Kekurangan Oksigen',
            'Suhu Ekstrem',
            'Kepadatan Tinggi',
            'Stres',
            'Predator',
            'Tidak Diketahui'
        ];

        return view('mortalities.edit', compact('mortality', 'batches', 'commonCauses'));
    }

    public function update(Request $request, Mortality $mortality)
    {
        $validated = $request->validate([
            'fish_batch_id' => 'required|exists:fish_batches,id',
            'date' => 'required|date|before_or_equal:today',
            'dead_count' => 'required|integer|min:1',
            'cause' => 'required|string|max:100',
            'description' => 'nullable|string',
            'action_taken' => 'nullable|string',
            'documentation_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        if ($request->hasFile('documentation_file')) {
            // Hapus file lama jika ada
            if ($mortality->documentation_file) {
                Storage::disk('public')->delete($mortality->documentation_file);
            }

            $validated['documentation_file'] = $request->file('documentation_file')
                ->store('mortality-documentation', 'public');
        }

        $mortality->update($validated);

        return redirect()->route('mortalities.show', $mortality)
            ->with('success', 'Laporan kematian berhasil diperbarui');
    }

    public function destroy(Mortality $mortality)
    {
        // Hapus file dokumentasi jika ada
        if ($mortality->documentation_file) {
            Storage::disk('public')->delete($mortality->documentation_file);
        }

        $mortality->delete();

        return redirect()->route('mortalities.index')
            ->with('success', 'Laporan kematian berhasil dihapus');
    }

    public function analytics()
    {
        // Data untuk dashboard analitik kematian
        $analytics = [
            'overview' => [
                'total_deaths_today' => Mortality::whereDate('date', today())->sum('dead_count'),
                'total_deaths_week' => Mortality::whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])->sum('dead_count'),
                'total_deaths_month' => Mortality::whereMonth('date', now()->month)->sum('dead_count'),
                'average_mortality_rate' => $this->calculateAverageMortalityRate(),
            ],
            'trends' => [
                'daily_trend' => $this->getDailyMortalityTrend(),
                'weekly_trend' => $this->getWeeklyMortalityTrend(),
                'monthly_trend' => $this->getMonthlyMortalityTrend(),
            ],
            'causes' => [
                'top_causes' => $this->getTopMortalityCauses(),
                'cause_trends' => $this->getCauseTrends(),
            ],
            'batches' => [
                'high_mortality_batches' => $this->getHighMortalityBatches(),
                'mortality_by_age' => $this->getMortalityByBatchAge(),
            ],
            'environmental' => [
                'mortality_vs_water_quality' => $this->getMortalityVsWaterQuality(),
                'seasonal_patterns' => $this->getSeasonalMortalityPatterns(),
            ]
        ];

        return view('mortalities.analytics', compact('analytics'));
    }

    // Helper methods
    private function calculateAverageMortalityRate()
    {
        $batches = FishBatch::with('mortalities')->get();
        $totalRate = 0;
        $count = 0;

        foreach ($batches as $batch) {
            if ($batch->initial_count > 0) {
                $totalRate += $batch->mortality_rate;
                $count++;
            }
        }

        return $count > 0 ? round($totalRate / $count, 2) : 0;
    }

    private function getTopMortalityCauses()
    {
        return Mortality::selectRaw('cause, SUM(dead_count) as total_deaths, COUNT(*) as incidents')
            ->groupBy('cause')
            ->orderBy('total_deaths', 'desc')
            ->limit(10)
            ->get();
    }

    private function getMortalityTrend($batch)
    {
        $mortalities = $batch->mortalities()->orderBy('date')->get();
        $trend = [];
        $cumulativeDeaths = 0;

        foreach ($mortalities as $mortality) {
            $cumulativeDeaths += $mortality->dead_count;
            $trend[] = [
                'date' => $mortality->date->format('M d'),
                'daily_deaths' => $mortality->dead_count,
                'cumulative_deaths' => $cumulativeDeaths,
                'mortality_rate' => round(($cumulativeDeaths / $batch->initial_count) * 100, 2),
                'cause' => $mortality->cause,
            ];
        }

        return collect($trend);
    }

    private function getDailyMortalityTrend()
    {
        $trend = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $deaths = Mortality::whereDate('date', $date)->sum('dead_count');

            $trend[] = [
                'date' => $date->format('M d'),
                'deaths' => $deaths,
            ];
        }

        return $trend;
    }

    private function getWeeklyMortalityTrend()
    {
        $trend = [];
        for ($i = 11; $i >= 0; $i--) {
            $startOfWeek = now()->subWeeks($i)->startOfWeek();
            $endOfWeek = now()->subWeeks($i)->endOfWeek();

            $deaths = Mortality::whereBetween('date', [$startOfWeek, $endOfWeek])->sum('dead_count');

            $trend[] = [
                'week' => $startOfWeek->format('M d') . ' - ' . $endOfWeek->format('M d'),
                'deaths' => $deaths,
            ];
        }

        return $trend;
    }

    private function getMonthlyMortalityTrend()
    {
        $trend = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $deaths = Mortality::whereYear('date', $month->year)
                ->whereMonth('date', $month->month)
                ->sum('dead_count');

            $trend[] = [
                'month' => $month->format('M Y'),
                'deaths' => $deaths,
            ];
        }

        return $trend;
    }

    private function getCauseTrends()
    {
        $causes = Mortality::distinct('cause')->pluck('cause');
        $trends = [];

        foreach ($causes as $cause) {
            $monthlyData = [];
            for ($i = 5; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $deaths = Mortality::where('cause', $cause)
                    ->whereYear('date', $month->year)
                    ->whereMonth('date', $month->month)
                    ->sum('dead_count');

                $monthlyData[] = [
                    'month' => $month->format('M Y'),
                    'deaths' => $deaths,
                ];
            }

            $trends[$cause] = $monthlyData;
        }

        return $trends;
    }

    private function getHighMortalityBatches()
    {
        return FishBatch::with(['pond.branch', 'fishType', 'mortalities'])
            ->get()
            ->filter(function ($batch) {
                return $batch->mortality_rate > 15; // Threshold 15%
            })
            ->sortByDesc('mortality_rate')
            ->take(10)
            ->map(function ($batch) {
                return [
                    'batch_id' => $batch->id,
                    'pond_name' => $batch->pond->name,
                    'branch_name' => $batch->pond->branch->name,
                    'fish_type' => $batch->fishType->name,
                    'mortality_rate' => $batch->mortality_rate,
                    'total_deaths' => $batch->mortalities->sum('dead_count'),
                    'age_days' => $batch->age_in_days,
                ];
            });
    }

    private function getMortalityByBatchAge()
    {
        $ageGroups = [
            '0-30 days' => [0, 30],
            '31-60 days' => [31, 60],
            '61-90 days' => [61, 90],
            '91-120 days' => [91, 120],
            '120+ days' => [121, 999],
        ];

        $mortalityByAge = [];

        foreach ($ageGroups as $group => $range) {
            $batches = FishBatch::with('mortalities')
                ->whereRaw('DATEDIFF(NOW(), date_start) BETWEEN ? AND ?', $range)
                ->get();

            $totalDeaths = $batches->sum(function ($batch) {
                return $batch->mortalities->sum('dead_count');
            });

            $totalInitial = $batches->sum('initial_count');
            $mortalityRate = $totalInitial > 0 ? round(($totalDeaths / $totalInitial) * 100, 2) : 0;

            $mortalityByAge[] = [
                'age_group' => $group,
                'total_deaths' => $totalDeaths,
                'total_initial' => $totalInitial,
                'mortality_rate' => $mortalityRate,
                'batch_count' => $batches->count(),
            ];
        }

        return $mortalityByAge;
    }

    private function getMortalityVsWaterQuality()
    {
        // Analisis korelasi antara kualitas air dan kematian
        $data = [];

        $batches = FishBatch::with(['mortalities', 'pond.waterQualities'])->get();

        foreach ($batches as $batch) {
            $avgWaterQuality = $batch->pond->waterQualities()
                ->whereBetween('date_recorded', [$batch->date_start, now()])
                ->selectRaw('AVG(ph) as avg_ph, AVG(temperature_c) as avg_temp, AVG(do_mg_l) as avg_do')
                ->first();

            if ($avgWaterQuality && $avgWaterQuality->avg_ph) {
                $data[] = [
                    'batch_id' => $batch->id,
                    'mortality_rate' => $batch->mortality_rate,
                    'avg_ph' => round($avgWaterQuality->avg_ph, 2),
                    'avg_temperature' => round($avgWaterQuality->avg_temp, 2),
                    'avg_do' => round($avgWaterQuality->avg_do, 2),
                ];
            }
        }

        return collect($data);
    }

    private function getSeasonalMortalityPatterns()
    {
        $patterns = [];
        $months = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'May',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Aug',
            9 => 'Sep',
            10 => 'Oct',
            11 => 'Nov',
            12 => 'Dec'
        ];

        foreach ($months as $monthNum => $monthName) {
            $deaths = Mortality::whereMonth('date', $monthNum)
                ->whereYear('date', '>=', now()->year - 2) // 2 tahun terakhir
                ->sum('dead_count');

            $incidents = Mortality::whereMonth('date', $monthNum)
                ->whereYear('date', '>=', now()->year - 2)
                ->count();

            $patterns[] = [
                'month' => $monthName,
                'total_deaths' => $deaths,
                'incidents' => $incidents,
                'avg_deaths_per_incident' => $incidents > 0 ? round($deaths / $incidents, 2) : 0,
            ];
        }

        return $patterns;
    }
}
