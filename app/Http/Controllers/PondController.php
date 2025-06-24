<?php

namespace App\Http\Controllers;

use App\Models\Pond;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PondController extends Controller
{
    public function index()
    {
        $ponds = Pond::with(['branch', 'fishBatches', 'waterQualities'])
            ->when(request('search'), function($query) {
                $query->where('name', 'like', '%' . request('search') . '%')
                      ->orWhere('code', 'like', '%' . request('search') . '%');
            })
            ->when(request('branch_id'), function($query) {
                $query->where('branch_id', request('branch_id'));
            })
            ->when(request('type'), function($query) {
                $query->where('type', request('type'));
            })
            ->paginate(12);

        // Tambahkan statistik untuk setiap kolam
        foreach ($ponds as $pond) {
            $pond->statistics = [
                'current_stock' => $pond->current_stock,
                'optimal_capacity' => $pond->optimal_capacity,
                'density_percentage' => $pond->density_percentage,
                'density_status' => $pond->density_status,
                'latest_water_quality' => $pond->latest_water_quality,
                'active_batches' => $pond->fishBatches()->count(),
            ];
        }

        $branches = Branch::all();
        $pondTypes = ['tanah', 'beton', 'viber', 'terpal'];

        return view('ponds.index', compact('ponds', 'branches', 'pondTypes'));
    }

    public function create()
    {
        $branches = Branch::all();
        $pondTypes = ['tanah', 'beton', 'viber', 'terpal'];

        return view('ponds.create', compact('branches', 'pondTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:ponds,code',
            'type' => 'required|in:tanah,beton,viber,terpal',
            'volume_liters' => 'required|numeric|min:1',
            'description' => 'nullable|string',
            'documentation_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        if ($request->hasFile('documentation_file')) {
            $validated['documentation_file'] = $request->file('documentation_file')
                ->store('pond-documentation', 'public');
        }

        Pond::create($validated);

        return redirect()->route('ponds.index')
            ->with('success', 'Kolam berhasil ditambahkan');
    }

    public function show(Pond $pond)
    {
        $pond->load([
            'branch',
            'fishBatches.fishType',
            'fishBatches.mortalities',
            'fishBatches.sales',
            'waterQualities' => function ($query) {
                $query->latest('date_recorded')->limit(10);
            }
        ]);

        // Statistik detail kolam
        $statistics = [
            'capacity' => [
                'volume_liters' => number_format($pond->volume_liters, 0),
                'optimal_capacity' => number_format($pond->optimal_capacity, 0),
                'current_stock' => number_format($pond->current_stock, 0),
                'density_percentage' => $pond->density_percentage,
                'density_status' => $pond->density_status,
            ],
            'production' => [
                'active_batches' => $pond->fishBatches()->count(),
                'total_fish_sold' => $pond->fishBatches()->with('sales')->get()->sum(function ($batch) {
                    return $batch->sales->sum('quantity_fish');
                }),
                'total_revenue' => $pond->fishBatches()->with('sales')->get()->sum(function ($batch) {
                    return $batch->sales->sum('total_price');
                }),
                'average_mortality_rate' => $this->calculatePondMortalityRate($pond),
            ],
            'water_quality' => [
                'latest_test' => $pond->latest_water_quality,
                'quality_status' => $pond->latest_water_quality ? $pond->latest_water_quality->water_quality_status : 'unknown',
                'total_tests' => $pond->waterQualities()->count(),
            ]
        ];

        // Data untuk charts
        $waterQualityTrend = $this->getWaterQualityTrend($pond);
        $stockHistory = $this->getStockHistory($pond);
        $monthlyProduction = $this->getMonthlyProduction($pond);

        return view('ponds.show', compact('pond', 'statistics', 'waterQualityTrend', 'stockHistory', 'monthlyProduction'));
    }

    public function edit(Pond $pond)
    {
        $branches = Branch::all();
        $pondTypes = ['tanah', 'beton', 'viber', 'terpal'];

        return view('ponds.edit', compact('pond', 'branches', 'pondTypes'));
    }

    public function update(Request $request, Pond $pond)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:ponds,code,' . $pond->id,
            'type' => 'required|in:tanah,beton,viber,terpal',
            'volume_liters' => 'required|numeric|min:1',
            'description' => 'nullable|string',
            'documentation_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        if ($request->hasFile('documentation_file')) {
            // Hapus file lama jika ada
            if ($pond->documentation_file) {
                Storage::disk('public')->delete($pond->documentation_file);
            }

            $validated['documentation_file'] = $request->file('documentation_file')
                ->store('pond-documentation', 'public');
        }

        $pond->update($validated);

        return redirect()->route('ponds.index')
            ->with('success', 'Kolam berhasil diperbarui');
    }

    public function destroy(Pond $pond)
    {
        // Cek apakah kolam masih memiliki batch aktif
        if ($pond->fishBatches()->count() > 0) {
            return redirect()->route('ponds.index')
                ->with('error', 'Kolam tidak dapat dihapus karena masih memiliki batch ikan aktif');
        }

        // Hapus file dokumentasi jika ada
        if ($pond->documentation_file) {
            Storage::disk('public')->delete($pond->documentation_file);
        }

        $pond->delete();

        return redirect()->route('ponds.index')
            ->with('success', 'Kolam berhasil dihapus');
    }

    // Helper methods
    private function calculatePondMortalityRate($pond)
    {
        $totalInitial = $pond->fishBatches()->sum('initial_count');
        $totalDeaths = 0;

        foreach ($pond->fishBatches as $batch) {
            $totalDeaths += $batch->mortalities()->sum('dead_count');
        }

        if ($totalInitial == 0) return 0;
        return round(($totalDeaths / $totalInitial) * 100, 2);
    }

    private function getWaterQualityTrend($pond)
    {
        return $pond->waterQualities()
            ->orderBy('date_recorded', 'desc')
            ->limit(30)
            ->get()
            ->reverse()
            ->map(function ($quality) {
                return [
                    'date' => $quality->date_recorded->format('M d'),
                    'ph' => $quality->ph,
                    'temperature' => $quality->temperature_c,
                    'do' => $quality->do_mg_l,
                    'ammonia' => $quality->ammonia_mg_l,
                    'status' => $quality->water_quality_status,
                ];
            });
    }

    private function getStockHistory($pond)
    {
        $history = [];

        foreach ($pond->fishBatches as $batch) {
            $history[] = [
                'batch_id' => $batch->id,
                'fish_type' => $batch->fishType->name,
                'date_start' => $batch->date_start->format('M d, Y'),
                'initial_count' => $batch->initial_count,
                'current_stock' => $batch->current_stock,
                'status' => $batch->status,
            ];
        }

        return collect($history)->sortByDesc('date_start');
    }

    private function getMonthlyProduction($pond)
    {
        $production = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);

            $totalSales = 0;
            $totalRevenue = 0;

            foreach ($pond->fishBatches as $batch) {
                $monthlySales = $batch->sales()
                    ->whereYear('date', $month->year)
                    ->whereMonth('date', $month->month)
                    ->get();

                $totalSales += $monthlySales->sum('quantity_fish');
                $totalRevenue += $monthlySales->sum('total_price');
            }

            $production[] = [
                'month' => $month->format('M Y'),
                'sales' => $totalSales,
                'revenue' => $totalRevenue,
            ];
        }

        return $production;
    }
}
