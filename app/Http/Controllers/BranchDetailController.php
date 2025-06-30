<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Pond;
use App\Models\FishBatch;
use App\Models\FishStockSnapshot;
use App\Models\FishType;
use App\Models\WaterQuality;
use App\Models\Sale;
use App\Models\Mortality;
use App\Models\Feeding;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BranchDetailController extends Controller
{
    public function show(Branch $branch)
    {
        // Get branch with relationships
        $branch->load(['ponds', 'users']);
        
        // Branch info
        $branchInfo = $branch;
        
        // Calculate statistics
        $totalPonds = $branch->ponds->count();
        
        // Hitung total fish dari fish_stock_snapshots terbaru
        $totalFish = $this->calculateTotalFish($branch);
        
        // Monthly revenue
        $monthlyRevenue = $this->calculateMonthlyRevenue($branch);
        
        // Fish types count - dari tabel fish_types
        $totalFishTypes = FishType::where('branch_id', $branch->id)->count();
        
        // Pond stock details
        $pondStockDetails = $this->getPondStockDetails($branch);
        
        // Pond status (water quality)
        $pondsStatus = $this->getPondsStatus($branch);
        
        // Water quality trend (7 days)
        $waterQualityTrend = $this->getWaterQualityTrend($branch);
        
        // Fish sales analysis
        $fishSalesAnalysis = $this->getFishSalesAnalysis($branch);
        
        // Performance metrics
        $survivalRate = $this->calculateSurvivalRate($branch);
        $averageFCR = $this->calculateAverageFCR($branch);
        
        // Harvest predictions
        $harvestPredictions = $this->getHarvestPredictions($branch);
        
        // Recent alerts
        $recentAlerts = $this->getRecentAlerts($branch);
        
        // Pond options for filter
        $pondOptions = $branch->ponds->map(function($pond) {
            return [
                'id' => $pond->id,
                'name' => $pond->name
            ];
        })->toArray();
        
        // Selected filters (from request)
        $selectedPondId = request('pond_id');
        $selectedPeriod = request('period', '1month');
        
        return view('admin.branches.show', compact(
            'branch',
            'branchInfo',
            'totalPonds',
            'totalFish',
            'monthlyRevenue',
            'totalFishTypes',
            'pondStockDetails',
            'pondsStatus',
            'waterQualityTrend',
            'fishSalesAnalysis',
            'survivalRate',
            'averageFCR',
            'harvestPredictions',
            'recentAlerts',
            'pondOptions',
            'selectedPondId',
            'selectedPeriod'
        ));
    }
    
    private function calculateTotalFish($branch)
    {
        $totalStock = 0;
        
        // Ambil semua batch aktif dari ponds di branch ini
        $activeBatches = FishBatch::whereHas('pond', function($query) use ($branch) {
            $query->where('branch_id', $branch->id);
        })->whereNull('deleted_at')->get();
        
        foreach ($activeBatches as $batch) {
            // Ambil snapshot terbaru untuk setiap batch
            $latestSnapshot = FishStockSnapshot::where('fish_batch_id', $batch->id)
                ->latest('created_at')
                ->first();
            
            if ($latestSnapshot) {
                $totalStock += $latestSnapshot->current_stock;
            } else {
                // Fallback ke initial_count jika tidak ada snapshot
                $totalStock += $batch->initial_count;
            }
        }
        
        return $totalStock;
    }
    
    private function calculateMonthlyRevenue($branch)
    {
        $currentMonth = Sale::whereHas('fishBatch.pond', function($query) use ($branch) {
            $query->where('branch_id', $branch->id);
        })
        ->whereMonth('date', Carbon::now()->month)
        ->whereYear('date', Carbon::now()->year)
        ->whereNull('deleted_at')
        ->sum('total_price') ?? 0;
        
        $previousMonth = Sale::whereHas('fishBatch.pond', function($query) use ($branch) {
            $query->where('branch_id', $branch->id);
        })
        ->whereMonth('date', Carbon::now()->subMonth()->month)
        ->whereYear('date', Carbon::now()->subMonth()->year)
        ->whereNull('deleted_at')
        ->sum('total_price') ?? 0;
        
        $growth = $previousMonth > 0 ? (($currentMonth - $previousMonth) / $previousMonth) * 100 : 0;
        
        return [
            'total' => $currentMonth,
            'formatted' => 'Rp ' . number_format($currentMonth, 0, ',', '.'),
            'growth' => round($growth, 1)
        ];
    }
    
    private function getPondStockDetails($branch)
    {
        return $branch->ponds()->with(['fishBatches' => function($query) {
            $query->whereNull('deleted_at');
        }])->get()->map(function($pond) {
            $activeBatch = $pond->fishBatches->first();
            
            if ($activeBatch) {
                // Ambil snapshot terbaru
                $latestSnapshot = FishStockSnapshot::where('fish_batch_id', $activeBatch->id)
                    ->latest('created_at')
                    ->first();
                
                $currentStock = $latestSnapshot ? $latestSnapshot->current_stock : $activeBatch->initial_count;
                
                // Hitung total mati dari tabel mortalities
                $totalDead = Mortality::where('fish_batch_id', $activeBatch->id)
                    ->whereNull('deleted_at')
                    ->sum('dead_count');
                
                // Hitung total terjual dari tabel sales
                $totalSold = Sale::where('fish_batch_id', $activeBatch->id)
                    ->whereNull('deleted_at')
                    ->sum('quantity_fish');
                
                // Ambil nama jenis ikan dari tabel fish_types
                $fishType = FishType::find($activeBatch->fish_type_id)->name ?? 'Unknown';
                
                // Hitung transfer (simplified - bisa dikembangkan lebih lanjut)
                $transferredIn = DB::table('fish_batch_transfers')
                    ->where('target_batch_id', $activeBatch->id)
                    ->whereNull('deleted_at')
                    ->sum('transferred_count');
                
                $transferredOut = DB::table('fish_batch_transfers')
                    ->where('source_batch_id', $activeBatch->id)
                    ->whereNull('deleted_at')
                    ->sum('transferred_count');
            } else {
                $currentStock = 0;
                $totalDead = 0;
                $totalSold = 0;
                $fishType = null;
                $transferredIn = 0;
                $transferredOut = 0;
            }
            
            return (object)[
                'pond_name' => $pond->name,
                'pond_code' => $pond->code,
                'pond_type' => $pond->type,
                'volume_liters' => $pond->volume_liters,
                'fish_type' => $fishType,
                'current_stock' => $currentStock,
                'total_dead' => $totalDead,
                'total_sold' => $totalSold,
                'transferred_in' => $transferredIn,
                'transferred_out' => $transferredOut,
            ];
        });
    }
    
    private function getPondsStatus($branch)
    {
        return $branch->ponds()->get()->map(function($pond) {
            // Ambil kualitas air terbaru berdasarkan date_recorded
            $waterQuality = WaterQuality::where('pond_id', $pond->id)
                ->whereNull('deleted_at')
                ->orderBy('date_recorded', 'desc')
                ->first();
            
            $status = 'healthy';
            if ($waterQuality) {
                if ($waterQuality->temperature_c > 32 || $waterQuality->ph < 6.5 || $waterQuality->ph > 8.5 || 
                    $waterQuality->do_mg_l < 4 || ($waterQuality->ammonia_mg_l && $waterQuality->ammonia_mg_l > 0.5)) {
                    $status = 'danger';
                } elseif ($waterQuality->temperature_c > 30 || $waterQuality->ph < 7 || $waterQuality->ph > 8 || 
                         $waterQuality->do_mg_l < 5 || ($waterQuality->ammonia_mg_l && $waterQuality->ammonia_mg_l > 0.25)) {
                    $status = 'warning';
                }
            }
            
            return [
                'name' => $pond->name,
                'status' => $status,
                'temperature' => $waterQuality->temperature_c ?? 'N/A',
                'ph' => $waterQuality->ph ?? 'N/A',
                'do' => $waterQuality->do_mg_l ?? 'N/A',
                'ammonia' => $waterQuality->ammonia_mg_l ?? 'N/A',
            ];
        })->toArray();
    }
    
    private function getWaterQualityTrend($branch)
    {
        $days = collect(range(6, 0))->map(function($daysAgo) {
            return Carbon::now()->subDays($daysAgo);
        });
        
        $labels = $days->map(function($date) {
            return $date->format('d/m');
        })->toArray();
        
        $temperature = [];
        $ph = [];
        $do = [];
        $ammonia = [];
        
        foreach ($days as $date) {
            $dayData = WaterQuality::whereHas('pond', function($query) use ($branch) {
                $query->where('branch_id', $branch->id);
            })
            ->whereDate('date_recorded', $date)
            ->whereNull('deleted_at')
            ->selectRaw('AVG(temperature_c) as avg_temp, AVG(ph) as avg_ph, AVG(do_mg_l) as avg_do, AVG(ammonia_mg_l) as avg_ammonia')
            ->first();
            
            $temperature[] = $dayData && $dayData->avg_temp ? round($dayData->avg_temp, 1) : 28;
            $ph[] = $dayData && $dayData->avg_ph ? round($dayData->avg_ph, 1) : 7.0;
            $do[] = $dayData && $dayData->avg_do ? round($dayData->avg_do, 1) : 6.0;
            $ammonia[] = $dayData && $dayData->avg_ammonia ? round($dayData->avg_ammonia, 2) : 0.1;
        }
        
        return [
            'labels' => $labels,
            'temperature' => $temperature,
            'ph' => $ph,
            'do' => $do,
            'ammonia' => $ammonia
        ];
    }
    
    private function getFishSalesAnalysis($branch)
    {
        $sales = Sale::whereHas('fishBatch.pond', function($query) use ($branch) {
            $query->where('branch_id', $branch->id);
        })
        ->with(['fishBatch.fishType'])
        ->whereMonth('date', Carbon::now()->month)
        ->whereNull('deleted_at')
        ->get();
        
        $topFishSales = $sales->groupBy(function($sale) {
            return $sale->fishBatch->fishType->name ?? 'Unknown';
        })
        ->map(function($group, $fishType) {
            return (object)[
                'fish_name' => $fishType,
                'total_quantity' => $group->sum('quantity_fish'),
                'total_revenue' => $group->sum('total_price')
            ];
        })
        ->sortByDesc('total_revenue')
        ->values();
        
        return [
            'top_fish_sales' => $topFishSales,
            'chart_data' => [
                'labels' => $topFishSales->take(5)->pluck('fish_name')->toArray(),
                'revenues' => $topFishSales->take(5)->pluck('total_revenue')->toArray()
            ]
        ];
    }
    
    private function calculateSurvivalRate($branch)
    {
        $batches = FishBatch::whereHas('pond', function($query) use ($branch) {
            $query->where('branch_id', $branch->id);
        })->whereNull('deleted_at')->get();
        
        $totalInitial = $batches->sum('initial_count');
        $totalDead = Mortality::whereIn('fish_batch_id', $batches->pluck('id'))
            ->whereNull('deleted_at')
            ->sum('dead_count');
        
        return $totalInitial > 0 ? round((($totalInitial - $totalDead) / $totalInitial) * 100, 1) : 0;
    }
    
    private function calculateAverageFCR($branch)
    {
        $batches = FishBatch::whereHas('pond', function($query) use ($branch) {
            $query->where('branch_id', $branch->id);
        })->whereNull('deleted_at')->get();
        
        $totalFCR = 0;
        $count = 0;
        
        foreach ($batches as $batch) {
            // Hitung FCR berdasarkan total pakan / total berat ikan yang dijual
            $totalFeed = Feeding::where('fish_batch_id', $batch->id)
                ->whereNull('deleted_at')
                ->sum('feed_amount_kg');
            
            $totalWeight = Sale::where('fish_batch_id', $batch->id)
                ->whereNull('deleted_at')
                ->sum(DB::raw('quantity_fish * avg_weight_per_fish_kg'));
            
            if ($totalWeight > 0 && $totalFeed > 0) {
                $fcr = $totalFeed / $totalWeight;
                $totalFCR += $fcr;
                $count++;
            }
        }
        
        return $count > 0 ? round($totalFCR / $count, 2) : 1.5;
    }
    
    private function getHarvestPredictions($branch)
    {
        return FishBatch::whereHas('pond', function($query) use ($branch) {
            $query->where('branch_id', $branch->id);
        })
        ->whereNull('deleted_at')
        ->with(['pond', 'fishType'])
        ->get()
        ->map(function($batch) {
            $daysFromStart = Carbon::parse($batch->date_start)->diffInDays(Carbon::now());
            $estimatedHarvestDays = 90; // Assume 90 days cycle
            $daysLeft = max(0, $estimatedHarvestDays - $daysFromStart);
            
            // Ambil stok terbaru
            $latestSnapshot = FishStockSnapshot::where('fish_batch_id', $batch->id)
                ->latest('created_at')
                ->first();
            
            $currentStock = $latestSnapshot ? $latestSnapshot->current_stock : $batch->initial_count;
            
            return [
                'pond' => $batch->pond->name,
                'fish_type' => $batch->fishType->name ?? 'Unknown',
                'days_left' => $daysLeft,
                'status' => $daysLeft <= 7 ? 'ready' : 'growing',
                'estimated_weight' => round($currentStock * 0.3) // Assume 300g average
            ];
        })
        ->toArray();
    }
    
    private function getRecentAlerts($branch)
    {
        $alerts = collect();
        
        // Check water quality alerts
        $criticalWaterQuality = WaterQuality::whereHas('pond', function($query) use ($branch) {
            $query->where('branch_id', $branch->id);
        })
        ->where('date_recorded', '>=', Carbon::now()->subDays(1))
        ->where(function($query) {
            $query->where('temperature_c', '>', 32)
                  ->orWhere('ph', '<', 6.5)
                  ->orWhere('ph', '>', 8.5)
                  ->orWhere('do_mg_l', '<', 4)
                  ->orWhere('ammonia_mg_l', '>', 0.5);
        })
        ->whereNull('deleted_at')
        ->with('pond')
        ->get();
        
        foreach ($criticalWaterQuality as $wq) {
            $alerts->push([
                'message' => 'Kualitas air kritis',
                'detail' => "Kolam {$wq->pond->name} - pH: {$wq->ph}, DO: {$wq->do_mg_l}mg/L"
            ]);
        }
        
        return $alerts;
    }
}
