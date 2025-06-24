<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\FishBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SaleController extends Controller
{
    public function index()
    {
        $sales = Sale::with(['fishBatch.pond.branch', 'fishBatch.fishType', 'creator'])
            ->when(request('search'), function($query) {
                $query->where('buyer_name', 'like', '%' . request('search') . '%')
                    ->orWhere('buyer_contact', 'like', '%' . request('search') . '%')
                    ->orWhereHas('fishBatch.pond', function($q) {
                        $q->where('name', 'like', '%' . request('search') . '%');
                    });
            })
            ->when(request('batch_id'), function($query) {
                $query->where('fish_batch_id', request('batch_id'));
            })
            ->when(request('date_from'), function($query) {
                $query->whereDate('date', '>=', request('date_from'));
            })
            ->when(request('date_to'), function($query) {
                $query->whereDate('date', '<=', request('date_to'));
            })
            ->when(request('min_amount'), function($query) {
                $query->where('total_amount', '>=', request('min_amount'));
            })
            ->latest('date')
            ->paginate(15);

        // Tambahkan perhitungan sale data
        foreach ($sales as $sale) {
            $sale->sale_data = [
                'price_per_fish' => $sale->price_per_fish,
                'price_per_kg' => $sale->price_per_kg,
                'profit_margin' => $sale->profit_margin,
                'batch_age_days' => $sale->fishBatch->age_in_days,
            ];
        }

        $batches = FishBatch::with(['pond', 'fishType'])->get();

        // Statistik ringkasan
        $statistics = [
            'total_sales_today' => Sale::whereDate('date', today())->sum('total_amount'),
            'total_sales_this_week' => Sale::whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])->sum('total_amount'),
            'total_sales_this_month' => Sale::whereMonth('date', now()->month)->sum('total_amount'),
            'total_fish_sold_month' => Sale::whereMonth('date', now()->month)->sum('quantity_fish'),
            'average_price_per_kg' => $this->calculateAveragePricePerKg(),
            'top_buyers' => $this->getTopBuyers(),
        ];

        return view('sales.index', compact('sales', 'batches', 'statistics'));
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

        return view('sales.create', compact('batches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fish_batch_id' => 'required|exists:fish_batches,id',
            'date' => 'required|date|before_or_equal:today',
            'buyer_name' => 'required|string|max:100',
            'buyer_contact' => 'nullable|string|max:50',
            'quantity_fish' => 'required|integer|min:1',
            'avg_weight_kg' => 'required|numeric|min:0.1',
            'price_per_kg' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'documentation_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        // Validasi jumlah ikan tidak melebihi stok
        $batch = FishBatch::find($validated['fish_batch_id']);
        $currentStock = $batch->current_stock;

        if ($validated['quantity_fish'] > $currentStock) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Jumlah ikan yang dijual ({$validated['quantity_fish']}) melebihi stok saat ini ({$currentStock})");
        }

        // Hitung total amount
        $validated['total_amount'] = $validated['quantity_fish'] * $validated['avg_weight_kg'] * $validated['price_per_kg'];

        if ($request->hasFile('documentation_file')) {
            $validated['documentation_file'] = $request->file('documentation_file')
                ->store('sale-documentation', 'public');
        }

        $validated['created_by'] = Auth::id();

        Sale::create($validated);

        return redirect()->route('sales.index')
            ->with('success', 'Data penjualan berhasil ditambahkan');
    }

    public function show(Sale $sale)
    {
        $sale->load(['fishBatch.pond.branch', 'fishBatch.fishType', 'creator']);

        // Analisis penjualan
        $analysis = [
            'sale_data' => [
                'quantity_fish' => $sale->quantity_fish,
                'avg_weight_kg' => $sale->avg_weight_kg,
                'total_weight_kg' => $sale->total_weight_kg,
                'price_per_kg' => $sale->price_per_kg,
                'price_per_fish' => $sale->price_per_fish,
                'total_amount' => $sale->total_amount,
            ],
            'profitability' => [
                'production_cost' => $sale->production_cost,
                'gross_profit' => $sale->gross_profit,
                'profit_margin' => $sale->profit_margin,
                'roi' => $sale->roi,
            ],
            'batch_context' => [
                'batch_age_days' => $sale->fishBatch->age_in_days,
                'batch_age_weeks' => $sale->fishBatch->age_in_weeks,
                'remaining_stock' => $sale->fishBatch->current_stock,
                'total_sold_to_date' => $sale->fishBatch->sales()->sum('quantity_fish'),
                'harvest_percentage' => $sale->harvest_percentage,
            ],
            'performance' => [
                'fcr' => $sale->fishBatch->fcr,
                'survival_rate' => $sale->fishBatch->survival_rate,
                'growth_rate' => $sale->fishBatch->average_growth_rate,
            ]
        ];

        // Riwayat penjualan batch
        $salesHistory = $sale->fishBatch->sales()
            ->orderBy('date', 'desc')
            ->limit(10)
            ->get();

        // Perbandingan harga pasar
        $marketComparison = $this->getMarketPriceComparison($sale);

        return view('sales.show', compact('sale', 'analysis', 'salesHistory', 'marketComparison'));
    }

    public function edit(Sale $sale)
    {
        $batches = FishBatch::with(['pond.branch', 'fishType'])->get();

        return view('sales.edit', compact('sale', 'batches'));
    }

    public function update(Request $request, Sale $sale)
    {
        $validated = $request->validate([
            'fish_batch_id' => 'required|exists:fish_batches,id',
            'date' => 'required|date|before_or_equal:today',
            'buyer_name' => 'required|string|max:100',
            'buyer_contact' => 'nullable|string|max:50',
            'quantity_fish' => 'required|integer|min:1',
            'avg_weight_kg' => 'required|numeric|min:0.1',
            'price_per_kg' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'documentation_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        // Hitung total amount
        $validated['total_amount'] = $validated['quantity_fish'] * $validated['avg_weight_kg'] * $validated['price_per_kg'];

        if ($request->hasFile('documentation_file')) {
            // Hapus file lama jika ada
            if ($sale->documentation_file) {
                Storage::disk('public')->delete($sale->documentation_file);
            }

            $validated['documentation_file'] = $request->file('documentation_file')
                ->store('sale-documentation', 'public');
        }

        $sale->update($validated);

        return redirect()->route('sales.show', $sale)
            ->with('success', 'Data penjualan berhasil diperbarui');
    }

    public function destroy(Sale $sale)
    {
        // Hapus file dokumentasi jika ada
        if ($sale->documentation_file) {
            Storage::disk('public')->delete($sale->documentation_file);
        }

        $sale->delete();

        return redirect()->route('sales.index')
            ->with('success', 'Data penjualan berhasil dihapus');
    }

    public function analytics()
    {
        // Data untuk dashboard analitik penjualan
        $analytics = [
            'overview' => [
                'total_sales_today' => Sale::whereDate('date', today())->sum('total_amount'),
                'total_sales_week' => Sale::whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])->sum('total_amount'),
                'total_sales_month' => Sale::whereMonth('date', now()->month)->sum('total_amount'),
                'total_fish_sold_month' => Sale::whereMonth('date', now()->month)->sum('quantity_fish'),
                'average_price_per_kg' => $this->calculateAveragePricePerKg(),
            ],
            'trends' => [
                'daily_sales' => $this->getDailySalesTrend(),
                'weekly_sales' => $this->getWeeklySalesTrend(),
                'monthly_sales' => $this->getMonthlySalesTrend(),
                'price_trends' => $this->getPriceTrends(),
            ],
            'buyers' => [
                'top_buyers' => $this->getTopBuyers(),
                'buyer_analysis' => $this->getBuyerAnalysis(),
            ],
            'profitability' => [
                'profit_trends' => $this->getProfitTrends(),
                'roi_analysis' => $this->getROIAnalysis(),
                'cost_breakdown' => $this->getCostBreakdown(),
            ],
            'performance' => [
                'sales_by_batch_age' => $this->getSalesByBatchAge(),
                'harvest_efficiency' => $this->getHarvestEfficiency(),
            ]
        ];

        return view('sales.analytics', compact('analytics'));
    }

    public function invoice(Sale $sale)
    {
        $sale->load(['fishBatch.pond.branch', 'fishBatch.fishType', 'creator']);

        return view('sales.invoice', compact('sale'));
    }

    // Helper methods
    private function calculateAveragePricePerKg()
    {
        $sales = Sale::whereMonth('date', now()->month)->get();

        if ($sales->isEmpty()) return 0;

        $totalWeight = $sales->sum('total_weight_kg');
        $totalAmount = $sales->sum('total_amount');

        return $totalWeight > 0 ? round($totalAmount / $totalWeight, 2) : 0;
    }

    private function getTopBuyers()
    {
        return Sale::selectRaw('buyer_name, SUM(total_amount) as total_purchase, SUM(quantity_fish) as total_fish, COUNT(*) as transaction_count')
            ->groupBy('buyer_name')
            ->orderBy('total_purchase', 'desc')
            ->limit(10)
            ->get();
    }

    private function getMarketPriceComparison($sale)
    {
        // Ambil harga rata-rata untuk jenis ikan yang sama dalam 30 hari terakhir
        $avgPrice = Sale::whereHas('fishBatch', function($query) use ($sale) {
                $query->where('fish_type_id', $sale->fishBatch->fish_type_id);
            })
            ->whereBetween('date', [now()->subDays(30), now()])
            ->avg('price_per_kg');

        $comparison = [
            'current_price' => $sale->price_per_kg,
            'market_average' => round($avgPrice, 2),
            'difference' => round($sale->price_per_kg - $avgPrice, 2),
            'percentage_diff' => $avgPrice > 0 ? round((($sale->price_per_kg - $avgPrice) / $avgPrice) * 100, 2) : 0,
        ];

        return $comparison;
    }

    private function getDailySalesTrend()
    {
        $trend = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $sales = Sale::whereDate('date', $date)->sum('total_amount');
            $quantity = Sale::whereDate('date', $date)->sum('quantity_fish');

            $trend[] = [
                'date' => $date->format('M d'),
                'sales' => $sales,
                'quantity' => $quantity,
            ];
        }

        return $trend;
    }

    private function getWeeklySalesTrend()
    {
        $trend = [];
        for ($i = 11; $i >= 0; $i--) {
            $startOfWeek = now()->subWeeks($i)->startOfWeek();
            $endOfWeek = now()->subWeeks($i)->endOfWeek();

            $sales = Sale::whereBetween('date', [$startOfWeek, $endOfWeek])->sum('total_amount');
            $quantity = Sale::whereBetween('date', [$startOfWeek, $endOfWeek])->sum('quantity_fish');

            $trend[] = [
                'week' => $startOfWeek->format('M d') . ' - ' . $endOfWeek->format('M d'),
                'sales' => $sales,
                'quantity' => $quantity,
            ];
        }

        return $trend;
    }

    private function getMonthlySalesTrend()
    {
        $trend = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $sales = Sale::whereYear('date', $month->year)
                ->whereMonth('date', $month->month)
                ->sum('total_amount');
            $quantity = Sale::whereYear('date', $month->year)
                ->whereMonth('date', $month->month)
                ->sum('quantity_fish');

            $trend[] = [
                'month' => $month->format('M Y'),
                'sales' => $sales,
                'quantity' => $quantity,
            ];
        }

        return $trend;
    }

    private function getPriceTrends()
    {
        $trends = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);

            $avgPrice = Sale::whereYear('date', $month->year)
                ->whereMonth('date', $month->month)
                ->selectRaw('AVG(price_per_kg) as avg_price, MIN(price_per_kg) as min_price, MAX(price_per_kg) as max_price')
                ->first();

            $trends[] = [
                'month' => $month->format('M Y'),
                'avg_price' => round($avgPrice->avg_price ?? 0, 2),
                'min_price' => round($avgPrice->min_price ?? 0, 2),
                'max_price' => round($avgPrice->max_price ?? 0, 2),
            ];
        }

        return $trends;
    }

    private function getBuyerAnalysis()
    {
        $buyers = Sale::selectRaw('buyer_name, COUNT(*) as transaction_count, AVG(price_per_kg) as avg_price, SUM(total_amount) as total_purchase')
            ->groupBy('buyer_name')
            ->having('transaction_count', '>=', 2)
            ->orderBy('total_purchase', 'desc')
            ->get();

        return $buyers->map(function($buyer) {
            return [
                'name' => $buyer->buyer_name,
                'transaction_count' => $buyer->transaction_count,
                'avg_price' => round($buyer->avg_price, 2),
                'total_purchase' => $buyer->total_purchase,
                'loyalty_score' => $this->calculateLoyaltyScore($buyer),
            ];
        });
    }

    private function calculateLoyaltyScore($buyer)
    {
        // Skor berdasarkan frekuensi transaksi dan total pembelian
        $frequencyScore = min($buyer->transaction_count * 10, 50);
        $valueScore = min($buyer->total_purchase / 1000000 * 25, 50);

        return round($frequencyScore + $valueScore, 2);
    }

    private function getProfitTrends()
    {
        $trends = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);

            $sales = Sale::with('fishBatch')
                ->whereYear('date', $month->year)
                ->whereMonth('date', $month->month)
                ->get();

            $totalRevenue = $sales->sum('total_amount');
            $totalProfit = $sales->sum('gross_profit');
            $avgMargin = $sales->avg('profit_margin');

            $trends[] = [
                'month' => $month->format('M Y'),
                'revenue' => $totalRevenue,
                'profit' => $totalProfit,
                'margin' => round($avgMargin ?? 0, 2),
            ];
        }

        return $trends;
    }

    private function getROIAnalysis()
    {
        $batches = FishBatch::with(['sales', 'feedings', 'mortalities'])
            ->whereHas('sales')
            ->get();

        return $batches->map(function($batch) {
            $totalRevenue = $batch->sales->sum('total_amount');
            $totalCost = $batch->total_production_cost;
            $roi = $totalCost > 0 ? round((($totalRevenue - $totalCost) / $totalCost) * 100, 2) : 0;

            return [
                'batch_id' => $batch->id,
                'pond_name' => $batch->pond->name,
                'fish_type' => $batch->fishType->name,
                'revenue' => $totalRevenue,
                'cost' => $totalCost,
                'profit' => $totalRevenue - $totalCost,
                'roi' => $roi,
                'age_days' => $batch->age_in_days,
            ];
        })->sortByDesc('roi')->take(20);
    }

    private function getCostBreakdown()
    {
        $batches = FishBatch::with(['sales', 'feedings'])->whereHas('sales')->get();

        $totalFeedCost = 0;
        $totalOperationalCost = 0;
        $totalRevenue = 0;

        foreach ($batches as $batch) {
            $feedCost = $batch->feedings->sum('feed_cost');
            $operationalCost = $batch->initial_count * 500; // Estimasi biaya operasional per ekor

            $totalFeedCost += $feedCost;
            $totalOperationalCost += $operationalCost;
            $totalRevenue += $batch->sales->sum('total_amount');
        }

        return [
            'feed_cost' => $totalFeedCost,
            'operational_cost' => $totalOperationalCost,
            'total_cost' => $totalFeedCost + $totalOperationalCost,
            'total_revenue' => $totalRevenue,
            'gross_profit' => $totalRevenue - ($totalFeedCost + $totalOperationalCost),
            'feed_cost_percentage' => $totalRevenue > 0 ? round(($totalFeedCost / $totalRevenue) * 100, 2) : 0,
            'operational_cost_percentage' => $totalRevenue > 0 ? round(($totalOperationalCost / $totalRevenue) * 100, 2) : 0,
        ];
    }

    private function getSalesByBatchAge()
    {
        $ageGroups = [
            '60-90 days' => [60, 90],
            '91-120 days' => [91, 120],
            '121-150 days' => [121, 150],
            '151-180 days' => [151, 180],
            '180+ days' => [181, 999],
        ];

        $salesByAge = [];

        foreach ($ageGroups as $group => $range) {
            $sales = Sale::with('fishBatch')
                ->whereHas('fishBatch', function ($query) use ($range) {
                    $query->whereRaw('DATEDIFF(NOW(), date_start) BETWEEN ? AND ?', $range);
                })
                ->get();

            $totalRevenue = $sales->sum('total_amount');
            $totalQuantity = $sales->sum('quantity_fish');
            $avgPrice = $sales->avg('price_per_kg');
            $avgWeight = $sales->avg('avg_weight_kg');

            $salesByAge[] = [
                'age_group' => $group,
                'total_revenue' => $totalRevenue,
                'total_quantity' => $totalQuantity,
                'avg_price_per_kg' => round($avgPrice ?? 0, 2),
                'avg_weight_per_fish' => round($avgWeight ?? 0, 2),
                'transaction_count' => $sales->count(),
            ];
        }

        return $salesByAge;
    }

    private function getHarvestEfficiency()
    {
        $batches = FishBatch::with(['sales', 'mortalities'])
            ->whereHas('sales')
            ->get();

        return $batches->map(function ($batch) {
            $totalSold = $batch->sales->sum('quantity_fish');
            $totalDead = $batch->mortalities->sum('dead_count');
            $remaining = $batch->current_stock;

            $harvestRate = $batch->initial_count > 0 ? round(($totalSold / $batch->initial_count) * 100, 2) : 0;
            $survivalRate = $batch->survival_rate;
            $efficiency = $survivalRate > 0 ? round(($harvestRate / $survivalRate) * 100, 2) : 0;

            return [
                'batch_id' => $batch->id,
                'pond_name' => $batch->pond->name,
                'fish_type' => $batch->fishType->name,
                'initial_count' => $batch->initial_count,
                'total_sold' => $totalSold,
                'total_dead' => $totalDead,
                'remaining' => $remaining,
                'harvest_rate' => $harvestRate,
                'survival_rate' => $survivalRate,
                'harvest_efficiency' => $efficiency,
                'age_days' => $batch->age_in_days,
            ];
        })->sortByDesc('harvest_efficiency')->take(20);
    }
}
