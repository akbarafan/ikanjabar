<?php

namespace App\Http\Controllers;

use App\Exports\ReportExport;
use App\Models\FishBatch;
use App\Models\Mortality;
use App\Models\Feeding;
use App\Models\Sale;
use App\Models\WaterQuality;
use App\Models\Branch;
use App\Models\Pond;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index()
    {
        $branches = Branch::with('ponds')->get();
        $reportTypes = [
            'production' => 'Laporan Produksi',
            'financial' => 'Laporan Keuangan',
            'mortality' => 'Laporan Kematian',
            'feeding' => 'Laporan Pemberian Pakan',
            'water_quality' => 'Laporan Kualitas Air',
            'comprehensive' => 'Laporan Komprehensif',
        ];

        return view('reports.index', compact('branches', 'reportTypes'));
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'report_type' => 'required|in:production,financial,mortality,feeding,water_quality,comprehensive',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'branch_id' => 'nullable|exists:branches,id',
            'pond_id' => 'nullable|exists:ponds,id',
            'format' => 'required|in:html,pdf,excel',
        ]);

        $reportData = $this->generateReportData($validated);

        switch ($validated['format']) {
            case 'pdf':
                return $this->generatePDF($reportData, $validated);
            case 'excel':
                return $this->generateExcel($reportData, $validated);
            default:
                return view('reports.view', compact('reportData', 'validated'));
        }
    }

    private function generateReportData($params)
    {
        $dateFrom = Carbon::parse($params['date_from']);
        $dateTo = Carbon::parse($params['date_to']);

        $data = [
            'period' => [
                'from' => $dateFrom,
                'to' => $dateTo,
                'days' => $dateFrom->diffInDays($dateTo) + 1,
            ],
            'filters' => $params,
        ];

        switch ($params['report_type']) {
            case 'production':
                $data = array_merge($data, $this->getProductionReport($params, $dateFrom, $dateTo));
                break;
            case 'financial':
                $data = array_merge($data, $this->getFinancialReport($params, $dateFrom, $dateTo));
                break;
            case 'mortality':
                $data = array_merge($data, $this->getMortalityReport($params, $dateFrom, $dateTo));
                break;
            case 'feeding':
                $data = array_merge($data, $this->getFeedingReport($params, $dateFrom, $dateTo));
                break;
            case 'water_quality':
                $data = array_merge($data, $this->getWaterQualityReport($params, $dateFrom, $dateTo));
                break;
            case 'comprehensive':
                $data = array_merge($data, $this->getComprehensiveReport($params, $dateFrom, $dateTo));
                break;
        }

        return $data;
    }

    private function getProductionReport($params, $dateFrom, $dateTo)
    {
        $batchesQuery = FishBatch::with(['pond.branch', 'fishType', 'sales', 'mortalities', 'feedings']);

        if ($params['branch_id']) {
            $batchesQuery->whereHas('pond', function($query) use ($params) {
                $query->where('branch_id', $params['branch_id']);
            });
        }

        if ($params['pond_id']) {
            $batchesQuery->where('pond_id', $params['pond_id']);
        }

        $batches = $batchesQuery->whereBetween('date_start', [$dateFrom, $dateTo])->get();

        // Summary statistics
        $summary = [
            'total_batches' => $batches->count(),
            'total_initial_stock' => $batches->sum('initial_count'),
            'total_current_stock' => $batches->sum(function($batch) {
                return $batch->current_stock;
            }),
            'total_harvested' => $batches->sum(function($batch) {
                return $batch->sales->sum('quantity_fish');
            }),
            'total_mortality' => $batches->sum(function($batch) {
                return $batch->mortalities->sum('dead_count');
            }),
            'average_survival_rate' => $batches->avg(function($batch) {
                return $batch->survival_rate;
            }),
            'average_fcr' => $batches->filter(function($batch) {
                return $batch->fcr > 0 && $batch->fcr < 10;
            })->avg('fcr'),
        ];

        // Batch details
        $batchDetails = $batches->map(function($batch) {
            return [
                'id' => $batch->id,
                'pond_name' => $batch->pond->name,
                'branch_name' => $batch->pond->branch->name,
                'fish_type' => $batch->fishType->name,
                'date_start' => $batch->date_start,
                'age_days' => $batch->age_in_days,
                'initial_count' => $batch->initial_count,
                'current_stock' => $batch->current_stock,
                'total_harvested' => $batch->sales->sum('quantity_fish'),
                'total_mortality' => $batch->mortalities->sum('dead_count'),
                'survival_rate' => $batch->survival_rate,
                'fcr' => $batch->fcr,
                'total_feed' => $batch->total_feed_given,
                'revenue' => $batch->sales->sum('total_amount'),
            ];
        });

        // Performance by branch
        $branchPerformance = $batches->groupBy('pond.branch.name')->map(function($branchBatches, $branchName) {
            return [
                'branch_name' => $branchName,
                'batch_count' => $branchBatches->count(),
                'total_stock' => $branchBatches->sum('initial_count'),
                'survival_rate' => $branchBatches->avg(function($batch) {
                    return $batch->survival_rate;
                }),
                'avg_fcr' => $branchBatches->filter(function($batch) {
                    return $batch->fcr > 0 && $batch->fcr < 10;
                })->avg('fcr'),
                'total_revenue' => $branchBatches->sum(function($batch) {
                    return $batch->sales->sum('total_amount');
                }),
            ];
        });

        return [
            'summary' => $summary,
            'batch_details' => $batchDetails,
            'branch_performance' => $branchPerformance,
        ];
    }

    private function getFinancialReport($params, $dateFrom, $dateTo)
    {
        $salesQuery = Sale::with(['fishBatch.pond.branch', 'fishBatch.fishType']);
        $feedingQuery = Feeding::with(['fishBatch.pond.branch']);

        if ($params['branch_id']) {
            $salesQuery->whereHas('fishBatch.pond', function($query) use ($params) {
                $query->where('branch_id', $params['branch_id']);
            });
            $feedingQuery->whereHas('fishBatch.pond', function($query) use ($params) {
                $query->where('branch_id', $params['branch_id']);
            });
        }

        if ($params['pond_id']) {
            $salesQuery->whereHas('fishBatch', function($query) use ($params) {
                $query->where('pond_id', $params['pond_id']);
            });
            $feedingQuery->whereHas('fishBatch', function($query) use ($params) {
                $query->where('pond_id', $params['pond_id']);
            });
        }

        $sales = $salesQuery->whereBetween('date', [$dateFrom, $dateTo])->get();
        $feedings = $feedingQuery->whereBetween('date', [$dateFrom, $dateTo])->get();

        // Revenue analysis
        $revenue = [
            'total_revenue' => $sales->sum('total_amount'),
            'total_quantity_sold' => $sales->sum('quantity_fish'),
            'total_weight_sold' => $sales->sum('total_weight_kg'),
            'average_price_per_kg' => $sales->avg('price_per_kg'),
            'transaction_count' => $sales->count(),
        ];

        // Cost analysis
        $costs = [
            'feed_cost' => $feedings->sum('feed_cost'),
            'total_feed_kg' => $feedings->sum('feed_amount_kg'),
            'average_feed_cost_per_kg' => $feedings->where('feed_cost', '>', 0)->avg(function($feeding) {
                return $feeding->feed_cost / $feeding->feed_amount_kg;
            }),
        ];

        // Profitability
        $profitability = [
            'gross_profit' => $revenue['total_revenue'] - $costs['feed_cost'],
            'profit_margin' => $revenue['total_revenue'] > 0 ?
                round((($revenue['total_revenue'] - $costs['feed_cost']) / $revenue['total_revenue']) * 100, 2) : 0,
        ];

        // Daily revenue trend
        $dailyRevenue = [];
        $currentDate = $dateFrom->copy();
        while ($currentDate <= $dateTo) {
            $dayRevenue = $sales->where('date', $currentDate->format('Y-m-d'))->sum('total_amount');
            $dailyRevenue[] = [
                'date' => $currentDate->format('Y-m-d'),
                'revenue' => $dayRevenue,
            ];
            $currentDate->addDay();
        }

        // Top buyers
        $topBuyers = $sales->groupBy('buyer_name')->map(function($buyerSales, $buyerName) {
            return [
                'buyer_name' => $buyerName,
                'total_purchase' => $buyerSales->sum('total_amount'),
                'transaction_count' => $buyerSales->count(),
                'avg_price' => $buyerSales->avg('price_per_kg'),
            ];
        })->sortByDesc('total_purchase')->take(10);

        return [
            'revenue' => $revenue,
            'costs' => $costs,
            'profitability' => $profitability,
            'daily_revenue' => $dailyRevenue,
            'top_buyers' => $topBuyers,
        ];
    }

    private function getMortalityReport($params, $dateFrom, $dateTo)
    {
        $mortalityQuery = Mortality::with(['fishBatch.pond.branch', 'fishBatch.fishType']);

        if ($params['branch_id']) {
            $mortalityQuery->whereHas('fishBatch.pond', function($query) use ($params) {
                $query->where('branch_id', $params['branch_id']);
            });
        }

        if ($params['pond_id']) {
            $mortalityQuery->whereHas('fishBatch', function($query) use ($params) {
                $query->where('pond_id', $params['pond_id']);
            });
        }

        $mortalities = $mortalityQuery->whereBetween('date', [$dateFrom, $dateTo])->get();

        // Summary statistics
        $summary = [
            'total_incidents' => $mortalities->count(),
            'total_deaths' => $mortalities->sum('dead_count'),
            'average_deaths_per_incident' => $mortalities->avg('dead_count'),
            'most_common_cause' => $mortalities->groupBy('cause')->map->count()->sortDesc()->keys()->first(),
        ];

        // Mortality by cause
        $byCause = $mortalities->groupBy('cause')->map(function($causeMortalities, $cause) {
            return [
                'cause' => $cause,
                'incident_count' => $causeMortalities->count(),
                'total_deaths' => $causeMortalities->sum('dead_count'),
                'percentage' => 0, // Will be calculated after
            ];
        });

        // Calculate percentages
        $totalDeaths = $summary['total_deaths'];
        $byCause = $byCause->map(function($item) use ($totalDeaths) {
            $item['percentage'] = $totalDeaths > 0 ? round(($item['total_deaths'] / $totalDeaths) * 100, 2) : 0;
            return $item;
        })->sortByDesc('total_deaths');

        // Daily mortality trend
        $dailyMortality = [];
        $currentDate = $dateFrom->copy();
        while ($currentDate <= $dateTo) {
            $dayDeaths = $mortalities->where('date', $currentDate->format('Y-m-d'))->sum('dead_count');
            $dailyMortality[] = [
                'date' => $currentDate->format('Y-m-d'),
                'deaths' => $dayDeaths,
            ];
            $currentDate->addDay();
        }

        // Mortality by batch
        $byBatch = $mortalities->groupBy('fish_batch_id')->map(function($batchMortalities) {
            $batch = $batchMortalities->first()->fishBatch;
            return [
                'batch_id' => $batch->id,
                'pond_name' => $batch->pond->name,
                'branch_name' => $batch->pond->branch->name,
                'fish_type' => $batch->fishType->name,
                'total_deaths' => $batchMortalities->sum('dead_count'),
                'incident_count' => $batchMortalities->count(),
                'mortality_rate' => $batch->initial_count > 0 ?
                    round(($batchMortalities->sum('dead_count') / $batch->initial_count) * 100, 2) : 0,
            ];
        })->sortByDesc('total_deaths');

        // Seasonal patterns
        $seasonalPatterns = $mortalities->groupBy(function($mortality) {
            return $mortality->date->format('M');
        })->map(function($monthMortalities, $month) {
            return [
                'month' => $month,
                'total_deaths' => $monthMortalities->sum('dead_count'),
                'incident_count' => $monthMortalities->count(),
            ];
        });

        return [
            'summary' => $summary,
            'by_cause' => $byCause,
            'daily_mortality' => $dailyMortality,
            'by_batch' => $byBatch,
            'seasonal_patterns' => $seasonalPatterns,
        ];
    }

    private function getFeedingReport($params, $dateFrom, $dateTo)
    {
        $feedingQuery = Feeding::with(['fishBatch.pond.branch', 'fishBatch.fishType']);

        if ($params['branch_id']) {
            $feedingQuery->whereHas('fishBatch.pond', function($query) use ($params) {
                $query->where('branch_id', $params['branch_id']);
            });
        }

        if ($params['pond_id']) {
            $feedingQuery->whereHas('fishBatch', function($query) use ($params) {
                $query->where('pond_id', $params['pond_id']);
            });
        }

        $feedings = $feedingQuery->whereBetween('date', [$dateFrom, $dateTo])->get();

        // Summary statistics
        $summary = [
            'total_feeding_sessions' => $feedings->count(),
            'total_feed_amount' => $feedings->sum('feed_amount_kg'),
            'total_feed_cost' => $feedings->sum('feed_cost'),
            'average_feed_per_session' => $feedings->avg('feed_amount_kg'),
            'average_cost_per_kg' => $feedings->where('feed_cost', '>', 0)->avg(function($feeding) {
                return $feeding->feed_cost / $feeding->feed_amount_kg;
            }),
        ];

        // Feed consumption by type
        $byFeedType = $feedings->groupBy('feed_type')->map(function($typeFeedings, $type) {
            return [
                'feed_type' => $type,
                'total_amount' => $typeFeedings->sum('feed_amount_kg'),
                'total_cost' => $typeFeedings->sum('feed_cost'),
                'session_count' => $typeFeedings->count(),
                'avg_cost_per_kg' => $typeFeedings->where('feed_cost', '>', 0)->avg(function($feeding) {
                    return $feeding->feed_cost / $feeding->feed_amount_kg;
                }),
            ];
        })->sortByDesc('total_amount');

        // Daily feeding trend
        $dailyFeeding = [];
        $currentDate = $dateFrom->copy();
        while ($currentDate <= $dateTo) {
            $dayFeeding = $feedings->where('date', $currentDate->format('Y-m-d'));
            $dailyFeeding[] = [
                'date' => $currentDate->format('Y-m-d'),
                'amount' => $dayFeeding->sum('feed_amount_kg'),
                'cost' => $dayFeeding->sum('feed_cost'),
                'sessions' => $dayFeeding->count(),
            ];
            $currentDate->addDay();
        }

        // FCR analysis by batch
        $fcrAnalysis = $feedings->groupBy('fish_batch_id')->map(function($batchFeedings) {
            $batch = $batchFeedings->first()->fishBatch;
            $totalFeed = $batchFeedings->sum('feed_amount_kg');
            $totalHarvest = $batch->sales->sum('total_weight_kg');
            $fcr = $totalHarvest > 0 ? round($totalFeed / $totalHarvest, 2) : 0;

            return [
                'batch_id' => $batch->id,
                'pond_name' => $batch->pond->name,
                'branch_name' => $batch->pond->branch->name,
                'fish_type' => $batch->fishType->name,
                'total_feed' => $totalFeed,
                'total_harvest' => $totalHarvest,
                'fcr' => $fcr,
                'feed_cost' => $batchFeedings->sum('feed_cost'),
            ];
        })->filter(function($item) {
            return $item['fcr'] > 0 && $item['fcr'] < 10;
        })->sortBy('fcr');

        // Cost efficiency analysis
        $costEfficiency = [
            'lowest_cost_per_kg' => $feedings->where('feed_cost', '>', 0)->min(function($feeding) {
                return $feeding->feed_cost / $feeding->feed_amount_kg;
            }),
            'highest_cost_per_kg' => $feedings->where('feed_cost', '>', 0)->max(function($feeding) {
                return $feeding->feed_cost / $feeding->feed_amount_kg;
            }),
            'cost_variance' => $this->calculateCostVariance($feedings),
        ];

        return [
            'summary' => $summary,
            'by_feed_type' => $byFeedType,
            'daily_feeding' => $dailyFeeding,
            'fcr_analysis' => $fcrAnalysis,
            'cost_efficiency' => $costEfficiency,
        ];
    }

    private function getWaterQualityReport($params, $dateFrom, $dateTo)
    {
        $waterQualityQuery = WaterQuality::with(['pond.branch']);

        if ($params['branch_id']) {
            $waterQualityQuery->whereHas('pond', function($query) use ($params) {
                $query->where('branch_id', $params['branch_id']);
            });
        }

        if ($params['pond_id']) {
            $waterQualityQuery->where('pond_id', $params['pond_id']);
        }

        $waterQualities = $waterQualityQuery->whereBetween('date', [$dateFrom, $dateTo])->get();

        // Summary statistics
        $summary = [
            'total_measurements' => $waterQualities->count(),
            'avg_temperature' => round($waterQualities->avg('temperature'), 2),
            'avg_ph' => round($waterQualities->avg('ph'), 2),
            'avg_dissolved_oxygen' => round($waterQualities->avg('dissolved_oxygen'), 2),
            'avg_ammonia' => round($waterQualities->avg('ammonia'), 2),
            'avg_nitrite' => round($waterQualities->avg('nitrite'), 2),
            'avg_nitrate' => round($waterQualities->avg('nitrate'), 2),
        ];

        // Parameter ranges and quality assessment
        $parameterAnalysis = [
            'temperature' => $this->analyzeParameter($waterQualities, 'temperature', [25, 30]),
            'ph' => $this->analyzeParameter($waterQualities, 'ph', [6.5, 8.5]),
            'dissolved_oxygen' => $this->analyzeParameter($waterQualities, 'dissolved_oxygen', [5, 15]),
            'ammonia' => $this->analyzeParameter($waterQualities, 'ammonia', [0, 0.5]),
            'nitrite' => $this->analyzeParameter($waterQualities, 'nitrite', [0, 0.1]),
            'nitrate' => $this->analyzeParameter($waterQualities, 'nitrate', [0, 40]),
        ];

        // Daily trends
        $dailyTrends = [];
        $currentDate = $dateFrom->copy();
        while ($currentDate <= $dateTo) {
            $dayMeasurements = $waterQualities->where('date', $currentDate->format('Y-m-d'));
            if ($dayMeasurements->isNotEmpty()) {
                $dailyTrends[] = [
                    'date' => $currentDate->format('Y-m-d'),
                    'temperature' => round($dayMeasurements->avg('temperature'), 2),
                    'ph' => round($dayMeasurements->avg('ph'), 2),
                    'dissolved_oxygen' => round($dayMeasurements->avg('dissolved_oxygen'), 2),
                    'ammonia' => round($dayMeasurements->avg('ammonia'), 2),
                    'measurement_count' => $dayMeasurements->count(),
                ];
            }
            $currentDate->addDay();
        }

        // Quality alerts (measurements outside optimal ranges)
        $qualityAlerts = $waterQualities->filter(function($measurement) {
            return $measurement->temperature < 25 || $measurement->temperature > 30 ||
                   $measurement->ph < 6.5 || $measurement->ph > 8.5 ||
                   $measurement->dissolved_oxygen < 5 ||
                   $measurement->ammonia > 0.5 ||
                   $measurement->nitrite > 0.1;
        })->map(function($measurement) {
            $alerts = [];
            if ($measurement->temperature < 25 || $measurement->temperature > 30) {
                $alerts[] = 'Temperature out of range';
            }
            if ($measurement->ph < 6.5 || $measurement->ph > 8.5) {
                $alerts[] = 'pH out of range';
            }
            if ($measurement->dissolved_oxygen < 5) {
                $alerts[] = 'Low dissolved oxygen';
            }
            if ($measurement->ammonia > 0.5) {
                $alerts[] = 'High ammonia';
            }
            if ($measurement->nitrite > 0.1) {
                $alerts[] = 'High nitrite';
            }

            return [
                'date' => $measurement->date,
                'pond_name' => $measurement->pond->name,
                'branch_name' => $measurement->pond->branch->name,
                'alerts' => $alerts,
                'temperature' => $measurement->temperature,
                'ph' => $measurement->ph,
                'dissolved_oxygen' => $measurement->dissolved_oxygen,
                'ammonia' => $measurement->ammonia,
            ];
        });

        return [
            'summary' => $summary,
            'parameter_analysis' => $parameterAnalysis,
            'daily_trends' => $dailyTrends,
            'quality_alerts' => $qualityAlerts,
        ];
    }

    private function getComprehensiveReport($params, $dateFrom, $dateTo)
    {
        // Combine all report types for comprehensive overview
        $production = $this->getProductionReport($params, $dateFrom, $dateTo);
        $financial = $this->getFinancialReport($params, $dateFrom, $dateTo);
        $mortality = $this->getMortalityReport($params, $dateFrom, $dateTo);
        $feeding = $this->getFeedingReport($params, $dateFrom, $dateTo);
        $waterQuality = $this->getWaterQualityReport($params, $dateFrom, $dateTo);

        // Executive summary
        $executiveSummary = [
            'total_batches' => $production['summary']['total_batches'],
            'total_revenue' => $financial['revenue']['total_revenue'],
            'total_costs' => $feeding['summary']['total_feed_cost'],
            'gross_profit' => $financial['profitability']['gross_profit'],
            'profit_margin' => $financial['profitability']['profit_margin'],
            'average_survival_rate' => $production['summary']['average_survival_rate'],
            'average_fcr' => $production['summary']['average_fcr'],
            'total_mortality' => $mortality['summary']['total_deaths'],
            'water_quality_score' => $this->calculateWaterQualityScore($waterQuality),
        ];

        // Key performance indicators
        $kpis = [
            'production_efficiency' => $this->calculateProductionEfficiency($production),
            'financial_performance' => $this->calculateFinancialPerformance($financial),
            'operational_excellence' => $this->calculateOperationalExcellence($mortality, $feeding, $waterQuality),
        ];

        // Recommendations
        $recommendations = $this->generateRecommendations($production, $financial, $mortality, $feeding, $waterQuality);

        return [
            'executive_summary' => $executiveSummary,
            'kpis' => $kpis,
            'production' => $production,
            'financial' => $financial,
            'mortality' => $mortality,
            'feeding' => $feeding,
            'water_quality' => $waterQuality,
            'recommendations' => $recommendations,
        ];
    }

    // Helper methods
    private function calculateCostVariance($feedings)
    {
        $costs = $feedings->where('feed_cost', '>', 0)->map(function ($feeding) {
            return $feeding->feed_cost / $feeding->feed_amount_kg;
        });

        if ($costs->isEmpty()) return 0;

        $mean = $costs->avg();
        $variance = $costs->map(function ($cost) use ($mean) {
            return pow($cost - $mean, 2);
        })->avg();

        return round(sqrt($variance), 2);
    }

    private function analyzeParameter($measurements, $parameter, $optimalRange)
    {
        $values = $measurements->pluck($parameter)->filter();

        if ($values->isEmpty()) {
            return [
                'min' => 0,
                'max' => 0,
                'avg' => 0,
                'within_range' => 0,
                'outside_range' => 0,
                'percentage_optimal' => 0,
            ];
        }

        $withinRange = $values->filter(function ($value) use ($optimalRange) {
            return $value >= $optimalRange[0] && $value <= $optimalRange[1];
        })->count();

        return [
            'min' => round($values->min(), 2),
            'max' => round($values->max(), 2),
            'avg' => round($values->avg(), 2),
            'within_range' => $withinRange,
            'outside_range' => $values->count() - $withinRange,
            'percentage_optimal' => round(($withinRange / $values->count()) * 100, 2),
        ];
    }

    private function calculateWaterQualityScore($waterQuality)
    {
        $parameters = $waterQuality['parameter_analysis'];
        $totalScore = 0;
        $parameterCount = 0;

        foreach ($parameters as $param) {
            $totalScore += $param['percentage_optimal'];
            $parameterCount++;
        }

        return $parameterCount > 0 ? round($totalScore / $parameterCount, 2) : 0;
    }

    private function calculateProductionEfficiency($production)
    {
        $summary = $production['summary'];

        $survivalScore = min($summary['average_survival_rate'] ?? 0, 100);
        $fcrScore = 0;

        if ($summary['average_fcr'] > 0) {
            // Lower FCR is better, so invert the score
            $fcrScore = max(0, 100 - (($summary['average_fcr'] - 1) * 50));
        }

        return round(($survivalScore + $fcrScore) / 2, 2);
    }

    private function calculateFinancialPerformance($financial)
    {
        $profitMargin = $financial['profitability']['profit_margin'] ?? 0;

        // Score based on profit margin
        if ($profitMargin >= 30) return 100;
        if ($profitMargin >= 20) return 80;
        if ($profitMargin >= 10) return 60;
        if ($profitMargin >= 0) return 40;
        return 20;
    }

    private function calculateOperationalExcellence($mortality, $feeding, $waterQuality)
    {
        // Mortality score (lower is better)
        $mortalityScore = 100;
        if (isset($mortality['summary']['total_deaths']) && $mortality['summary']['total_deaths'] > 0) {
            $mortalityScore = max(0, 100 - ($mortality['summary']['total_deaths'] / 100));
        }

        // Feeding consistency score
        $feedingScore = 80; // Base score

        // Water quality score
        $waterScore = $this->calculateWaterQualityScore($waterQuality);

        return round(($mortalityScore + $feedingScore + $waterScore) / 3, 2);
    }

    private function generateRecommendations($production, $financial, $mortality, $feeding, $waterQuality)
    {
        $recommendations = [];

        // Production recommendations
        if (($production['summary']['average_survival_rate'] ?? 0) < 80) {
            $recommendations[] = [
                'category' => 'Production',
                'priority' => 'high',
                'title' => 'Improve Survival Rate',
                'description' => 'Current survival rate is below optimal. Review water quality management and feeding practices.',
            ];
        }

        if (($production['summary']['average_fcr'] ?? 0) > 2.0) {
            $recommendations[] = [
                'category' => 'Production',
                'priority' => 'medium',
                'title' => 'Optimize Feed Conversion',
                'description' => 'FCR is higher than industry standard. Consider adjusting feed quality or feeding schedule.',
            ];
        }

        // Financial recommendations
        if (($financial['profitability']['profit_margin'] ?? 0) < 15) {
            $recommendations[] = [
                'category' => 'Financial',
                'priority' => 'high',
                'title' => 'Improve Profit Margins',
                'description' => 'Profit margin is below target. Review pricing strategy and cost optimization opportunities.',
            ];
        }

        // Mortality recommendations
        if (($mortality['summary']['total_deaths'] ?? 0) > 100) {
            $recommendations[] = [
                'category' => 'Health',
                'priority' => 'high',
                'title' => 'Address High Mortality',
                'description' => 'Mortality levels are concerning. Investigate causes and implement preventive measures.',
            ];
        }

        // Water quality recommendations
        $waterScore = $this->calculateWaterQualityScore($waterQuality);
        if ($waterScore < 80) {
            $recommendations[] = [
                'category' => 'Water Quality',
                'priority' => 'medium',
                'title' => 'Improve Water Quality Management',
                'description' => 'Water quality parameters are frequently outside optimal ranges. Enhance monitoring and treatment.',
            ];
        }

        // Feeding recommendations
        if (isset($feeding['cost_efficiency']['cost_variance']) && $feeding['cost_efficiency']['cost_variance'] > 2000) {
            $recommendations[] = [
                'category' => 'Feeding',
                'priority' => 'low',
                'title' => 'Standardize Feed Costs',
                'description' => 'High variance in feed costs detected. Consider negotiating better supplier contracts.',
            ];
        }

        return $recommendations;
    }

    private function generatePDF($reportData, $params)
    {
        // Implementation for PDF generation using DomPDF or similar
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('reports.pdf', compact('reportData', 'params'));

        $filename = 'report_' . $params['report_type'] . '_' . now()->format('Y-m-d_H-i-s') . '.pdf';

        return $pdf->download($filename);
    }

    private function generateExcel($reportData, $params)
    {
        // Implementation for Excel generation using Laravel Excel
        $filename = 'report_' . $params['report_type'] . '_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(new ReportExport($reportData, $params), $filename);
    }

    public function dashboard()
    {
        // Quick dashboard with key metrics
        $today = now();
        $thisWeek = [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()];
        $thisMonth = [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()];

        $dashboard = [
            'today' => [
                'sales' => Sale::whereDate('date', $today)->sum('total_amount'),
                'mortality' => Mortality::whereDate('date', $today)->sum('dead_count'),
                'feeding_cost' => Feeding::whereDate('date', $today)->sum('feed_cost'),
            ],
            'this_week' => [
                'sales' => Sale::whereBetween('date', $thisWeek)->sum('total_amount'),
                'mortality' => Mortality::whereBetween('date', $thisWeek)->sum('dead_count'),
                'feeding_cost' => Feeding::whereBetween('date', $thisWeek)->sum('feed_cost'),
            ],
            'this_month' => [
                'sales' => Sale::whereBetween('date', $thisMonth)->sum('total_amount'),
                'mortality' => Mortality::whereBetween('date', $thisMonth)->sum('dead_count'),
                'feeding_cost' => Feeding::whereBetween('date', $thisMonth)->sum('feed_cost'),
                'profit' => Sale::whereBetween('date', $thisMonth)->sum('total_amount') -
                    Feeding::whereBetween('date', $thisMonth)->sum('feed_cost'),
            ],
            'active_batches' => FishBatch::where('current_stock', '>', 0)->count(),
            'total_stock' => FishBatch::sum('current_stock'),
            'alerts' => $this->getSystemAlerts(),
        ];

        return view('reports.dashboard', compact('dashboard'));
    }

    private function getSystemAlerts()
    {
        $alerts = [];

        // High mortality alert
        $highMortality = Mortality::whereDate('date', '>=', now()->subDays(7))
            ->groupBy('fish_batch_id')
            ->selectRaw('fish_batch_id, SUM(dead_count) as total_deaths')
            ->having('total_deaths', '>', 50)
            ->with('fishBatch.pond')
            ->get();

        foreach ($highMortality as $mortality) {
            $alerts[] = [
                'type' => 'danger',
                'title' => 'High Mortality Alert',
                'message' => "Batch in {$mortality->fishBatch->pond->name} has {$mortality->total_deaths} deaths in the last 7 days",
                'date' => now(),
            ];
        }

        // Low stock alert
        $lowStock = FishBatch::where('current_stock', '>', 0)
            ->where('current_stock', '<', 100)
            ->with('pond')
            ->get();

        foreach ($lowStock as $batch) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Low Stock Alert',
                'message' => "Batch in {$batch->pond->name} has only {$batch->current_stock} fish remaining",
                'date' => now(),
            ];
        }

        // Water quality alerts
        $poorWaterQuality = WaterQuality::whereDate('date', '>=', now()->subDays(3))
            ->where(function ($query) {
                $query->where('ph', '<', 6.5)
                    ->orWhere('ph', '>', 8.5)
                    ->orWhere('dissolved_oxygen', '<', 5)
                    ->orWhere('ammonia', '>', 0.5);
            })
            ->with('pond')
            ->get();

        foreach ($poorWaterQuality as $wq) {
            $issues = [];
            if ($wq->ph < 6.5 || $wq->ph > 8.5) $issues[] = 'pH';
            if ($wq->dissolved_oxygen < 5) $issues[] = 'Low DO';
            if ($wq->ammonia > 0.5) $issues[] = 'High Ammonia';

            $alerts[] = [
                'type' => 'warning',
                'title' => 'Water Quality Alert',
                'message' => "Issues in {$wq->pond->name}: " . implode(', ', $issues),
                'date' => $wq->date,
            ];
        }

        return collect($alerts)->sortByDesc('date')->take(10);
    }
}
