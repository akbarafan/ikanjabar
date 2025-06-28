<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pond;
use App\Models\FishBatch;
use App\Models\FishType;
use App\Models\WaterQuality;
use App\Models\Sale;
use App\Models\Mortality;
use App\Models\FishBatchTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class UserDashboardController extends Controller
{
    private $userBranchId = 1;

    public function index(Request $request)
    {
        $period = $request->get('period', '1month');
        $selectedPondId = $request->get('pond_id', null);

        return view('user.dashboard', array_merge(
            $this->getBasicStats(),
            $this->getChartsData($selectedPondId),
            $this->getPerformanceMetrics(),
            [
                'pondsStatus' => $this->getPondsStatus(),
                'recentAlerts' => $this->getRecentAlerts(),
                'fishSalesAnalysis' => $this->getFishSalesAnalysis($period),
                'pondStockDetails' => $this->getPondStockDetails(),
                'branchInfo' => $this->getBranchInfo(),
                'selectedPeriod' => $period,
                'selectedPondId' => $selectedPondId,
                'pondOptions' => $this->getPondOptions(),
            ]
        ));
    }

    private function getBasicStats()
    {
        // Get all batches for this branch
        $batchesData = DB::table('fish_batches as fb')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->where('p.branch_id', $this->userBranchId)
            ->whereNull('fb.deleted_at')
            ->select('fb.id', 'fb.initial_count', 'fb.fish_type_id')
            ->get();

        $totalPonds = DB::table('ponds')->where('branch_id', $this->userBranchId)->count();
        $totalFishTypes = DB::table('fish_types')->where('branch_id', $this->userBranchId)->count();

        // Calculate total current stock
        $totalCurrentStock = 0;
        $totalDeadFish = 0;

        foreach ($batchesData as $batch) {
            // Calculate sold fish
            $sold = DB::table('sales')
                ->where('fish_batch_id', $batch->id)
                ->whereNull('deleted_at')
                ->sum('quantity_fish');

            // Calculate mortality
            $mortality = DB::table('mortalities')
                ->where('fish_batch_id', $batch->id)
                ->whereNull('deleted_at')
                ->sum('dead_count');

            // Calculate transferred OUT
            $transferredOut = DB::table('fish_batch_transfers')
                ->where('source_batch_id', $batch->id)
                ->whereNull('deleted_at')
                ->sum('transferred_count');

            // Calculate transferred IN
            $transferredIn = DB::table('fish_batch_transfers')
                ->where('target_batch_id', $batch->id)
                ->whereNull('deleted_at')
                ->sum('transferred_count');

            // Current stock = initial + transferred_in - sold - mortality - transferred_out
            $currentStock = $batch->initial_count + $transferredIn - $sold - $mortality - $transferredOut;
            $totalCurrentStock += max(0, $currentStock);
            $totalDeadFish += $mortality;
        }

        return [
            'totalPonds' => $totalPonds,
            'totalFish' => $totalCurrentStock,
            'totalDeadFish' => $totalDeadFish,
            'totalFishTypes' => $totalFishTypes,
            'monthlyRevenue' => $this->getMonthlyRevenue(),
        ];
    }

    private function getMonthlyRevenue()
    {
        $revenues = DB::table('sales as s')
            ->join('fish_batches as fb', 's.fish_batch_id', '=', 'fb.id')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->where('p.branch_id', $this->userBranchId)
            ->whereNull('s.deleted_at')
            ->selectRaw('
                SUM(CASE WHEN MONTH(s.date) = MONTH(NOW()) AND YEAR(s.date) = YEAR(NOW())
                    THEN s.total_price ELSE 0 END) as current_month,
                SUM(CASE WHEN MONTH(s.date) = MONTH(DATE_SUB(NOW(), INTERVAL 1 MONTH))
                    AND YEAR(s.date) = YEAR(DATE_SUB(NOW(), INTERVAL 1 MONTH))
                    THEN s.total_price ELSE 0 END) as last_month
            ')
            ->first();

        $growth = $revenues->last_month > 0 ?
            round((($revenues->current_month - $revenues->last_month) / $revenues->last_month) * 100, 1) : 0;

        return [
            'amount' => $revenues->current_month,
            'formatted' => 'Rp ' . number_format($revenues->current_month, 0, ',', '.'),
            'growth' => $growth
        ];
    }

    private function getPondStockDetails()
    {
        // Get all ponds for this branch
        $ponds = DB::table('ponds')->where('branch_id', $this->userBranchId)->get();

        $pondStockDetails = collect();

        foreach ($ponds as $pond) {
            // Get all batches in this pond
            $batches = DB::table('fish_batches as fb')
                ->join('fish_types as ft', 'fb.fish_type_id', '=', 'ft.id')
                ->where('fb.pond_id', $pond->id)
                ->whereNull('fb.deleted_at')
                ->select('fb.id', 'fb.initial_count', 'ft.name as fish_type')
                ->get();

            $totalInitialCount = 0;
            $totalCurrentStock = 0;
            $totalDead = 0;
            $totalSold = 0;
            $totalTransferredOut = 0;
            $totalTransferredIn = 0;
            $fishTypes = [];

            foreach ($batches as $batch) {
                $totalInitialCount += $batch->initial_count;

                // Calculate sold fish
                $sold = DB::table('sales')
                    ->where('fish_batch_id', $batch->id)
                    ->whereNull('deleted_at')
                    ->sum('quantity_fish');

                // Calculate mortality
                $mortality = DB::table('mortalities')
                    ->where('fish_batch_id', $batch->id)
                    ->whereNull('deleted_at')
                    ->sum('dead_count');

                // Calculate transferred OUT
                $transferredOut = DB::table('fish_batch_transfers')
                    ->where('source_batch_id', $batch->id)
                    ->whereNull('deleted_at')
                    ->sum('transferred_count');

                // Calculate transferred IN
                $transferredIn = DB::table('fish_batch_transfers')
                    ->where('target_batch_id', $batch->id)
                    ->whereNull('deleted_at')
                    ->sum('transferred_count');

                // Current stock for this batch
                $currentStock = $batch->initial_count + $transferredIn - $sold - $mortality - $transferredOut;

                $totalCurrentStock += max(0, $currentStock);
                $totalDead += $mortality;
                $totalSold += $sold;
                $totalTransferredOut += $transferredOut;
                $totalTransferredIn += $transferredIn;

                if (!in_array($batch->fish_type, $fishTypes)) {
                    $fishTypes[] = $batch->fish_type;
                }
            }

            $pondStockDetails->push((object)[
                'pond_id' => $pond->id,
                'pond_name' => $pond->name,
                'pond_code' => $pond->code,
                'pond_type' => $pond->type,
                'volume_liters' => $pond->volume_liters,
                'fish_type' => count($fishTypes) > 0 ? implode(', ', $fishTypes) : null,
                'initial_count' => $totalInitialCount,
                'current_stock' => $totalCurrentStock,
                'total_dead' => $totalDead,
                'total_sold' => $totalSold,
                'transferred_out' => $totalTransferredOut,
                'transferred_in' => $totalTransferredIn,
            ]);
        }

        return $pondStockDetails->sortByDesc('current_stock');
    }

    private function getBranchInfo()
    {
        return DB::table('branches')
            ->where('id', $this->userBranchId)
            ->select('name', 'location', 'contact_person', 'pic_name')
            ->first();
    }

    private function getFishSalesAnalysis($period)
    {
        $dateRange = $this->getDateRange($period);

        $topFishSales = DB::table('sales as s')
            ->join('fish_batches as fb', 's.fish_batch_id', '=', 'fb.id')
            ->join('fish_types as ft', 'fb.fish_type_id', '=', 'ft.id')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->where('p.branch_id', $this->userBranchId)
            ->whereBetween('s.date', [$dateRange['start'], $dateRange['end']])
            ->whereNull('s.deleted_at')
            ->select(
                'ft.name as fish_name',
                DB::raw('SUM(s.total_price) as total_revenue'),
                DB::raw('SUM(s.quantity_fish) as total_quantity'),
                DB::raw('AVG(s.price_per_kg) as avg_price')
            )
            ->groupBy('ft.id', 'ft.name')
            ->orderBy('total_revenue', 'desc')
            ->limit(5)
            ->get();

        return [
            'period' => $period,
            'period_label' => $this->getPeriodLabel($period),
            'top_fish_sales' => $topFishSales,
            'chart_data' => [
                'labels' => $topFishSales->pluck('fish_name')->toArray(),
                'revenues' => $topFishSales->pluck('total_revenue')->toArray(),
                'quantities' => $topFishSales->pluck('total_quantity')->toArray(),
            ]
        ];
    }

    private function getDateRange($period)
    {
        $end = now();
        $start = match($period) {
            '3months' => now()->subMonths(3),
            '6months' => now()->subMonths(6),
            '1year' => now()->subYear(),
            default => now()->subMonth()
        };

        return ['start' => $start->format('Y-m-d'), 'end' => $end->format('Y-m-d')];
    }

    private function getPeriodLabel($period)
    {
        return match($period) {
            '3months' => '3 Bulan Terakhir',
            '6months' => '6 Bulan Terakhir',
            '1year' => '1 Tahun Terakhir',
            default => '1 Bulan Terakhir'
        };
    }

    private function getPondsStatus()
    {
        return Pond::with(['latestWaterQuality'])
            ->where('branch_id', $this->userBranchId)
            ->get()
            ->map(function ($pond) {
                $wq = $pond->latestWaterQuality;
                return [
                    'id' => $pond->id,
                    'name' => $pond->name,
                    'code' => $pond->code,
                    'status' => $this->getWaterStatus($wq),
                    'temperature' => $wq->temperature_c ?? 0,
                    'ph' => $wq->ph ?? 0,
                    'do' => $wq->do_mg_l ?? 0,
                    'ammonia' => $wq->ammonia_mg_l ?? 0,
                ];
            });
    }

    private function getWaterStatus($wq)
    {
        if (!$wq) return 'warning';

        if ($wq->ph < 6.5 || $wq->ph > 8.5 || $wq->temperature_c > 30 || $wq->do_mg_l < 5 || $wq->ammonia_mg_l > 0.5) {
            return 'danger';
        }

        if ($wq->ph < 7 || $wq->ph > 8 || $wq->temperature_c > 28 || $wq->do_mg_l < 6 || $wq->ammonia_mg_l > 0.25) {
            return 'warning';
        }

        return 'healthy';
    }

    private function getRecentAlerts()
    {
        return WaterQuality::with('pond')
            ->whereHas('pond', function($query) {
                $query->where('branch_id', $this->userBranchId);
            })
            ->whereDate('date_recorded', '>=', now()->subDay())
            ->get()
            ->filter(fn($wq) => $this->getWaterStatus($wq) === 'danger')
            ->take(4)
            ->map(fn($wq) => [
                'type' => 'danger',
                'message' => "Kualitas Air Buruk - {$wq->pond->name}",
                'detail' => "pH: {$wq->ph}, Suhu: {$wq->temperature_c}°C",
                'time' => $wq->date_recorded->diffForHumans(),
            ]);
    }

    private function getChartsData($selectedPondId = null)
    {
        return [
            'waterQualityTrend' => $this->getWaterQualityTrend($selectedPondId),
            'productionDistribution' => $this->getProductionDistribution(),
            'growthAnalysis' => $this->getGrowthAnalysis(),
            'harvestPredictions' => $this->getHarvestPredictions(),
        ];
    }

    private function getWaterQualityTrend($selectedPondId = null)
    {
        $data = ['labels' => collect(), 'temperature' => collect(), 'ph' => collect(), 'do' => collect(), 'ammonia' => collect()];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $data['labels']->push($date->format('d M'));

            $query = WaterQuality::whereHas('pond', function ($q) {
                $q->where('branch_id', $this->userBranchId);
            })->whereDate('date_recorded', $date);

            if ($selectedPondId) {
                $query->where('pond_id', $selectedPondId);
            }

            $avg = $query->selectRaw('
                AVG(temperature_c) as avg_temp,
                AVG(ph) as avg_ph,
                AVG(do_mg_l) as avg_do,
                AVG(ammonia_mg_l) as avg_ammonia
            ')->first();

            $data['temperature']->push($avg->avg_temp ?? 0);
            $data['ph']->push($avg->avg_ph ?? 0);
            $data['do']->push($avg->avg_do ?? 0);
            $data['ammonia']->push($avg->avg_ammonia ?? 0);
        }

        return $data;
    }

    private function getProductionDistribution()
    {
        $fishTypes = DB::table('fish_types as ft')
            ->join('fish_batches as fb', 'ft.id', '=', 'fb.fish_type_id')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->where('ft.branch_id', $this->userBranchId)
            ->whereNull('fb.deleted_at')
            ->select('ft.name', 'ft.id')
            ->groupBy('ft.id', 'ft.name')
            ->get();

        $distribution = [];
        foreach ($fishTypes as $fishType) {
            $batches = DB::table('fish_batches')
                ->where('fish_type_id', $fishType->id)
                ->whereNull('deleted_at')
                ->get();

            $totalStock = 0;
            foreach ($batches as $batch) {
                // Calculate current stock for each batch
                $sold = DB::table('sales')
                    ->where('fish_batch_id', $batch->id)
                    ->whereNull('deleted_at')
                    ->sum('quantity_fish');

                $mortality = DB::table('mortalities')
                    ->where('fish_batch_id', $batch->id)
                    ->whereNull('deleted_at')
                    ->sum('dead_count');

                $transferredOut = DB::table('fish_batch_transfers')
                    ->where('source_batch_id', $batch->id)
                    ->whereNull('deleted_at')
                    ->sum('transferred_count');

                $transferredIn = DB::table('fish_batch_transfers')
                    ->where('target_batch_id', $batch->id)
                    ->whereNull('deleted_at')
                    ->sum('transferred_count');

                $currentStock = $batch->initial_count + $transferredIn - $sold - $mortality - $transferredOut;
                $totalStock += max(0, $currentStock);
            }

            $distribution[] = [
                'name' => $fishType->name,
                'value' => $totalStock
            ];
        }

        return [
            'labels' => collect($distribution)->pluck('name')->toArray(),
            'data' => collect($distribution)->pluck('value')->toArray(),
        ];
    }

    private function getGrowthAnalysis()
    {
        $growthData = DB::table('fish_growth_logs as fgl')
            ->join('fish_batches as fb', 'fgl.fish_batch_id', '=', 'fb.id')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->where('p.branch_id', $this->userBranchId)
            ->whereNull('fgl.deleted_at')
            ->selectRaw('
                fgl.week_number,
                AVG(fgl.avg_weight_gram) as avg_weight,
                AVG(fgl.avg_length_cm) as avg_length
            ')
            ->groupBy('fgl.week_number')
            ->orderBy('fgl.week_number')
            ->get();

        return [
            'labels' => $growthData->pluck('week_number')->map(fn($week) => "Minggu {$week}")->toArray(),
            'weight' => $growthData->pluck('avg_weight')->toArray(),
            'length' => $growthData->pluck('avg_length')->toArray(),
        ];
    }

    private function getHarvestPredictions()
    {
        $predictions = DB::table('fish_batches as fb')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->join('fish_types as ft', 'fb.fish_type_id', '=', 'ft.id')
            ->where('p.branch_id', $this->userBranchId)
            ->whereNull('fb.deleted_at')
            ->select('fb.id', 'fb.date_start', 'fb.initial_count', 'ft.name as fish_type', 'p.name as pond_name')
            ->get()
            ->map(function ($batch) {
                $ageInDays = now()->diffInDays($batch->date_start);
                $estimatedHarvestDate = Carbon::parse($batch->date_start)->addDays(120); // Assume 120 days cycle

                // Calculate current stock
                $sold = DB::table('sales')
                    ->where('fish_batch_id', $batch->id)
                    ->whereNull('deleted_at')
                    ->sum('quantity_fish');

                $mortality = DB::table('mortalities')
                    ->where('fish_batch_id', $batch->id)
                    ->whereNull('deleted_at')
                    ->sum('dead_count');

                $transferredOut = DB::table('fish_batch_transfers')
                    ->where('source_batch_id', $batch->id)
                    ->whereNull('deleted_at')
                    ->sum('transferred_count');

                $transferredIn = DB::table('fish_batch_transfers')
                    ->where('target_batch_id', $batch->id)
                    ->whereNull('deleted_at')
                    ->sum('transferred_count');

                $currentStock = $batch->initial_count + $transferredIn - $sold - $mortality - $transferredOut;

                return [
                    'batch_id' => $batch->id,
                    'fish_type' => $batch->fish_type,
                    'pond_name' => $batch->pond_name,
                    'current_stock' => max(0, $currentStock),
                    'age_days' => $ageInDays,
                    'estimated_harvest' => $estimatedHarvestDate,
                    'days_to_harvest' => max(0, $estimatedHarvestDate->diffInDays(now())),
                    'readiness' => $ageInDays >= 90 ? 'ready' : ($ageInDays >= 60 ? 'soon' : 'growing')
                ];
            })
            ->filter(fn($batch) => $batch['current_stock'] > 0)
            ->sortBy('days_to_harvest')
            ->take(5);

        return $predictions;
    }

    private function getPerformanceMetrics()
    {
        // Survival rate calculation
        $totalInitial = DB::table('fish_batches as fb')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->where('p.branch_id', $this->userBranchId)
            ->whereNull('fb.deleted_at')
            ->sum('fb.initial_count');

        $totalMortality = DB::table('mortalities as m')
            ->join('fish_batches as fb', 'm.fish_batch_id', '=', 'fb.id')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->where('p.branch_id', $this->userBranchId)
            ->whereNull('m.deleted_at')
            ->sum('m.dead_count');

        $survivalRate = $totalInitial > 0 ? (($totalInitial - $totalMortality) / $totalInitial) * 100 : 0;

        // Feed conversion ratio (FCR)
        $totalFeed = DB::table('feedings as f')
            ->join('fish_batches as fb', 'f.fish_batch_id', '=', 'fb.id')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->where('p.branch_id', $this->userBranchId)
            ->whereNull('f.deleted_at')
            ->sum('f.feed_amount_kg');

        $totalSalesWeight = DB::table('sales as s')
            ->join('fish_batches as fb', 's.fish_batch_id', '=', 'fb.id')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->where('p.branch_id', $this->userBranchId)
            ->whereNull('s.deleted_at')
            ->selectRaw('SUM(s.quantity_fish * s.avg_weight_per_fish_kg) as total_weight')
            ->value('total_weight');

        $fcr = $totalSalesWeight > 0 ? $totalFeed / $totalSalesWeight : 0;

        return [
            'survival_rate' => round($survivalRate, 1),
            'fcr' => round($fcr, 2),
            'total_feed_used' => $totalFeed,
            'total_sales_weight' => $totalSalesWeight,
        ];
    }

    private function getPondOptions()
    {
        return DB::table('ponds')
            ->where('branch_id', $this->userBranchId)
            ->select('id', 'name', 'code')
            ->orderBy('name')
            ->get();
    }
}
