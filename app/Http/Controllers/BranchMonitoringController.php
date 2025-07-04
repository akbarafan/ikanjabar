<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Pond;
use App\Models\FishBatch;
use App\Models\WaterQuality;
use App\Models\Sale;
use App\Models\Feeding;
use App\Models\Mortality;
use App\Models\FishType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BranchMonitoringController extends Controller
{
    public function show(Branch $branch, Request $request)
    {
        $selectedPeriod = $request->get('period', '1month');
        $selectedPondId = $request->get('pond_id');

        // Get branch info
        $branchInfo = $branch;

        // Get statistics similar to user dashboard
        $statistics = $this->getBranchStatistics($branch);

        // Get pond options for filter
        $pondOptions = $branch->ponds()->get();

        // Get water quality trend
        $waterQualityTrend = $this->getWaterQualityTrend($branch, $selectedPondId);

        // Get production distribution
        $productionDistribution = $this->getProductionDistribution($branch);

        // Get pond stock details
        $pondStockDetails = $this->getPondStockDetails($branch);

        // Get fish sales analysis
        $fishSalesAnalysis = $this->getFishSalesAnalysis($branch, $selectedPeriod);

        // Get harvest predictions
        $harvestPredictions = $this->getHarvestPredictions($branch);

        // Get recent alerts
        $recentAlerts = $this->getRecentAlerts($branch);

        return view('admin.branches.monitoring', compact(
            'branch',
            'branchInfo',
            'selectedPeriod',
            'selectedPondId',
            'pondOptions',
            'waterQualityTrend',
            'productionDistribution',
            'pondStockDetails',
            'fishSalesAnalysis',
            'harvestPredictions',
            'recentAlerts'
        ) + $statistics);
    }

    private function getBranchStatistics(Branch $branch)
    {
        // Get ponds with their batches
        $ponds = $branch->ponds()->with(['fishBatches' => function($query) {
            $query->where('status', 'active');
        }])->get();

        $totalPonds = $ponds->count();

        // Get all active batches
        $activeBatches = FishBatch::whereHas('pond', function($query) use ($branch) {
            $query->where('branch_id', $branch->id);
        })->where('status', 'active')->get();

        $totalFish = $activeBatches->sum('current_stock');

        // Get mortality data
        $totalDeadFish = Mortality::whereHas('fishBatch.pond', function($query) use ($branch) {
            $query->where('branch_id', $branch->id);
        })->sum('quantity');

        // Get fish types count
        $totalFishTypes = $activeBatches->pluck('fish_type_id')->unique()->count();

        // Calculate monthly revenue and growth
        $currentMonth = Carbon::now()->startOfMonth();
        $previousMonth = Carbon::now()->subMonth()->startOfMonth();

        $currentMonthRevenue = Sale::whereHas('fishBatch.pond', function($query) use ($branch) {
            $query->where('branch_id', $branch->id);
        })
        ->where('created_at', '>=', $currentMonth)
        ->sum('total_price');

        $previousMonthRevenue = Sale::whereHas('fishBatch.pond', function($query) use ($branch) {
            $query->where('branch_id', $branch->id);
        })
        ->whereBetween('created_at', [$previousMonth, $currentMonth])
        ->sum('total_price');

        $revenueGrowth = 0;
        if ($previousMonthRevenue > 0) {
            $revenueGrowth = (($currentMonthRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100;
        }

        $monthlyRevenue = [
            'amount' => $currentMonthRevenue,
            'formatted' => 'Rp ' . number_format($currentMonthRevenue, 0, ',', '.'),
            'growth' => round($revenueGrowth, 1)
        ];

        // Calculate survival rate
        $totalInitialStock = $activeBatches->sum('initial_count');
        $survivalRate = $totalInitialStock > 0 ? (($totalFish / $totalInitialStock) * 100) : 0;

        // Calculate FCR
        $totalFeedUsed = Feeding::whereHas('fishBatch.pond', function($query) use ($branch) {
            $query->where('branch_id', $branch->id);
        })->sum('amount_kg');

        $totalSalesWeight = Sale::whereHas('fishBatch.pond', function($query) use ($branch) {
            $query->where('branch_id', $branch->id);
        })->sum('weight_kg');

        $fcr = $totalSalesWeight > 0 ? round($totalFeedUsed / $totalSalesWeight, 2) : 0;

        return [
            'totalPonds' => $totalPonds,
            'totalFish' => $totalFish,
            'totalDeadFish' => $totalDeadFish,
            'totalFishTypes' => $totalFishTypes,
            'monthlyRevenue' => $monthlyRevenue,
            'survival_rate' => round($survivalRate, 1),
            'fcr' => $fcr,
            'total_feed_used' => $totalFeedUsed,
            'total_sales_weight' => $totalSalesWeight
        ];
    }

    private function getWaterQualityTrend(Branch $branch, $selectedPondId = null)
    {
        $query = WaterQuality::whereHas('pond', function($q) use ($branch) {
            $q->where('branch_id', $branch->id);
        });

        if ($selectedPondId) {
            $query->where('pond_id', $selectedPondId);
        }

        $waterQualities = $query->where('created_at', '>=', Carbon::now()->subDays(7))
            ->orderBy('created_at')
            ->get()
            ->groupBy(function($item) {
                return $item->created_at->format('Y-m-d');
            });

        $labels = [];
        $temperature = [];
        $ph = [];
        $do = [];
        $ammonia = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dateKey = $date->format('Y-m-d');
            $labels[] = $date->format('M d');

            if (isset($waterQualities[$dateKey])) {
                $dayData = $waterQualities[$dateKey];
                $temperature[] = round($dayData->avg('temperature_c'), 1);
                $ph[] = round($dayData->avg('ph'), 1);
                $do[] = round($dayData->avg('do_mg_l'), 1);
                $ammonia[] = round($dayData->avg('ammonia_mg_l'), 1);
            } else {
                $temperature[] = null;
                $ph[] = null;
                $do[] = null;
                $ammonia[] = null;
            }
        }

        return [
            'labels' => $labels,
            'temperature' => $temperature,
            'ph' => $ph,
            'do' => $do,
            'ammonia' => $ammonia
        ];
    }

    private function getProductionDistribution(Branch $branch)
    {
        $production = FishBatch::whereHas('pond', function($query) use ($branch) {
            $query->where('branch_id', $branch->id);
        })
        ->with('fishType')
        ->where('status', 'active')
        ->get()
        ->groupBy('fish_type_id')
        ->map(function($batches) {
            return [
                'name' => $batches->first()->fishType->name,
                'stock' => $batches->sum('current_stock')
            ];
        });

        return [
            'labels' => $production->pluck('name')->toArray(),
            'data' => $production->pluck('stock')->toArray()
        ];
    }

    private function getPondStockDetails(Branch $branch)
    {
        return $branch->ponds()->with(['fishBatches' => function($query) {
            $query->where('status', 'active')->with('fishType');
        }])->get()->map(function($pond) {
            $activeBatch = $pond->fishBatches->first();

            // Calculate transfers
            $transferredIn = DB::table('fish_batch_transfers')
                ->where('destination_pond_id', $pond->id)
                ->sum('quantity');

            $transferredOut = DB::table('fish_batch_transfers')
                ->where('source_pond_id', $pond->id)
                ->sum('quantity');

            // Calculate sales and mortality
            $totalSold = 0;
            $totalDead = 0;

            if ($activeBatch) {
                $totalSold = Sale::where('fish_batch_id', $activeBatch->id)->sum('quantity');
                $totalDead = Mortality::where('fish_batch_id', $activeBatch->id)->sum('quantity');
            }

            return (object) [
                'pond_name' => $pond->name,
                'pond_code' => $pond->code,
                'pond_type' => $pond->type,
                'volume_liters' => $pond->volume_liters,
                'fish_type' => $activeBatch ? $activeBatch->fishType->name : null,
                'initial_count' => $activeBatch ? $activeBatch->initial_count : 0,
                'current_stock' => $activeBatch ? $activeBatch->current_stock : 0,
                'transferred_in' => $transferredIn,
                'transferred_out' => $transferredOut,
                'total_sold' => $totalSold,
                'total_dead' => $totalDead
            ];
        });
    }

    private function getFishSalesAnalysis(Branch $branch, $period)
    {
        $days = $this->getPeriodDays($period);
        $startDate = Carbon::now()->subDays($days);

        $sales = Sale::whereHas('fishBatch.pond', function($query) use ($branch) {
            $query->where('branch_id', $branch->id);
        })
        ->with('fishBatch.fishType')
        ->where('created_at', '>=', $startDate)
        ->get();

        $topFishSales = $sales->groupBy('fishBatch.fish_type_id')
            ->map(function($salesGroup) {
                $fishType = $salesGroup->first()->fishBatch->fishType;
                return (object) [
                    'fish_name' => $fishType->name,
                    'total_quantity' => $salesGroup->sum('quantity'),
                    'total_revenue' => $salesGroup->sum('total_price'),
                    'avg_price' => $salesGroup->avg('price_per_kg')
                ];
            })
            ->sortByDesc('total_revenue')
            ->take(5)
            ->values();

        $chartData = [
            'labels' => $topFishSales->pluck('fish_name')->toArray(),
            'revenues' => $topFishSales->pluck('total_revenue')->toArray()
        ];

        $periodLabel = $this->getPeriodLabel($period);

        return [
            'top_fish_sales' => $topFishSales,
            'chart_data' => $chartData,
            'period_label' => $periodLabel
        ];
    }

    private function getHarvestPredictions(Branch $branch)
    {
        return FishBatch::whereHas('pond', function($query) use ($branch) {
            $query->where('branch_id', $branch->id);
        })
        ->with(['fishType', 'pond'])
        ->where('status', 'active')
        ->get()
        ->map(function($batch) {
            $ageDays = $batch->created_at->diffInDays(now());
            $harvestAge = $batch->fishType->harvest_age_days ?? 90;
            $daysToHarvest = $harvestAge - $ageDays;
            $estimatedHarvest = $batch->created_at->addDays($harvestAge);

            $readiness = 'growing';
            if ($daysToHarvest <= 0) {
                $readiness = 'ready';
            } elseif ($daysToHarvest <= 14) {
                $readiness = 'soon';
            }

            return [
                'batch_id' => $batch->id,
                'pond_name' => $batch->pond->name,
                'fish_type' => $batch->fishType->name,
                'current_stock' => $batch->current_stock,
                'age_days' => $ageDays,
                'days_to_harvest' => max(0, $daysToHarvest),
                'estimated_harvest' => $estimatedHarvest,
                'readiness' => $readiness
            ];
        })
        ->sortBy('days_to_harvest');
    }

    private function getRecentAlerts(Branch $branch)
    {
        $alerts = [];

        // Water quality alerts
        $badWaterQuality = WaterQuality::whereHas('pond', function($query) use ($branch) {
            $query->where('branch_id', $branch->id);
        })
        ->where('created_at', '>=', Carbon::now()->subDays(7))
        ->where(function($query) {
            $query->where('ph', '<', 6.5)
                  ->orWhere('ph', '>', 8.5)
                  ->orWhere('do_mg_l', '<', 4)
                  ->orWhere('temperature_c', '>', 32);
        })
        ->with('pond')
        ->latest()
        ->take(10)
        ->get();

        foreach ($badWaterQuality as $wq) {
            $alerts[] = [
                'message' => 'Kualitas air buruk terdeteksi',
                'detail' => "pH: {$wq->ph}, DO: {$wq->do_mg_l} mg/L, Suhu: {$wq->temperature_c}°C di {$wq->pond->name}",
                'time' => $wq->created_at->diffForHumans()
            ];
        }

        return collect($alerts)->take(5);
    }

    private function getPeriodDays($period)
    {
        switch ($period) {
            case '1month':
                return 30;
            case '3months':
                return 90;
            case '6months':
                return 180;
            case '1year':
                return 365;
            default:
                return 30;
        }
    }

    private function getPeriodLabel($period)
    {
        switch ($period) {
            case '1month':
                return '30 Hari Terakhir';
            case '3months':
                return '3 Bulan Terakhir';
            case '6months':
                return '6 Bulan Terakhir';
            case '1year':
                return '1 Tahun Terakhir';
            default:
                return '30 Hari Terakhir';
        }
    }

    public function getChartData(Branch $branch, Request $request)
    {
        $type = $request->get('type');
        $period = $request->get('period', '1month');
        $pondId = $request->get('pond_id');

        switch ($type) {
            case 'water_quality':
                $data = $this->getWaterQualityTrend($branch, $pondId);
                return response()->json([
                    'success' => true,
                    'data' => $data
                ]);

            case 'production':
                $data = $this->getProductionDistribution($branch);
                return response()->json([
                    'success' => true,
                    'data' => $data
                ]);

            case 'sales':
                $data = $this->getFishSalesAnalysis($branch, $period);
                return response()->json([
                    'success' => true,
                    'data' => $data
                ]);

            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid chart type'
                ], 400);
        }
    }
}
