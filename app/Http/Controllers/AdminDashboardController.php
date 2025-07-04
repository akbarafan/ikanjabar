<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Pond;
use App\Models\FishBatch;
use App\Models\WaterQuality;
use App\Models\Sale;
use App\Models\User;
use App\Models\Mortality;
use App\Models\Feeding;
use App\Models\FishType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Basic Stats
        $totalBranches = Branch::count();
        $totalPonds = Pond::count();
        $totalUsers = User::count();
        $totalFishTypes = FishType::count();

        // Active Batches (with current stock > 0)
        $activeBatches = FishBatch::whereHas('pond')
            ->get()
            ->filter(function ($batch) {
                return $this->calculateCurrentStock($batch->id) > 0;
            })
            ->count();

        // Sales Data
        $currentMonth = Carbon::now();
        $lastMonth = Carbon::now()->subMonth();

        $totalSales = Sale::sum('total_price') ?? 0;
        $currentMonthSales = Sale::whereMonth('date', $currentMonth->month)
            ->whereYear('date', $currentMonth->year)
            ->sum('total_price') ?? 0;
        $lastMonthSales = Sale::whereMonth('date', $lastMonth->month)
            ->whereYear('date', $lastMonth->year)
            ->sum('total_price') ?? 0;

        $salesGrowth = $lastMonthSales > 0
            ? (($currentMonthSales - $lastMonthSales) / $lastMonthSales) * 100
            : 0;

        // Branch Performance Data
        $branchPerformance = $this->getBranchPerformance();

        // Branches with pagination
        $branches = Branch::withCount(['ponds', 'users'])
            ->with(['ponds.fishBatches'])
            ->paginate(5);

        // Add statistics to each branch
        foreach ($branches as $branch) {
            $branch->statistics = $this->getBranchStatistics($branch->id);
        }

        // Pond Types Distribution
        $pondTypes = Pond::select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        // Format pond types for display
        $formattedPondTypes = [];
        foreach ($pondTypes as $type => $count) {
            $formattedPondTypes[ucfirst($type)] = $count;
        }

        // Recent Activities
        $recentActivities = $this->getRecentActivities();

        // Water Quality Alerts
        $waterQualityAlerts = $this->getWaterQualityAlerts();

        return view('admin.dashboard', compact(
            'totalBranches',
            'totalPonds',
            'totalUsers',
            'totalFishTypes',
            'activeBatches',
            'totalSales',
            'salesGrowth',
            'branchPerformance',
            'branches',
            'pondTypes',
            'recentActivities',
            'waterQualityAlerts'
        ));
    }

    private function calculateCurrentStock($batchId)
    {
        $batch = FishBatch::find($batchId);
        if (!$batch) return 0;

        $initialCount = $batch->initial_count;

        // Calculate transfers in
        $transferredIn = DB::table('fish_batch_transfers')
            ->where('target_batch_id', $batchId)
            ->whereNull('deleted_at')
            ->sum('transferred_count');

        // Calculate transfers out
        $transferredOut = DB::table('fish_batch_transfers')
            ->where('source_batch_id', $batchId)
            ->whereNull('deleted_at')
            ->sum('transferred_count');

        // Calculate sold fish
        $sold = Sale::where('fish_batch_id', $batchId)->sum('quantity_fish');

        // Calculate mortality
        $mortality = Mortality::where('fish_batch_id', $batchId)->sum('dead_count');

        return $initialCount + $transferredIn - $transferredOut - $sold - $mortality;
    }

    private function getBranchPerformance()
    {
        return Branch::select('branches.*')
            ->withCount(['ponds', 'users'])
            ->with(['ponds.fishBatches.sales'])
            ->get()
            ->map(function ($branch) {
                $totalRevenue = $branch->ponds->flatMap->fishBatches->flatMap->sales->sum('total_price');
                $activeBatches = $branch->ponds->flatMap->fishBatches->filter(function ($batch) {
                    return $this->calculateCurrentStock($batch->id) > 0;
                })->count();

                return [
                    'id' => $branch->id,
                    'name' => $branch->name,
                    'location' => $branch->location,
                    'total_ponds' => $branch->ponds_count,
                    'total_users' => $branch->users_count,
                    'active_batches' => $activeBatches,
                    'total_revenue' => $totalRevenue,
                    'revenue_formatted' => 'Rp ' . number_format($totalRevenue, 0, ',', '.')
                ];
            })
            ->sortByDesc('total_revenue')
            ->take(7)
            ->values();
    }

    private function getBranchStatistics($branchId)
    {
        $ponds = Pond::where('branch_id', $branchId)->get();
        $totalPonds = $ponds->count();

        $activeBatches = 0;
        $totalFishStock = 0;

        foreach ($ponds as $pond) {
            $batches = FishBatch::where('pond_id', $pond->id)->get();
            foreach ($batches as $batch) {
                $currentStock = $this->calculateCurrentStock($batch->id);
                if ($currentStock > 0) {
                    $activeBatches++;
                    $totalFishStock += $currentStock;
                }
            }
        }

        return [
            'total_ponds' => $totalPonds,
            'total_active_batches' => $activeBatches,
            'total_fish_stock' => $totalFishStock
        ];
    }

    private function getRecentActivities()
    {
        $activities = collect();

        // Recent Sales
        $recentSales = Sale::with(['fishBatch.pond.branch', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        foreach ($recentSales as $sale) {
            $activities->push([
                'type' => 'sale',
                'icon' => 'fa-money-bill-wave',
                'color' => 'green',
                'title' => 'Penjualan Ikan',
                'description' => number_format($sale->quantity_fish) . ' ekor ikan dijual',
                'branch' => $sale->fishBatch->pond->branch->name,
                'user' => $sale->createdBy->full_name,
                'time' => $sale->created_at->diffForHumans(),
                'amount' => 'Rp ' . number_format($sale->total_price, 0, ',', '.')
            ]);
        }

        // Recent Mortalities
        $recentMortalities = Mortality::with(['fishBatch.pond.branch', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->take(2)
            ->get();

        foreach ($recentMortalities as $mortality) {
            $activities->push([
                'type' => 'mortality',
                'icon' => 'fa-exclamation-triangle',
                'color' => 'red',
                'title' => 'Mortalitas Ikan',
                'description' => number_format($mortality->dead_count) . ' ekor ikan mati',
                'branch' => $mortality->fishBatch->pond->branch->name,
                'user' => $mortality->createdBy->full_name,
                'time' => $mortality->created_at->diffForHumans(),
                'cause' => $mortality->cause
            ]);
        }

        // Recent Fish Batches
        $recentBatches = FishBatch::with(['pond.branch', 'fishType', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->take(2)
            ->get();

        foreach ($recentBatches as $batch) {
            $activities->push([
                'type' => 'batch',
                'icon' => 'fa-fish',
                'color' => 'blue',
                'title' => 'Batch Ikan Baru',
                'description' => number_format($batch->initial_count) . ' ekor ' . $batch->fishType->name,
                'branch' => $batch->pond->branch->name,
                'user' => $batch->createdBy->full_name,
                'time' => $batch->created_at->diffForHumans(),
                'pond' => $batch->pond->name
            ]);
        }

        return $activities->sortByDesc('created_at')->take(5)->values();
    }

    private function getWaterQualityAlerts()
    {
        $alerts = collect();

        $recentWaterQuality = WaterQuality::with(['pond.branch'])
            ->where('date_recorded', '>=', Carbon::now()->subDays(7))
            ->get();

        foreach ($recentWaterQuality as $wq) {
            // Check pH levels (ideal: 7.0-8.0)
            if ($wq->ph < 6.5 || $wq->ph > 8.5) {
                $alerts->push([
                    'type' => 'ph',
                    'severity' => $wq->ph < 6.0 || $wq->ph > 9.0 ? 'critical' : 'warning',
                    'message' => 'pH tidak normal',
                    'value' => $wq->ph,
                    'pond' => $wq->pond->name,
                    'branch' => $wq->pond->branch->name,
                    'date' => $wq->date_recorded->format('d M Y')
                ]);
            }

            // Check temperature (ideal: 25-30°C)
            if ($wq->temperature_c < 20 || $wq->temperature_c > 35) {
                $alerts->push([
                    'type' => 'temperature',
                    'severity' => $wq->temperature_c < 15 || $wq->temperature_c > 40 ? 'critical' : 'warning',
                    'message' => 'Suhu air tidak normal',
                    'value' => $wq->temperature_c . '°C',
                    'pond' => $wq->pond->name,
                    'branch' => $wq->pond->branch->name,
                    'date' => $wq->date_recorded->format('d M Y')
                ]);
            }

            // Check DO levels (ideal: >5 mg/L)
            if ($wq->do_mg_l < 4) {
                $alerts->push([
                    'type' => 'do',
                    'severity' => $wq->do_mg_l < 2 ? 'critical' : 'warning',
                    'message' => 'Oksigen terlarut rendah',
                    'value' => $wq->do_mg_l . ' mg/L',
                    'pond' => $wq->pond->name,
                    'branch' => $wq->pond->branch->name,
                    'date' => $wq->date_recorded->format('d M Y')
                ]);
            }

            // Check Ammonia levels (ideal: <0.5 mg/L)
            if ($wq->ammonia_mg_l && $wq->ammonia_mg_l > 0.5) {
                $alerts->push([
                    'type' => 'ammonia',
                    'severity' => $wq->ammonia_mg_l > 1.0 ? 'critical' : 'warning',
                    'message' => 'Kadar ammonia tinggi',
                    'value' => $wq->ammonia_mg_l . ' mg/L',
                    'pond' => $wq->pond->name,
                    'branch' => $wq->pond->branch->name,
                    'date' => $wq->date_recorded->format('d M Y')
                ]);
            }
        }

        return $alerts->sortByDesc('severity')->take(5)->values();
    }
}
