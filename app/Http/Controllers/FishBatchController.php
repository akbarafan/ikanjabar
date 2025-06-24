<?php

namespace App\Http\Controllers;

use App\Models\FishBatch;
use App\Models\Pond;
use App\Models\FishType;
use App\Models\FishStockSnapshot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FishBatchController extends Controller
{
    public function index()
    {
        $batches = FishBatch::with(['pond.branch', 'fishType', 'creator'])
            ->when(request('search'), function($query) {
                $query->whereHas('pond', function($q) {
                    $q->where('name', 'like', '%' . request('search') . '%');
                })->orWhereHas('fishType', function($q) {
                    $q->where('name', 'like', '%' . request('search') . '%');
                });
            })
            ->when(request('pond_id'), function($query) {
                $query->where('pond_id', request('pond_id'));
            })
            ->when(request('fish_type_id'), function($query) {
                $query->where('fish_type_id', request('fish_type_id'));
            })
            ->when(request('status'), function($query) {
                // Filter berdasarkan status yang dihitung
                $status = request('status');
                $query->whereRaw("
                    CASE
                        WHEN (
                            initial_count -
                            COALESCE((SELECT SUM(dead_count) FROM mortalities WHERE fish_batch_id = fish_batches.id AND deleted_at IS NULL), 0) -
                            COALESCE((SELECT SUM(quantity_fish) FROM sales WHERE fish_batch_id = fish_batches.id AND deleted_at IS NULL), 0) -
                            COALESCE((SELECT SUM(transferred_count) FROM fish_batch_transfers WHERE source_batch_id = fish_batches.id AND deleted_at IS NULL), 0) +
                            COALESCE((SELECT SUM(transferred_count) FROM fish_batch_transfers WHERE target_batch_id = fish_batches.id AND deleted_at IS NULL), 0)
                        ) <= 0 THEN 'completed'
                        WHEN DATEDIFF(NOW(), date_start) < 30 THEN 'juvenile'
                        WHEN DATEDIFF(NOW(), date_start) < 90 THEN 'growing'
                        WHEN DATEDIFF(NOW(), date_start) < 150 THEN 'mature'
                        ELSE 'ready_harvest'
                    END = ?
                ", [$status]);
            })
            ->latest()
            ->paginate(15);

        // Tambahkan statistik untuk setiap batch
        foreach ($batches as $batch) {
            $batch->statistics = [
                'current_stock' => $batch->current_stock,
                'age_in_days' => $batch->age_in_days,
                'age_in_weeks' => $batch->age_in_weeks,
                'mortality_rate' => $batch->mortality_rate,
                'survival_rate' => $batch->survival_rate,
                'status' => $batch->status,
                'total_feed_given' => $batch->total_feed_given,
                'fcr' => $batch->fcr,
                'total_revenue' => $batch->total_revenue,
                'current_biomass' => $batch->current_biomass,
            ];
        }

        $ponds = Pond::with('branch')->get();
        $fishTypes = FishType::all();
        $statuses = ['juvenile', 'growing', 'mature', 'ready_harvest', 'completed'];

        return view('fish-batches.index', compact('batches', 'ponds', 'fishTypes', 'statuses'));
    }

    public function create()
    {
        $ponds = Pond::with('branch')->get();
        $fishTypes = FishType::all();

        return view('fish-batches.create', compact('ponds', 'fishTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pond_id' => 'required|exists:ponds,id',
            'fish_type_id' => 'required|exists:fish_types,id',
            'date_start' => 'required|date',
            'initial_count' => 'required|integer|min:1',
            'notes' => 'nullable|string',
            'documentation_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        // Cek kapasitas kolam
        $pond = Pond::find($validated['pond_id']);
        $currentStock = $pond->current_stock;
        $optimalCapacity = $pond->optimal_capacity;

        if (($currentStock + $validated['initial_count']) > $optimalCapacity) {
            return redirect()->back()
                ->withInput()
                ->with('warning', "Peringatan: Jumlah ikan akan melebihi kapasitas optimal kolam ({$optimalCapacity} ekor). Saat ini: {$currentStock} ekor.");
        }

        if ($request->hasFile('documentation_file')) {
            $validated['documentation_file'] = $request->file('documentation_file')
                ->store('batch-documentation', 'public');
        }

        $validated['created_by'] = Auth::id();

        $batch = FishBatch::create($validated);

        // Buat snapshot stok awal
        FishStockSnapshot::create([
            'fish_batch_id' => $batch->id,
            'current_stock' => $validated['initial_count'],
        ]);

        return redirect()->route('fish-batches.index')
            ->with('success', 'Batch ikan berhasil ditambahkan');
    }

    public function show(FishBatch $fishBatch)
    {
        $fishBatch->load([
            'pond.branch', 'fishType', 'creator',
            'fishGrowthLogs' => function($query) {
                $query->orderBy('week_number');
            },
            'mortalities' => function($query) {
                $query->latest('date');
            },
            'feedings' => function($query) {
                $query->latest('date');
            },
            'sales' => function($query) {
                $query->latest('date');
            },
            'sourceTransfers', 'targetTransfers'
        ]);

        // Statistik lengkap batch
        $statistics = [
            'overview' => [
                'initial_count' => number_format($fishBatch->initial_count, 0),
                'current_stock' => number_format($fishBatch->current_stock, 0),
                'age_in_days' => $fishBatch->age_in_days,
                'age_in_weeks' => $fishBatch->age_in_weeks,
                'status' => $fishBatch->status,
            ],
            'health' => [
                'mortality_rate' => $fishBatch->mortality_rate,
                'survival_rate' => $fishBatch->survival_rate,
                'total_deaths' => $fishBatch->mortalities->sum('dead_count'),
                'latest_mortality' => $fishBatch->mortalities->first(),
            ],
            'growth' => [
                'latest_weight' => $fishBatch->fishGrowthLogs->last()?->avg_weight_gram ?? 0,
                'latest_length' => $fishBatch->fishGrowthLogs->last()?->avg_length_cm ?? 0,
                'weekly_growth_rate' => $fishBatch->weekly_growth_rate,
                'total_growth_logs' => $fishBatch->fishGrowthLogs->count(),
            ],
            'feeding' => [
                'total_feed_given' => $fishBatch->total_feed_given,
                'fcr' => $fishBatch->fcr,
                'total_feeding_sessions' => $fishBatch->feedings->count(),
                'latest_feeding' => $fishBatch->feedings->first(),
            ],
            'production' => [
                'current_biomass' => $fishBatch->current_biomass,
                'total_sold' => $fishBatch->sales->sum('quantity_fish'),
                'total_revenue' => $fishBatch->total_revenue,
                'average_selling_price' => $fishBatch->sales->count() > 0 ?
                    round($fishBatch->total_revenue / $fishBatch->sales->sum('quantity_fish'), 2) : 0,
            ],
            'transfers' => [
                'transferred_out' => $fishBatch->sourceTransfers->sum('transferred_count'),
                'transferred_in' => $fishBatch->targetTransfers->sum('transferred_count'),
                'net_transfer' => $fishBatch->targetTransfers->sum('transferred_count') -
                    $fishBatch->sourceTransfers->sum('transferred_count'),
            ]
        ];

        // Data untuk charts
        $growthChart = $this->getGrowthChartData($fishBatch);
        $mortalityChart = $this->getMortalityChartData($fishBatch);
        $feedingChart = $this->getFeedingChartData($fishBatch);
        $stockTimeline = $this->getStockTimelineData($fishBatch);

        return view('fish-batches.show', compact(
            'fishBatch',
            'statistics',
            'growthChart',
            'mortalityChart',
            'feedingChart',
            'stockTimeline'
        ));
    }

    public function edit(FishBatch $fishBatch)
    {
        $ponds = Pond::with('branch')->get();
        $fishTypes = FishType::all();

        return view('fish-batches.edit', compact('fishBatch', 'ponds', 'fishTypes'));
    }

    public function update(Request $request, FishBatch $fishBatch)
    {
        $validated = $request->validate([
            'pond_id' => 'required|exists:ponds,id',
            'fish_type_id' => 'required|exists:fish_types,id',
            'date_start' => 'required|date',
            'initial_count' => 'required|integer|min:1',
            'notes' => 'nullable|string',
            'documentation_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        if ($request->hasFile('documentation_file')) {
            // Hapus file lama jika ada
            if ($fishBatch->documentation_file) {
                Storage::disk('public')->delete($fishBatch->documentation_file);
            }

            $validated['documentation_file'] = $request->file('documentation_file')
                ->store('batch-documentation', 'public');
        }

        $fishBatch->update($validated);

        return redirect()->route('fish-batches.show', $fishBatch)
            ->with('success', 'Batch ikan berhasil diperbarui');
    }

    public function destroy(FishBatch $fishBatch)
    {
        // Cek apakah batch masih memiliki data terkait
        $hasRelatedData = $fishBatch->fishGrowthLogs()->count() > 0 ||
            $fishBatch->mortalities()->count() > 0 ||
            $fishBatch->feedings()->count() > 0 ||
            $fishBatch->sales()->count() > 0;

        if ($hasRelatedData) {
            return redirect()->route('fish-batches.index')
                ->with('error', 'Batch tidak dapat dihapus karena masih memiliki data terkait');
        }

        // Hapus file dokumentasi jika ada
        if ($fishBatch->documentation_file) {
            Storage::disk('public')->delete($fishBatch->documentation_file);
        }

        $fishBatch->delete();

        return redirect()->route('fish-batches.index')
            ->with('success', 'Batch ikan berhasil dihapus');
    }

    public function updateStock(Request $request, FishBatch $fishBatch)
    {
        $validated = $request->validate([
            'current_stock' => 'required|integer|min:0|max:' . $fishBatch->initial_count,
            'reason' => 'required|string|max:255',
        ]);

        // Buat snapshot stok baru
        FishStockSnapshot::create([
            'fish_batch_id' => $fishBatch->id,
            'current_stock' => $validated['current_stock'],
        ]);

        return redirect()->route('fish-batches.show', $fishBatch)
            ->with('success', 'Stok ikan berhasil diperbarui');
    }

    // Helper methods untuk chart data
    private function getGrowthChartData($fishBatch)
    {
        return $fishBatch->fishGrowthLogs->map(function ($log) {
            return [
                'week' => $log->week_number,
                'weight' => $log->avg_weight_gram,
                'length' => $log->avg_length_cm,
                'date' => $log->date_recorded->format('M d'),
                'growth_from_previous' => $log->weight_growth_from_previous,
                'growth_percentage' => $log->growth_percentage,
            ];
        });
    }

    private function getMortalityChartData($fishBatch)
    {
        $mortalityData = [];
        $cumulativeDeaths = 0;

        foreach ($fishBatch->mortalities->sortBy('date') as $mortality) {
            $cumulativeDeaths += $mortality->dead_count;
            $mortalityData[] = [
                'date' => $mortality->date->format('M d'),
                'daily_deaths' => $mortality->dead_count,
                'cumulative_deaths' => $cumulativeDeaths,
                'cause' => $mortality->cause,
                'mortality_rate' => round(($cumulativeDeaths / $fishBatch->initial_count) * 100, 2),
            ];
        }

        return collect($mortalityData);
    }

    private function getFeedingChartData($fishBatch)
    {
        $feedingData = [];
        $cumulativeFeed = 0;

        foreach ($fishBatch->feedings->sortBy('date') as $feeding) {
            $cumulativeFeed += $feeding->feed_amount_kg;
            $feedingData[] = [
                'date' => $feeding->date->format('M d'),
                'daily_feed' => $feeding->feed_amount_kg,
                'cumulative_feed' => round($cumulativeFeed, 2),
                'feed_type' => $feeding->feed_type,
                'feeding_rate' => $feeding->feeding_rate,
                'feed_per_fish' => $feeding->feed_per_fish,
            ];
        }

        return collect($feedingData);
    }

    private function getStockTimelineData($fishBatch)
    {
        $timeline = [];
        $currentStock = $fishBatch->initial_count;

        // Event awal
        $timeline[] = [
            'date' => $fishBatch->date_start->format('M d, Y'),
            'event' => 'Batch Started',
            'change' => $fishBatch->initial_count,
            'stock_after' => $currentStock,
            'type' => 'start',
        ];

        // Events kematian
        foreach ($fishBatch->mortalities->sortBy('date') as $mortality) {
            $currentStock -= $mortality->dead_count;
            $timeline[] = [
                'date' => $mortality->date->format('M d, Y'),
                'event' => 'Mortality',
                'change' => -$mortality->dead_count,
                'stock_after' => $currentStock,
                'type' => 'mortality',
                'details' => $mortality->cause,
            ];
        }

        // Events penjualan
        foreach ($fishBatch->sales->sortBy('date') as $sale) {
            $currentStock -= $sale->quantity_fish;
            $timeline[] = [
                'date' => $sale->date->format('M d, Y'),
                'event' => 'Sale',
                'change' => -$sale->quantity_fish,
                'stock_after' => $currentStock,
                'type' => 'sale',
                'details' => "Sold to {$sale->buyer_name}",
            ];
        }

        // Events transfer
        foreach ($fishBatch->sourceTransfers->sortBy('date_transfer') as $transfer) {
            $currentStock -= $transfer->transferred_count;
            $timeline[] = [
                'date' => $transfer->date_transfer->format('M d, Y'),
                'event' => 'Transfer Out',
                'change' => -$transfer->transferred_count,
                'stock_after' => $currentStock,
                'type' => 'transfer_out',
                'details' => "To {$transfer->targetBatch->pond->name}",
            ];
        }

        foreach ($fishBatch->targetTransfers->sortBy('date_transfer') as $transfer) {
            $currentStock += $transfer->transferred_count;
            $timeline[] = [
                'date' => $transfer->date_transfer->format('M d, Y'),
                'event' => 'Transfer In',
                'change' => $transfer->transferred_count,
                'stock_after' => $currentStock,
                'type' => 'transfer_in',
                'details' => "From {$transfer->sourceBatch->pond->name}",
            ];
        }

        return collect($timeline)->sortBy('date');
    }
}
