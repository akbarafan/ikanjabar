<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SaleController extends Controller
{
    private const ITEMS_PER_PAGE = 10;

    private function getCurrentBranchId()
    {
        // Sementara hardcode untuk demo, nanti bisa dari session/auth
        return Auth::user()->branch_id;
    }

    private function getCurrentUserId()
    {
        // Sementara hardcode untuk demo, nanti bisa dari auth
        return Auth::user()->id;
    }

    public function index(Request $request)
    {
        $branchId = $this->getCurrentBranchId();
        $page = (int) $request->get('page', 1);
        $offset = ($page - 1) * self::ITEMS_PER_PAGE;

        // Get branch info
        $branchInfo = DB::table('branches')->find($branchId);

        // Get total count for pagination
        $totalSales = DB::table('sales as s')
            ->join('fish_batches as fb', 's.fish_batch_id', '=', 'fb.id')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->where('p.branch_id', $branchId)
            ->whereNull('s.deleted_at')
            ->whereNull('fb.deleted_at')
            ->count();

        // Get sales with pagination - optimized query
        $sales = $this->getSalesQuery($branchId)
            ->limit(self::ITEMS_PER_PAGE)
            ->offset($offset)
            ->get();

        // Process sales data for mobile display
        $this->processSalesData($sales);

        // Calculate stats
        $stats = $this->calculateStats($branchId);

        // Get active fish batches for form
        $fishBatches = $this->getActiveFishBatches($branchId);

        // Pagination info
        $pagination = [
            'current_page' => $page,
            'total_pages' => ceil($totalSales / self::ITEMS_PER_PAGE),
            'total_items' => $totalSales,
            'per_page' => self::ITEMS_PER_PAGE,
            'has_prev' => $page > 1,
            'has_next' => $page < ceil($totalSales / self::ITEMS_PER_PAGE),
            'prev_page' => $page > 1 ? $page - 1 : null,
            'next_page' => $page < ceil($totalSales / self::ITEMS_PER_PAGE) ? $page + 1 : null
        ];

        return view('user.sales.index', compact(
            'sales',
            'branchInfo',
            'stats',
            'fishBatches',
            'pagination'
        ));
    }

    private function getSalesQuery($branchId)
    {
        return DB::table('sales as s')
            ->join('fish_batches as fb', 's.fish_batch_id', '=', 'fb.id')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->join('fish_types as ft', 'fb.fish_type_id', '=', 'ft.id')
            ->leftJoin('users as u', 's.created_by', '=', 'u.id')
            ->where('p.branch_id', $branchId)
            ->whereNull('s.deleted_at')
            ->whereNull('fb.deleted_at')
            ->select(
                's.id',
                's.date',
                's.quantity_fish',
                's.avg_weight_per_fish_kg',
                's.price_per_kg',
                's.buyer_name',
                's.total_price',
                's.created_at',
                'fb.id as batch_id',
                'fb.date_start as batch_date_start',
                'fb.documentation_file as batch_image',
                'p.name as pond_name',
                'p.code as pond_code',
                'ft.name as fish_type_name',
                'u.full_name as created_by_name'
            )
            ->orderBy('s.date', 'desc')
            ->orderBy('s.created_at', 'desc');
    }

    private function processSalesData($sales)
    {
        foreach ($sales as $sale) {
            // Add image URLs
            $sale->batch_image_url = $sale->batch_image
                ? Storage::url($sale->batch_image)
                : null;

            // Calculate additional metrics
            $sale->total_weight_kg = $sale->quantity_fish * $sale->avg_weight_per_fish_kg;
            $sale->price_per_fish = $sale->avg_weight_per_fish_kg * $sale->price_per_kg;

            // Calculate batch age at sale date
            $sale->batch_age_days = \Carbon\Carbon::parse($sale->batch_date_start)
                ->diffInDays($sale->date);

            // Format for mobile display
            $sale->formatted_date = \Carbon\Carbon::parse($sale->date)->format('d M');
            $sale->formatted_quantity = number_format($sale->quantity_fish);
            $sale->formatted_total_price = 'Rp ' . number_format($sale->total_price);
            $sale->formatted_weight = number_format($sale->total_weight_kg, 1) . ' kg';
            $sale->short_buyer_name = \Str::limit($sale->buyer_name, 15);
        }
    }

    private function calculateStats($branchId)
    {
        $statsQuery = DB::table('sales as s')
            ->join('fish_batches as fb', 's.fish_batch_id', '=', 'fb.id')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->where('p.branch_id', $branchId)
            ->whereNull('s.deleted_at')
            ->whereNull('fb.deleted_at');

        $totalSales = $statsQuery->count();
        $totalRevenue = $statsQuery->sum('s.total_price');
        $totalFishSold = $statsQuery->sum('s.quantity_fish');
        $avgPricePerKg = $statsQuery->avg('s.price_per_kg') ?: 0;

        // Calculate this month's performance
        $thisMonthRevenue = DB::table('sales as s')
            ->join('fish_batches as fb', 's.fish_batch_id', '=', 'fb.id')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->where('p.branch_id', $branchId)
            ->whereMonth('s.date', now()->month)
            ->whereYear('s.date', now()->year)
            ->whereNull('s.deleted_at')
            ->whereNull('fb.deleted_at')
            ->sum('s.total_price');

        return [
            'total_sales' => $totalSales,
            'total_revenue' => $totalRevenue,
            'total_fish_sold' => $totalFishSold,
            'avg_price_per_kg' => $avgPricePerKg,
            'this_month_revenue' => $thisMonthRevenue
        ];
    }

    private function getActiveFishBatches($branchId)
    {
        $fishBatches = DB::table('fish_batches as fb')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->join('fish_types as ft', 'fb.fish_type_id', '=', 'ft.id')
            ->where('p.branch_id', $branchId)
            ->whereNull('fb.deleted_at')
            ->select(
                'fb.id',
                'fb.date_start',
                'fb.initial_count',
                'fb.documentation_file',
                'p.name as pond_name',
                'p.code as pond_code',
                'ft.name as fish_type_name'
            )
            ->orderBy('fb.created_at', 'desc')
            ->get();

        // Process batch data
        foreach ($fishBatches as $batch) {
            $batch->image_url = $batch->documentation_file
                ? Storage::url($batch->documentation_file)
                : null;

            $batch->current_stock = $this->calculateCurrentStock($batch->id);
            $batch->age_days = \Carbon\Carbon::parse($batch->date_start)->diffInDays(now());
            $batch->is_active = $batch->current_stock > 0;
        }

        return $fishBatches->where('is_active', true)->values();
    }

    public function store(Request $request)
    {
        $request->validate([
            'fish_batch_id' => 'required|exists:fish_batches,id',
            'date' => 'required|date|before_or_equal:today',
            'quantity_fish' => 'required|integer|min:1',
            'avg_weight_per_fish_kg' => 'required|numeric|min:0.01',
            'price_per_kg' => 'required|numeric|min:0.01',
            'buyer_name' => 'required|string|max:100'
        ]);

        try {
            // Verify batch belongs to current branch and is active
            $batch = $this->validateBatch($request->fish_batch_id);
            if (!$batch) {
                return response()->json([
                    'success' => false,
                    'message' => 'Batch ikan tidak valid'
                ], 400);
            }

            // Check if batch has enough stock
            $currentStock = $this->calculateCurrentStock($batch->id);
            if ($currentStock < $request->quantity_fish) {
                return response()->json([
                    'success' => false,
                    'message' => "Stok tidak mencukupi. Stok tersedia: {$currentStock} ekor"
                ], 400);
            }

            // Calculate total price
            $totalPrice = $request->quantity_fish * $request->avg_weight_per_fish_kg * $request->price_per_kg;

            DB::table('sales')->insert([
                'fish_batch_id' => $request->fish_batch_id,
                'date' => $request->date,
                'quantity_fish' => $request->quantity_fish,
                'avg_weight_per_fish_kg' => $request->avg_weight_per_fish_kg,
                'price_per_kg' => $request->price_per_kg,
                'buyer_name' => trim($request->buyer_name),
                'total_price' => $totalPrice,
                'created_by' => $this->getCurrentUserId(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data penjualan berhasil ditambahkan!'
            ]);
        } catch (\Exception $e) {
            \Log::error('Sale store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan data penjualan. Silakan coba lagi.'
            ], 500);
        }
    }

    public function show($id)
    {
        $sale = $this->findSale($id);

        if (!$sale) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        return response()->json(['success' => true, 'data' => $sale]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'fish_batch_id' => 'required|exists:fish_batches,id',
            'date' => 'required|date|before_or_equal:today',
            'quantity_fish' => 'required|integer|min:1',
            'avg_weight_per_fish_kg' => 'required|numeric|min:0.01',
            'price_per_kg' => 'required|numeric|min:0.01',
            'buyer_name' => 'required|string|max:100'
        ]);

        try {
            // Verify sale exists
            $sale = $this->findSale($id);
            if (!$sale) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            // Verify new batch
            $batch = $this->validateBatch($request->fish_batch_id);
            if (!$batch) {
                return response()->json([
                    'success' => false,
                    'message' => 'Batch ikan tidak valid'
                ], 400);
            }

            // Check stock availability (add back the current sale quantity for calculation)
            $currentStock = $this->calculateCurrentStock($batch->id);
            if ($sale->fish_batch_id == $request->fish_batch_id) {
                // Same batch, add back the original quantity
                $availableStock = $currentStock + $sale->quantity_fish;
            } else {
                // Different batch, use current stock
                $availableStock = $currentStock;
            }

            if ($availableStock < $request->quantity_fish) {
                return response()->json([
                    'success' => false,
                    'message' => "Stok tidak mencukupi. Stok tersedia: {$availableStock} ekor"
                ], 400);
            }

            // Calculate total price
            $totalPrice = $request->quantity_fish * $request->avg_weight_per_fish_kg * $request->price_per_kg;

            DB::table('sales')
                ->where('id', $id)
                ->update([
                    'fish_batch_id' => $request->fish_batch_id,
                    'date' => $request->date,
                    'quantity_fish' => $request->quantity_fish,
                    'avg_weight_per_fish_kg' => $request->avg_weight_per_fish_kg,
                    'price_per_kg' => $request->price_per_kg,
                    'buyer_name' => trim($request->buyer_name),
                    'total_price' => $totalPrice,
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Data penjualan berhasil diperbarui!'
            ]);
        } catch (\Exception $e) {
            \Log::error('Sale update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data penjualan. Silakan coba lagi.'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $sale = $this->findSale($id);
            if (!$sale) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            DB::table('sales')
                ->where('id', $id)
                ->update([
                    'deleted_at' => now(),
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Data penjualan berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            \Log::error('Sale delete error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data penjualan. Silakan coba lagi.'
            ], 500);
        }
    }

    private function findSale($id)
    {
        return DB::table('sales as s')
            ->join('fish_batches as fb', 's.fish_batch_id', '=', 'fb.id')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->where('s.id', $id)
            ->where('p.branch_id', $this->getCurrentBranchId())
            ->whereNull('s.deleted_at')
            ->select('s.*')
            ->first();
    }

    private function validateBatch($batchId)
    {
        return DB::table('fish_batches as fb')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->where('fb.id', $batchId)
            ->where('p.branch_id', $this->getCurrentBranchId())
            ->whereNull('fb.deleted_at')
            ->select('fb.*')
            ->first();
    }

    private function calculateCurrentStock($batchId)
    {
        $batch = DB::table('fish_batches')->where('id', $batchId)->first();
        if (!$batch) return 0;

        $sold = DB::table('sales')
            ->where('fish_batch_id', $batchId)
            ->whereNull('deleted_at')
            ->sum('quantity_fish');

        $mortality = DB::table('mortalities')
            ->where('fish_batch_id', $batchId)
            ->whereNull('deleted_at')
            ->sum('dead_count');

        $transferredOut = DB::table('fish_batch_transfers')
            ->where('source_batch_id', $batchId)
            ->whereNull('deleted_at')
            ->sum('transferred_count');

        $transferredIn = DB::table('fish_batch_transfers')
            ->where('target_batch_id', $batchId)
            ->whereNull('deleted_at')
            ->sum('transferred_count');

        return $batch->initial_count + $transferredIn - $sold - $mortality - $transferredOut;
    }

    public function getBatchStock($batchId)
    {
        try {
            $currentStock = $this->calculateCurrentStock($batchId);
            return response()->json([
                'success' => true,
                'current_stock' => $currentStock
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data stok'
            ], 500);
        }
    }
}
