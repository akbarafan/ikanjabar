<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        $query = Branch::with(['users', 'ponds'])
            ->withCount(['users', 'ponds']);

        // Handle search functionality
        $searchTerm = null;
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where('name', 'LIKE', '%' . $searchTerm . '%');
        }

        $branches = $query->paginate(10);

        // Perhitungan statistik per cabang
        foreach ($branches as $branch) {
            $branch->statistics = [
                'total_ponds' => $branch->ponds_count ?? 0,
                'total_volume' => $branch->ponds->sum('volume_liters') ?? 0,
                'total_active_batches' => $branch->ponds->sum(function($pond) {
                    return $pond->fishBatches()->whereNull('deleted_at')->count();
                }) ?? 0,
                'total_fish_stock' => $this->calculateTotalFishStock($branch),
                'total_sales' => $this->calculateTotalSales($branch),
                'average_water_quality' => $this->calculateAverageWaterQuality($branch),
            ];
        }

        // If it's an AJAX request, return JSON
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'branches' => $branches,
                    'search_term' => $searchTerm,
                    'total' => $branches->total(),
                    'has_results' => $branches->count() > 0
                ],
                'html' => view('admin.branches.partials.table', compact('branches', 'searchTerm'))->render(),
                'pagination' => $branches->appends(['search' => $searchTerm])->links()->render()
            ]);
        }

        return view('admin.branches.index', compact('branches', 'searchTerm'));
    }

    public function search(Request $request)
    {
        $searchTerm = $request->input('search');
        
        $query = Branch::with(['users', 'ponds'])
            ->withCount(['users', 'ponds']);

        if (!empty($searchTerm)) {
            $query->where('name', 'LIKE', '%' . $searchTerm . '%');
        }

        $branches = $query->paginate(10);

        // Perhitungan statistik per cabang
        foreach ($branches as $branch) {
            $branch->statistics = [
                'total_ponds' => $branch->ponds_count ?? 0,
                'total_volume' => $branch->ponds->sum('volume_liters') ?? 0,
                'total_active_batches' => $branch->ponds->sum(function($pond) {
                    return $pond->fishBatches()->whereNull('deleted_at')->count();
                }) ?? 0,
                'total_fish_stock' => $this->calculateTotalFishStock($branch),
                'total_sales' => $this->calculateTotalSales($branch),
                'average_water_quality' => $this->calculateAverageWaterQuality($branch),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'branches' => $branches,
                'search_term' => $searchTerm,
                'total' => $branches->total(),
                'has_results' => $branches->count() > 0
            ],
            'html' => view('admin.branches.partials.table', compact('branches', 'searchTerm'))->render(),
            'pagination' => $branches->appends(['search' => $searchTerm])->links()->render(),
            'search_info' => view('admin.branches.partials.search-info', [
                'searchTerm' => $searchTerm,
                'total' => $branches->total(),
                'hasResults' => $branches->count() > 0
            ])->render()
        ]);
    }

    public function create()
    {
        return view('admin.branches.create');
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

        return redirect()->route('admin.branches.index')
            ->with('success', 'Cabang berhasil ditambahkan');
    }

    public function show(Branch $branch)
    {
        $branch->load(['users', 'ponds.fishBatches', 'ponds.waterQualities']);

        // Perhitungan detail statistik cabang
        $statistics = [
            'overview' => [
                'total_ponds' => $branch->ponds->count(),
                'total_volume' => number_format($branch->ponds->sum('volume_liters'), 0),
                'total_users' => $branch->users->count(),
                'total_active_batches' => $branch->ponds->sum(function($pond) {
                    return $pond->fishBatches()->whereNull('deleted_at')->count();
                }),
            ],
            'production' => [
                'total_fish_stock' => number_format($this->calculateTotalFishStock($branch), 0),
                'total_sales' => 'Rp ' . number_format($this->calculateTotalSales($branch), 0),
                'average_density' => $this->calculateAverageDensity($branch),
                'productivity_score' => $this->calculateProductivityScore($branch),
            ],
            'water_quality' => $this->calculateAverageWaterQuality($branch),
            'performance' => [
                'mortality_rate' => $this->calculateBranchMortalityRate($branch),
                'growth_rate' => $this->calculateBranchGrowthRate($branch),
                'fcr' => $this->calculateBranchFCR($branch),
            ]
        ];

        // Data untuk chart
        $monthlyData = $this->getMonthlySalesData($branch);
        $pondTypes = $this->getPondTypeDistribution($branch);

        return view('admin.branches.show', compact('branch', 'statistics', 'monthlyData', 'pondTypes'));
    }

    public function edit(Branch $branch)
    {
        return view('admin.branches.edit', compact('branch'));
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

        return redirect()->route('admin.branches.index')
            ->with('success', 'Cabang berhasil diperbarui');
    }

    public function destroy(Branch $branch)
    {
        // Cek apakah cabang masih memiliki data terkait
        if ($branch->users()->count() > 0 || $branch->ponds()->count() > 0) {
            return redirect()->route('admin.branches.index')
                ->with('error', 'Cabang tidak dapat dihapus karena masih memiliki data terkait');
        }

        $branch->delete();

        return redirect()->route('admin.branches.index')
            ->with('success', 'Cabang berhasil dihapus');
    }

    // Helper methods untuk perhitungan
    private function calculateTotalFishStock($branch)
    {
        $totalStock = 0;
        foreach ($branch->ponds as $pond) {
            foreach ($pond->fishBatches as $batch) {
                $totalStock += $batch->initial_count ?? 0;
            }
        }
        return $totalStock;
    }

    private function calculateTotalSales($branch)
    {
        $totalSales = 0;
        foreach ($branch->ponds as $pond) {
            foreach ($pond->fishBatches as $batch) {
                // Assuming there's a sales relationship
                // $totalSales += $batch->sales()->sum('total_price');
            }
        }
        return $totalSales;
    }

    private function calculateAverageWaterQuality($branch)
    {
        $waterQualities = collect();
        foreach ($branch->ponds as $pond) {
            $waterQualities = $waterQualities->merge($pond->waterQualities ?? collect());
        }

        if ($waterQualities->isEmpty()) {
            return [
                'avg_ph' => 0,
                'avg_temperature' => 0,
                'avg_do' => 0,
                'avg_ammonia' => 0,
            ];
        }

        return [
            'avg_ph' => $waterQualities->avg('ph') ?? 0,
            'avg_temperature' => $waterQualities->avg('temperature_c') ?? 0,
            'avg_do' => $waterQualities->avg('do_mg_l') ?? 0,
            'avg_ammonia' => $waterQualities->avg('ammonia_mg_l') ?? 0,
        ];
    }

    private function calculateAverageDensity($branch)
    {
        $ponds = $branch->ponds;
        if ($ponds->isEmpty()) return 0;

        $totalDensity = $ponds->sum(function($pond) {
            // Calculate density based on fish stock vs pond volume
            $totalFish = $pond->fishBatches()->sum('initial_count') ?? 0;
            $volume = $pond->volume_liters ?? 1;
            return ($totalFish / $volume) * 100;
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
                $totalInitial += $batch->initial_count ?? 0;
                // $totalDeaths += $batch->mortalities()->sum('dead_count');
            }
        }

        if ($totalInitial == 0) return 0;
        return round(($totalDeaths / $totalInitial) * 100, 2);
    }

    private function calculateBranchGrowthRate($branch)
    {
        // Simplified calculation
        return 15.5; // Default value
    }

    private function calculateBranchFCR($branch)
    {
        // Simplified calculation
        return 1.35; // Default value
    }

    private function calculateWaterQualityScore($branch)
    {
        $waterQuality = $this->calculateAverageWaterQuality($branch);
        if (!$waterQuality || $waterQuality['avg_ph'] == 0) return 50;

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
            $salesData[] = [
                'month' => $month->format('M Y'),
                'sales' => rand(1000000, 5000000) // Dummy data
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
