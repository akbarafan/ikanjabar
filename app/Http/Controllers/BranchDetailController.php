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

class BranchDetailController extends Controller
{
    public function show(Branch $branch, Request $request)
    {
        $selectedPeriod = $request->get('period', '1month');

        // Get branch statistics
        $statistics = $this->getBranchStatistics($branch);

        // Get chart data
        $waterQualityTrend = $this->getWaterQualityTrend($branch);
        $productionDistribution = $this->getProductionDistribution($branch);
        $fishSalesAnalysis = $this->getFishSalesAnalysis($branch, $selectedPeriod);

        // Get detailed information
        $pondStockDetails = $this->getPondStockDetails($branch);
        $harvestPredictions = $this->getHarvestPredictions($branch);
        $recentAlerts = $this->getRecentAlerts($branch);

        return view('admin.branches.show', array_merge($statistics, [
            'branch' => $branch,
            'selectedPeriod' => $selectedPeriod,
            'waterQualityTrend' => $waterQualityTrend,
            'productionDistribution' => $productionDistribution,
            'fishSalesAnalysis' => $fishSalesAnalysis,
            'pondStockDetails' => $pondStockDetails,
            'harvestPredictions' => $harvestPredictions,
            'recentAlerts' => $recentAlerts
        ]));
    }

