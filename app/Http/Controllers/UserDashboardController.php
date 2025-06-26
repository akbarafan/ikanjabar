<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Pond;
use App\Models\FishBatch;
use App\Models\WaterQuality;
use App\Models\Sale;
use App\Models\Mortality;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserDashboardController extends Controller
{
    public function index()
    {
        // Stats Cards Data
        $totalPonds = Pond::count();
        $totalFish = $this->getTotalFishCount();
        $totalDeadFish = $this->getTotalDeadFish();
        $totalSoldFish = $this->getTotalSoldFish(); // Tambahkan ini untuk tracking
        $activeAlerts = $this->getActiveAlerts();
        $productivityPercentage = $this->calculateProductivity();

        // Pond Status Data
        $pondsStatus = $this->getPondsStatus();

        // Recent Alerts
        $recentAlerts = $this->getRecentAlerts();

        // Chart Data
        $waterQualityTrend = $this->getWaterQualityTrend();
        $productionDistribution = $this->getProductionDistribution();
        $growthAnalysis = $this->getGrowthAnalysis();
        $harvestPredictions = $this->getHarvestPredictions();

        // Additional Stats
        $survivalRate = $this->calculateSurvivalRate();
        $averageFCR = $this->calculateAverageFCR();
        $monthlyEstimatedHarvest = $this->getMonthlyEstimatedHarvest();

        return view('user.dashboard', compact(
            'totalPonds',
            'totalFish',
            'totalDeadFish',
            'totalSoldFish', // Tambahkan ini jika ingin ditampilkan
            'activeAlerts',
            'productivityPercentage',
            'pondsStatus',
            'recentAlerts',
            'waterQualityTrend',
            'productionDistribution',
            'growthAnalysis',
            'harvestPredictions',
            'survivalRate',
            'averageFCR',
            'monthlyEstimatedHarvest'
        ));
    }

    private function getTotalFishCount()
    {
        $totalInitial = FishBatch::whereNull('deleted_at')->sum('initial_count');
        $totalDead = Mortality::whereNull('deleted_at')->sum('dead_count');
        $totalSold = Sale::whereNull('deleted_at')->sum('quantity_fish');

        return $totalInitial - $totalDead - $totalSold;
    }

    private function getTotalDeadFish()
    {
        return Mortality::whereNull('deleted_at')->sum('dead_count');
    }

    // Tambahkan method baru untuk total ikan terjual
    private function getTotalSoldFish()
    {
        return Sale::whereNull('deleted_at')->sum('quantity_fish');
    }

    private function getActiveAlerts()
    {
        $alerts = 0;

        // Check water quality alerts
        $badWaterQuality = WaterQuality::whereDate('date_recorded', '>=', Carbon::now()->subDays(1))
            ->where(function ($query) {
                $query->where('ph', '<', 6.5)
                    ->orWhere('ph', '>', 8.5)
                    ->orWhere('temperature_c', '>', 30)
                    ->orWhere('do_mg_l', '<', 5)
                    ->orWhere('ammonia_mg_l', '>', 0.5);
            })->count();

        $alerts += $badWaterQuality;

        // Check harvest ready
        $harvestReady = FishBatch::where('date_start', '<=', Carbon::now()->subDays(90))
            ->whereNull('deleted_at')
            ->count();

        $alerts += $harvestReady;

        return $alerts;
    }

    private function calculateProductivity()
    {
        $currentMonth = Carbon::now()->format('Y-m');
        $lastMonth = Carbon::now()->subMonth()->format('Y-m');

        $currentProduction = Sale::whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$currentMonth])
            ->sum('quantity_fish');

        $lastProduction = Sale::whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$lastMonth])
            ->sum('quantity_fish');

        if ($lastProduction == 0) return 100;

        return round(($currentProduction / $lastProduction) * 100);
    }

    private function getPondsStatus()
    {
        return Pond::with(['latestWaterQuality', 'branch'])->get()->map(function ($pond) {
            $latestWaterQuality = $pond->latestWaterQuality;

            $status = 'healthy';
            if ($latestWaterQuality) {
                if (
                    $latestWaterQuality->ph < 6.5 || $latestWaterQuality->ph > 8.5 ||
                    $latestWaterQuality->temperature_c > 30 || $latestWaterQuality->do_mg_l < 5 ||
                    $latestWaterQuality->ammonia_mg_l > 0.5
                ) {
                    $status = 'danger';
                } elseif (
                    $latestWaterQuality->ph < 7 || $latestWaterQuality->ph > 8 ||
                    $latestWaterQuality->temperature_c > 28 || $latestWaterQuality->do_mg_l < 6 ||
                    $latestWaterQuality->ammonia_mg_l > 0.25
                ) {
                    $status = 'warning';
                }
            }

            return [
                'name' => $pond->name,
                'branch_name' => $pond->branch->name,
                'status' => $status,
                'temperature' => $latestWaterQuality ? $latestWaterQuality->temperature_c : 0,
                'ph' => $latestWaterQuality ? $latestWaterQuality->ph : 0,
                'do' => $latestWaterQuality ? $latestWaterQuality->do_mg_l : 0,
                'ammonia' => $latestWaterQuality ? $latestWaterQuality->ammonia_mg_l : 0,
            ];
        });
    }

    private function getRecentAlerts()
    {
        $alerts = collect();

        // Water quality alerts
        $waterAlerts = WaterQuality::with('pond.branch')
            ->whereDate('date_recorded', '>=', Carbon::now()->subHours(24))
            ->where(function ($query) {
                $query->where('ph', '<', 6.5)
                    ->orWhere('ph', '>', 8.5)
                    ->orWhere('temperature_c', '>', 30)
                    ->orWhere('do_mg_l', '<', 5)
                    ->orWhere('ammonia_mg_l', '>', 0.5);
            })
            ->latest('date_recorded')
            ->take(5)
            ->get()
            ->map(function ($wq) {
                $type = 'warning';
                $message = '';

                if ($wq->ph < 6.5 || $wq->ph > 8.5) {
                    $type = 'danger';
                    $message = "pH " . ($wq->ph > 8.5 ? 'Tinggi' : 'Rendah') . " - " . $wq->pond->name;
                } elseif ($wq->temperature_c > 30) {
                    $type = 'warning';
                    $message = "Suhu Tinggi - " . $wq->pond->name;
                } elseif ($wq->do_mg_l < 5) {
                    $type = 'danger';
                    $message = "DO Rendah - " . $wq->pond->name;
                } elseif ($wq->ammonia_mg_l > 0.5) {
                    $type = 'danger';
                    $message = "Ammonia Tinggi - " . $wq->pond->name;
                }

                return [
                    'type' => $type,
                    'message' => $message,
                    'detail' => $wq->pond->branch->name . ' - ' . $wq->pond->name . ': ' .
                        ($wq->ph < 6.5 || $wq->ph > 8.5 ? "pH {$wq->ph}" : ($wq->temperature_c > 30 ? "{$wq->temperature_c}°C" : ($wq->do_mg_l < 5 ? "{$wq->do_mg_l} mg/L DO" : "{$wq->ammonia_mg_l} mg/L NH₃"))),
                    'time' => $wq->date_recorded->diffForHumans(),
                ];
            });

        // Harvest ready alerts
        $harvestAlerts = FishBatch::with('pond.branch', 'fishType')
            ->where('date_start', '<=', Carbon::now()->subDays(90))
            ->whereNull('deleted_at')
            ->take(3)
            ->get()
            ->map(function ($batch) {
                return [
                    'type' => 'info',
                    'message' => "Siap Panen - " . $batch->pond->name,
                    'detail' => $batch->pond->branch->name . ' - ' . $batch->pond->name . ': ' . $batch->date_start->diffInDays() . ' hari',
                    'time' => '1 jam lalu',
                ];
            });

        return $alerts->concat($waterAlerts)->concat($harvestAlerts)->take(4);
    }

    private function getWaterQualityTrend()
    {
        $days = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $days->push($date->format('d M'));
        }

        $temperatures = collect();
        $phValues = collect();
        $doValues = collect();
        $ammoniaValues = collect();

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');

            $avgTemp = WaterQuality::whereDate('date_recorded', $date)->avg('temperature_c') ?? 27;
            $avgPh = WaterQuality::whereDate('date_recorded', $date)->avg('ph') ?? 7.2;
            $avgDo = WaterQuality::whereDate('date_recorded', $date)->avg('do_mg_l') ?? 6.5;
            $avgAmmonia = WaterQuality::whereDate('date_recorded', $date)->avg('ammonia_mg_l') ?? 0.2;

            $temperatures->push(round($avgTemp, 1));
            $phValues->push(round($avgPh, 1));
            $doValues->push(round($avgDo, 1));
            $ammoniaValues->push(round($avgAmmonia, 2));
        }

        return [
            'labels' => $days,
            'temperature' => $temperatures,
            'ph' => $phValues,
            'do' => $doValues,
            'ammonia' => $ammoniaValues,
        ];
    }

    private function getProductionDistribution()
    {
        return Pond::with(['fishBatches.sales', 'branch'])
            ->get()
            ->map(function ($pond) {
                $totalFishSold = $pond->fishBatches
                    ->flatMap->sales
                    ->sum('quantity_fish');

                return [
                    'name' => $pond->name . ' (' . $pond->branch->name . ')',
                    'production' => $totalFishSold,
                    'pond_code' => $pond->code,
                    'pond_type' => $pond->type,
                ];
            })
            ->filter(function ($pond) {
                return $pond['production'] > 0;
            })
            ->sortByDesc('production')
            ->take(5)
            ->values();
    }

    private function getGrowthAnalysis()
    {
        $months = collect();
        $weights = collect();

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $months->push($month->format('M'));

            $avgWeight = DB::table('fish_growth_logs')
                ->whereYear('date_recorded', $month->year)
                ->whereMonth('date_recorded', $month->month)
                ->whereNull('deleted_at')
                ->avg('avg_weight_gram');

            $weightInKg = $avgWeight ? round($avgWeight / 1000, 2) : 0;
            $weights->push($weightInKg);
        }

        return [
            'labels' => $months,
            'weights' => $weights,
        ];
    }

    private function getHarvestPredictions()
    {
        return FishBatch::with('pond.branch', 'fishType')
            ->where('date_start', '>=', Carbon::now()->subDays(120))
            ->where('date_start', '<=', Carbon::now()->subDays(60))
            ->whereNull('deleted_at')
            ->take(4)
            ->get()
            ->map(function ($batch) {
                $daysFromStart = $batch->date_start->diffInDays();
                $estimatedWeight = $batch->initial_count * 0.5;

                $status = 'ready';
                $daysLeft = 0;

                if ($daysFromStart < 90) {
                    $status = 'pending';
                    $daysLeft = 90 - $daysFromStart;
                }

                return [
                    'branch' => $batch->pond->branch->name,
                    'pond' => $batch->pond->name,
                    'fish_type' => $batch->fishType->name,
                    'days' => $daysFromStart,
                    'status' => $status,
                    'days_left' => $daysLeft,
                    'estimated_weight' => round($estimatedWeight),
                ];
            });
    }

    private function calculateSurvivalRate()
    {
        $totalInitial = FishBatch::whereNull('deleted_at')->sum('initial_count');
        $totalDead = Mortality::whereNull('deleted_at')->sum('dead_count');

        if ($totalInitial == 0) return 0;

        // Survival rate = ((total awal - yang mati) / total awal) * 100
        // Tidak termasuk yang terjual karena survival rate mengukur tingkat kelangsungan hidup
        return round((($totalInitial - $totalDead) / $totalInitial) * 100, 1);
    }

    private function calculateAverageFCR()
    {
        return 1.35;
    }

    private function getMonthlyEstimatedHarvest()
    {
        $readyBatches = FishBatch::where('date_start', '<=', Carbon::now()->subDays(90))
            ->whereNull('deleted_at')
            ->get();

        $totalEstimated = $readyBatches->sum(function ($batch) {
            // Hitung stok saat ini (initial - mati - terjual)
            $currentStock = $batch->initial_count
                - $batch->mortalities()->sum('dead_count')
                - $batch->sales()->sum('quantity_fish');

            return $currentStock * 0.5; // estimasi berat
        });

        return [
            'total' => round($totalEstimated),
            'target' => 11000,
            'percentage' => round(($totalEstimated / 11000) * 100),
        ];
    }
}
