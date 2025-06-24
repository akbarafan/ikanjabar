<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::with(['users', 'ponds'])
            ->withCount(['users', 'ponds'])
            ->paginate(10);

        // Perhitungan statistik per cabang
        foreach ($branches as $branch) {
            $branch->statistics = [
                'total_ponds' => $branch->total_ponds,
                'total_volume' => $branch->total_volume,
                'total_active_batches' => $branch->total_active_batches,
                'total_fish_stock' => $branch->total_fish_stock,
                'total_sales' => $branch->total_sales,
                'average_water_quality' => $branch->average_water_quality,
            ];
        }

        return view('branches.index', compact('branches'));
    }

    public function create()
    {
        return view('branches.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'location' => 'required|string',
            'contact_person' => 'required|string|max:100',
            'pic_name' => 'required|string|max:100',
        ]);

        Branch::create($validated);

        return redirect()->route('branches.index')
            ->with('success', 'Cabang berhasil ditambahkan');
    }

    public function show(Branch $branch)
    {
        $branch->load(['users', 'ponds.fishBatches', 'ponds.waterQualities']);

        // Perhitungan detail statistik cabang
        $statistics = [
            'overview' => [
                'total_ponds' => $branch->total_ponds,
                'total_volume' => number_format($branch->total_volume, 0),
                'total_users' => $branch->users->count(),
                'total_active_batches' => $branch->total_active_batches,
            ],
            'production' => [
                'total_fish_stock' => number_format($branch->total_fish_stock, 0),
                'total_sales' => 'Rp ' . number_format($branch->total_sales, 0),
                'average_density' => $this->calculateAverageDensity($branch),
                'productivity_score' => $this->calculateProductivityScore($branch),
            ],
            'water_quality' => $branch->average_water_quality,
            'performance' => [
                'mortality_rate' => $this->calculateBranchMortalityRate($branch),
                'growth_rate' => $this->calculateBranchGrowthRate($branch),
                'fcr' => $this->calculateBranchFCR($branch),
            ]
        ];

        // Data untuk chart
        $monthlyData = $this->getMonthlySalesData($branch);
        $pondTypes = $this->getPondTypeDistribution($branch);

        return view('branches.show', compact('branch', 'statistics', 'monthlyData', 'pondTypes'));
    }

    public function edit(Branch $branch)
    {
        return view('branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'location' => 'required|string',
            'contact_person' => 'required|string|max:100',
            'pic_name' => 'required|string|max:100',
        ]);

        $branch->update($validated);

        return redirect()->route('branches.index')
            ->with('success', 'Cabang berhasil diperbarui');
    }

    public function destroy(Branch $branch)
    {
        // Cek apakah cabang masih memiliki data terkait
        if ($branch->users()->count() > 0 || $branch->ponds()->count() > 0) {
            return redirect()->route('branches.index')
                ->with('error', 'Cabang tidak dapat dihapus karena masih memiliki data terkait');
        }

        $branch->delete();

        return redirect()->route('branches.index')
            ->with('success', 'Cabang berhasil dihapus');
    }

    // Helper methods untuk perhitungan
    private function calculateAverageDensity($branch)
    {
        $ponds = $branch->ponds;
        if ($ponds->isEmpty()) return 0;

        $totalDensity = $ponds->sum(function($pond) {
            return $pond->density_percentage;
        });

        return round($totalDensity / $ponds->count(), 2);
    }

    private function calculateProductivityScore($branch)
    {
        // Score berdasarkan berbagai faktor
        $densityScore = min(100 - $this->calculateAverageDensity($branch), 100);
        $mortalityScore = max(100 - ($this->calculateBranchMortalityRate($branch) * 10), 0);
        $waterQualityScore = $this->calculateWaterQualityScore($branch);

        return round(($densityScore + $mortalityScore + $waterQualityScore) / 3, 1);
    }

    private function calculateBranchMortalityRate($branch)
    {
        $totalInitial = 0;
        $totalDeaths = 0;

        foreach ($branch->ponds as $pond) {
            foreach ($pond->fishBatches as $batch) {
                $totalInitial += $batch->initial_count;
                $totalDeaths += $batch->mortalities()->sum('dead_count');
            }
        }

        if ($totalInitial == 0) return 0;
        return round(($totalDeaths / $totalInitial) * 100, 2);
    }

    private function calculateBranchGrowthRate($branch)
    {
        $allGrowthLogs = collect();

        foreach ($branch->ponds as $pond) {
            foreach ($pond->fishBatches as $batch) {
                $allGrowthLogs = $allGrowthLogs->merge($batch->fishGrowthLogs);
            }
        }

        if ($allGrowthLogs->isEmpty()) return 0;

        return round($allGrowthLogs->avg('avg_weight_gram'), 2);
    }

    private function calculateBranchFCR($branch)
    {
        $totalFeed = 0;
        $totalBiomass = 0;

        foreach ($branch->ponds as $pond) {
            foreach ($pond->fishBatches as $batch) {
                $totalFeed += $batch->total_feed_given;
                $totalBiomass += $batch->current_biomass;
            }
        }

        if ($totalBiomass == 0) return 0;
        return round($totalFeed / $totalBiomass, 2);
    }

    private function calculateWaterQualityScore($branch)
    {
        $waterQuality = $branch->average_water_quality;
        if (!$waterQuality) return 50;

        $phScore = $this->getPhScore($waterQuality['avg_ph']);
        $tempScore = $this->getTemperatureScore($waterQuality['avg_temperature']);
        $doScore = $this->getDoScore($waterQuality['avg_do']);

        return round(($phScore + $tempScore + $doScore) / 3, 1);
    }

    private function getPhScore($ph)
    {
        if ($ph >= 7.0 && $ph <= 8.0) return 100;
        if ($ph >= 6.5 && $ph <= 8.5) return 80;
        if ($ph >= 6.0 && $ph <= 9.0) return 60;
        return 30;
    }

    private function getTemperatureScore($temp)
    {
        if ($temp >= 25 && $temp <= 30) return 100;
        if ($temp >= 22 && $temp <= 32) return 80;
        if ($temp >= 20 && $temp <= 35) return 60;
        return 30;
    }

    private function getDoScore($do)
    {
        if ($do >= 5) return 100;
        if ($do >= 4) return 80;
        if ($do >= 3) return 60;
        return 30;
    }

    private function getMonthlySalesData($branch)
    {
        $salesData = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthKey = $month->format('Y-m');

            $totalSales = 0;
            foreach ($branch->ponds as $pond) {
                foreach ($pond->fishBatches as $batch) {
                    $totalSales += $batch->sales()
                        ->whereYear('date', $month->year)
                        ->whereMonth('date', $month->month)
                        ->sum('total_price');
                }
            }

            $salesData[] = [
                'month' => $month->format('M Y'),
                'sales' => $totalSales
            ];
        }

        return $salesData;
    }

    private function getPondTypeDistribution($branch)
    {
        return $branch->ponds()
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->type => $item->count];
            });
    }
}