    public function getChartData(Branch $branch, Request $request)
    {
        $type = $request->get('type');
        $period = $request->get('period', '1month');

        switch ($type) {
            case 'sales':
                $data = $this->getFishSalesAnalysis($branch, $period);
                return response()->json([
                    'success' => true,
                    'chart_data' => $data['chart_data'],
                    'summary' => $data['summary']
                ]);

            case 'water_quality':
                $data = $this->getWaterQualityTrend($branch, $period);
                return response()->json([
                    'success' => true,
                    'chart_data' => $data
                ]);

            case 'production':
                $data = $this->getProductionDistribution($branch, $period);
                return response()->json([
                    'success' => true,
                    'chart_data' => $data
                ]);

            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid chart type'
                ], 400);
        }
    }

    private function getBranchStatistics(Branch $branch)
    {
        // Get ponds with their batches
        $ponds = $branch->ponds()->with(['fishBatches' => function($query) {
            $query->where('status', 'active');
        }])->get();

        $totalPonds = $ponds->count();
        $totalActiveBatches = $ponds->pluck('fishBatches')->flatten()->count();

        // Calculate total fish stock
        $totalFishStock = $ponds->pluck('fishBatches')->flatten()->sum('current_stock');

        // Get feed usage (last 30 days)
        $totalFeedUsed = Feeding::whereHas('fishBatch.pond', function($query) use ($branch) {
            $query->where('branch_id', $branch->id);
        })
        ->where('created_at', '>=', Carbon::now()->subDays(30))
        ->sum('amount_kg');

        // Get sales data (last 30 days)
        $salesData = Sale::whereHas('fishBatch.pond', function($query) use ($branch) {
            $query->where('branch_id', $branch->id);
        })
        ->where('created_at', '>=', Carbon::now()->subDays(30))
        ->selectRaw('SUM(weight_kg) as total_weight, SUM(total_price) as total_revenue')
        ->first();

        $totalSalesWeight = $salesData->total_weight ?? 0;
        $totalSalesRevenue = $salesData->total_revenue ?? 0;

        // Get fish types count
        $totalFishTypes = FishBatch::whereHas('pond', function($query) use ($branch) {
            $query->where('branch_id', $branch->id);
        })
        ->where('status', 'active')
        ->distinct('fish_type_id')
        ->count();

        return [
            'totalPonds' => $totalPonds,
            'totalActiveBatches' => $totalActiveBatches,
            'totalFishStock' => $totalFishStock,
            'totalFeedUsed' => $totalFeedUsed,
            'totalSalesWeight' => $totalSalesWeight,
            'totalSalesRevenue' => $totalSalesRevenue,
            'totalFishTypes' => $totalFishTypes
        ];
    }

    private function getWaterQualityTrend(Branch $branch, $period = '7days')
    {
        $days = $this->getPeriodDays($period);
        $startDate = Carbon::now()->subDays($days);

        $waterQualities = WaterQuality::whereHas('pond', function($query) use ($branch) {
            $query->where('branch_id', $branch->id);
        })
        ->where('created_at', '>=', $startDate)
        ->orderBy('created_at')
        ->get()
        ->groupBy(function($item) {
            return $item->created_at->format('Y-m-d');
        });

        $labels = [];
        $temperature = [];
        $ph = [];
        $do = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dateKey = $date->format('Y-m-d');
            $labels[] = $date->format('M d');

            if (isset($waterQualities[$dateKey])) {
                $dayData = $waterQualities[$dateKey];
                $temperature[] = round($dayData->avg('temperature_c'), 1);
                $ph[] = round($dayData->avg('ph'), 1);
                $do[] = round($dayData->avg('do_mg_l'), 1);
            } else {
                $temperature[] = null;
                $ph[] = null;
                $do[] = null;
            }
        }

        return [
            'labels' => $labels,
            'temperature' => $temperature,
            'ph' => $ph,
            'do' => $do
        ];
    }

    private function getProductionDistribution(Branch $branch, $period = '1month')
    {
        $days = $this->getPeriodDays($period);
        $startDate = Carbon::now()->subDays($days);

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

    private function getFishSalesAnalysis(Branch $branch, $period = '1month')
    {
        $days = $this->getPeriodDays($period);
        $startDate = Carbon::now()->subDays($days);

        // Get sales data
        $sales = Sale::whereHas('fishBatch.pond', function($query) use ($branch) {
            $query->where('branch_id', $branch->id);
        })
        ->where('created_at', '>=', $startDate)
        ->orderBy('created_at')
        ->get();

        // Calculate summary
        $summary = [
            'total_revenue' => $sales->sum('total_price'),
            'total_weight' => $sales->sum('weight_kg'),
            'total_transactions' => $sales->count(),
            'avg_price_per_kg' => $sales->count() > 0 ? $sales->sum('total_price') / $sales->sum('weight_kg') : 0
        ];

        // Group by date for chart
        $salesByDate = $sales->groupBy(function($item) use ($period) {
            if (in_array($period, ['1week', '1month'])) {
                return $item->created_at->format('Y-m-d');
            } else {
                return $item->created_at->format('Y-m');
            }
        });

        $labels = [];
        $revenues = [];
        $weights = [];

        if (in_array($period, ['1week', '1month'])) {
            $daysToShow = $period == '1week' ? 7 : 30;
            for ($i = $daysToShow - 1; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $dateKey = $date->format('Y-m-d');
                $labels[] = $date->format('M d');

                if (isset($salesByDate[$dateKey])) {
                    $revenues[] = $salesByDate[$dateKey]->sum('total_price');
                    $weights[] = $salesByDate[$dateKey]->sum('weight_kg');
                } else {
                    $revenues[] = 0;
                    $weights[] = 0;
                }
            }
        } else {
            // For longer periods, group by month
            $monthsToShow = $period == '3months' ? 3 : ($period == '6months' ? 6 : 12);
            for ($i = $monthsToShow - 1; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $dateKey = $date->format('Y-m');
                $labels[] = $date->format('M Y');

                if (isset($salesByDate[$dateKey])) {
                    $revenues[] = $salesByDate[$dateKey]->sum('total_price');
                    $weights[] = $salesByDate[$dateKey]->sum('weight_kg');
                } else {
                    $revenues[] = 0;
                    $weights[] = 0;
                }
            }
        }

        return [
            'summary' => $summary,
            'chart_data' => [
                'labels' => $labels,
                'revenues' => $revenues,
                'weights' => $weights
            ]
        ];
    }

    private function getPondStockDetails(Branch $branch)
    {
        return $branch->ponds()->with(['fishBatches' => function($query) {
            $query->where('status', 'active')->with('fishType');
        }])->get()->map(function($pond) {
            $totalStock = $pond->fishBatches->sum('current_stock');
            $capacity = $pond->capacity ?? 10000; // Default capacity

            return [
                'pond_name' => $pond->name,
                'total_stock' => $totalStock,
                'capacity' => $capacity,
                'utilization' => $capacity > 0 ? ($totalStock / $capacity) * 100 : 0,
                'batches' => $pond->fishBatches->map(function($batch) {
                    return [
                        'fish_type' => $batch->fishType->name,
                        'current_stock' => $batch->current_stock,
                        'status' => $this->getBatchStatus($batch)
                    ];
                })
            ];
        });
    }

    private function getHarvestPredictions(Branch $branch)
    {
        return FishBatch::whereHas('pond', function($query) use ($branch) {
            $query->where('branch_id', $branch->id);
        })
        ->with('fishType')
        ->where('status', 'active')
        ->get()
        ->map(function($batch) {
            $ageDays = $batch->created_at->diffInDays(now());
            $harvestAge = $batch->fishType->harvest_age_days ?? 90;
            $estimatedWeight = $batch->current_stock * ($batch->fishType->average_weight_gram ?? 500) / 1000;

            return [
                'fish_type' => $batch->fishType->name,
                'current_stock' => $batch->current_stock,
                'age_days' => $ageDays,
                'harvest_age' => $harvestAge,
                'status' => $ageDays >= $harvestAge ? 'ready' : 'growing',
                'estimated_weight_kg' => number_format($estimatedWeight, 1),
                'estimated_harvest_date' => $batch->created_at->addDays($harvestAge)->format('d M Y')
            ];
        });
    }

    private function getRecentAlerts(Branch $branch)
    {
        $alerts = collect();

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
        ->latest()
        ->take(5)
        ->get();

        foreach ($badWaterQuality as $wq) {
            $alerts->push([
                'type' => 'water_quality',
                'severity' => 'danger',
                'title' => 'Kualitas Air Buruk',
                'message' => "pH: {$wq->ph}, DO: {$wq->do_mg_l} mg/L, Suhu: {$wq->temperature_c}°C di {$wq->pond->name}",
                'date' => $wq->created_at
            ]);
        }

        // High mortality alerts
        $highMortality = Mortality::whereHas('fishBatch.pond', function ($query) use ($branch) {
            $query->where('branch_id', $branch->id);
        })
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->where('quantity', '>', 50)
            ->latest()
            ->take(5)
            ->get();

        foreach ($highMortality as $mortality) {
            $alerts->push([
                'type' => 'mortality',
                'severity' => $mortality->quantity > 100 ? 'danger' : 'warning',
                'title' => 'Mortalitas Tinggi',
                'message' => "{$mortality->quantity} ekor mati di {$mortality->fishBatch->pond->name} - {$mortality->cause}",
                'date' => $mortality->created_at
            ]);
        }

        return $alerts->sortByDesc('date')->take(10);
    }

    private function getBatchStatus($batch)
    {
        $ageDays = $batch->created_at->diffInDays(now());
        $harvestAge = $batch->fishType->harvest_age_days ?? 90;

        if ($ageDays >= $harvestAge) {
            return ['status' => 'ready', 'color' => 'green'];
        } elseif ($ageDays >= $harvestAge * 0.8) {
            return ['status' => 'almost_ready', 'color' => 'yellow'];
        } else {
            return ['status' => 'growing', 'color' => 'blue'];
        }
    }

    private function getPeriodDays($period)
    {
        switch ($period) {
            case '1week':
                return 7;
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

    // API Methods
    public function apiShow(Branch $branch)
    {
        $statistics = $this->getBranchStatistics($branch);

        return response()->json([
            'success' => true,
            'data' => array_merge($statistics, [
                'branch' => $branch,
                'water_quality_trend' => $this->getWaterQualityTrend($branch),
                'production_distribution' => $this->getProductionDistribution($branch),
                'pond_stock_details' => $this->getPondStockDetails($branch),
                'harvest_predictions' => $this->getHarvestPredictions($branch),
                'recent_alerts' => $this->getRecentAlerts($branch)
            ])
        ]);
    }

    public function apiStatistics(Branch $branch)
    {
        $statistics = $this->getBranchStatistics($branch);

        return response()->json([
            'success' => true,
            'data' => $statistics
        ]);
    }
}
