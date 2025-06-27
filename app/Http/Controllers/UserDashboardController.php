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

    // public function __construct()
    // {
    //     $this->middleware(function ($request, $next) {
    //         $this->userBranchId = Auth::user()->branch_id;
    //         return $next($request);
    //     });
    // }

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
        // Single query untuk mendapatkan semua stats dasar
        $stats = DB::table('fish_batches as fb')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->leftJoin('mortalities as m', 'fb.id', '=', 'm.fish_batch_id')
            ->leftJoin('sales as s', 'fb.id', '=', 's.fish_batch_id')
            ->leftJoin('fish_batch_transfers as fbt_out', 'fb.id', '=', 'fbt_out.source_batch_id')
            ->leftJoin('fish_batch_transfers as fbt_in', 'fb.id', '=', 'fbt_in.target_batch_id')
            ->where('p.branch_id', $this->userBranchId)
            ->whereNull('fb.deleted_at')
            ->selectRaw('
                COUNT(DISTINCT p.id) as total_ponds,
                COUNT(DISTINCT fb.fish_type_id) as total_fish_types,
                SUM(fb.initial_count) as total_initial,
                COALESCE(SUM(m.dead_count), 0) as total_dead,
                COALESCE(SUM(s.quantity_fish), 0) as total_sold,
                COALESCE(SUM(fbt_out.transferred_count), 0) as total_transferred_out,
                COALESCE(SUM(fbt_in.transferred_count), 0) as total_transferred_in
            ')
            ->first();

        $currentStock = max(0, $stats->total_initial + $stats->total_transferred_in -
                           $stats->total_transferred_out - $stats->total_dead - $stats->total_sold);

        return [
            'totalPonds' => $stats->total_ponds,
            'totalFish' => $currentStock,
            'totalDeadFish' => $stats->total_dead,
            'totalFishTypes' => $stats->total_fish_types,
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
        return DB::table('ponds as p')
            ->leftJoin('fish_batches as fb', 'p.id', '=', 'fb.pond_id')
            ->leftJoin('fish_types as ft', 'fb.fish_type_id', '=', 'ft.id')
            ->leftJoin('mortalities as m', 'fb.id', '=', 'm.fish_batch_id')
            ->leftJoin('sales as s', 'fb.id', '=', 's.fish_batch_id')
            ->leftJoin('fish_batch_transfers as fbt_out', 'fb.id', '=', 'fbt_out.source_batch_id')
            ->leftJoin('fish_batch_transfers as fbt_in', 'fb.id', '=', 'fbt_in.target_batch_id')
            ->where('p.branch_id', $this->userBranchId)
            ->whereNull('fb.deleted_at')
            ->select(
                'p.id as pond_id',
                'p.name as pond_name',
                'p.code as pond_code',
                'p.type as pond_type',
                'p.volume_liters',
                'ft.name as fish_type',
                DB::raw('COALESCE(SUM(fb.initial_count), 0) as initial_count'),
                DB::raw('COALESCE(SUM(m.dead_count), 0) as total_dead'),
                DB::raw('COALESCE(SUM(s.quantity_fish), 0) as total_sold'),
                DB::raw('COALESCE(SUM(fbt_out.transferred_count), 0) as transferred_out'),
                DB::raw('COALESCE(SUM(fbt_in.transferred_count), 0) as transferred_in'),
                DB::raw('(COALESCE(SUM(fb.initial_count), 0) + COALESCE(SUM(fbt_in.transferred_count), 0) -
                         COALESCE(SUM(fbt_out.transferred_count), 0) - COALESCE(SUM(m.dead_count), 0) -
                         COALESCE(SUM(s.quantity_fish), 0)) as current_stock')
            )
            ->groupBy('p.id', 'p.name', 'p.code', 'p.type', 'p.volume_liters', 'ft.name')
            ->orderBy('current_stock', 'desc')
            ->get();
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

            $query = WaterQuality::whereHas('pond', function($q) {
                $q->where('branch_id', $this->userBranchId);
            })->whereDate('date_recorded', $date->format('Y-m-d'));

            if ($selectedPondId) {
                $query->where('pond_id', $selectedPondId);
            }

            $avg = $query->selectRaw('AVG(temperature_c) as temp, AVG(ph) as ph, AVG(do_mg_l) as do_val, AVG(ammonia_mg_l) as ammonia')
                ->first();

            $data['temperature']->push(round($avg->temp ?? 27, 1));
            $data['ph']->push(round($avg->ph ?? 7.2, 1));
            $data['do']->push(round($avg->do_val ?? 6.5, 1));
            $data['ammonia']->push(round($avg->ammonia ?? 0.2, 2));
        }

        return $data;
    }

    private function getProductionDistribution()
    {
        return Pond::where('branch_id', $this->userBranchId)
            ->with(['fishBatches.sales'])
            ->get()
            ->map(fn($pond) => [
                'name' => $pond->name,
                'production' => $pond->fishBatches->flatMap->sales->sum('quantity_fish'),
            ])
            ->filter(fn($pond) => $pond['production'] > 0)
            ->sortByDesc('production')
            ->take(5)
            ->values();
    }

    private function getGrowthAnalysis()
    {
        $data = ['labels' => collect(), 'weights' => collect()];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $data['labels']->push($month->format('M'));

            $avgWeight = DB::table('fish_growth_logs as fgl')
                ->join('fish_batches as fb', 'fgl.fish_batch_id', '=', 'fb.id')
                ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
                ->where('p.branch_id', $this->userBranchId)
                ->whereYear('fgl.date_recorded', $month->year)
                ->whereMonth('fgl.date_recorded', $month->month)
                ->whereNull('fgl.deleted_at')
                ->avg('fgl.avg_weight_gram');

            $data['weights']->push($avgWeight ? round($avgWeight / 1000, 2) : 0);
        }

        return $data;
    }

    private function getHarvestPredictions()
    {
        return FishBatch::with('pond', 'fishType')
            ->whereHas('pond', function ($query) {
                $query->where('branch_id', $this->userBranchId);
            })
            ->where('date_start', '>=', now()->subDays(120))
            ->take(4)
            ->get()
            ->map(function ($batch) {
                $days = $batch->date_start->diffInDays();
                $currentStock = $this->calculateBatchStock($batch);

                return [
                    'pond' => $batch->pond->name,
                    'fish_type' => $batch->fishType->name,
                    'days' => $days,
                    'status' => $days >= 90 ? 'ready' : 'pending',
                    'days_left' => max(0, 90 - $days),
                    'estimated_weight' => round($currentStock * 0.5),
                ];
            });
    }

    private function calculateBatchStock($batch)
    {
        $dead = $batch->mortalities()->sum('dead_count');
        $sold = $batch->sales()->sum('quantity_fish');
        $transferredOut = FishBatchTransfer::where('source_batch_id', $batch->id)->sum('transferred_count');
        $transferredIn = FishBatchTransfer::where('target_batch_id', $batch->id)->sum('transferred_count');

        return max(0, $batch->initial_count + $transferredIn - $transferredOut - $dead - $sold);
    }

    private function getPerformanceMetrics()
    {
        $stats = DB::table('fish_batches as fb')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->leftJoin('mortalities as m', 'fb.id', '=', 'm.fish_batch_id')
            ->where('p.branch_id', $this->userBranchId)
            ->whereNull('fb.deleted_at')
            ->selectRaw('
                SUM(fb.initial_count) as total_initial,
                COALESCE(SUM(m.dead_count), 0) as total_dead
            ')
            ->first();

        $survivalRate = $stats->total_initial > 0 ?
            round((($stats->total_initial - $stats->total_dead) / $stats->total_initial) * 100, 1) : 0;

        return [
            'survivalRate' => $survivalRate,
            'averageFCR' => 1.35,
            'monthlyEstimatedHarvest' => $this->getMonthlyEstimatedHarvest(),
        ];
    }

    private function getMonthlyEstimatedHarvest()
    {
        $readyBatches = FishBatch::whereHas('pond', function ($query) {
            $query->where('branch_id', $this->userBranchId);
        })
            ->where('date_start', '<=', now()->subDays(90))
            ->get();

        $totalEstimated = $readyBatches->sum(fn($batch) => $this->calculateBatchStock($batch) * 0.5);
        $target = 5000; // Target per cabang

        return [
            'total' => round($totalEstimated),
            'target' => $target,
            'percentage' => round(($totalEstimated / $target) * 100),
        ];
    }

    private function getPondOptions()
    {
        return Pond::where('branch_id', $this->userBranchId)
            ->select('id', 'name', 'code')
            ->orderBy('name')
            ->get()
            ->map(fn($pond) => [
                'id' => $pond->id,
                'name' => $pond->name . ' (' . $pond->code . ')',
            ]);
    }
}
