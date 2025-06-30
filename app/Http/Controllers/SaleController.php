<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    private function getCurrentBranchId()
    {
        // Sementara hardcode untuk demo, nanti bisa dari session/auth
        return 1;
    }

    private function getCurrentUserId()
    {
        // Sementara hardcode untuk demo, nanti bisa dari auth
        return DB::table('users')->where('branch_id', $this->getCurrentBranchId())->first()->id ?? '550e8400-e29b-41d4-a716-446655440000';
    }

    public function index()
    {
        $branchId = $this->getCurrentBranchId();

        // Get branch info
        $branchInfo = DB::table('branches')->find($branchId);

        // Get sales with related data - optimized query
        $sales = DB::table('sales as s')
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
                'p.name as pond_name',
                'p.code as pond_code',
                'ft.name as fish_type_name',
                'u.full_name as created_by_name'
            )
            ->orderBy('s.date', 'desc')
            ->get();

        // Calculate additional metrics
        foreach ($sales as $sale) {
            $sale->total_weight_kg = $sale->quantity_fish * $sale->avg_weight_per_fish_kg;
            $sale->price_per_fish = $sale->avg_weight_per_fish_kg * $sale->price_per_kg;
        }

        // Summary stats
        $stats = [
            'total_sales' => $sales->count(),
            'total_revenue' => $sales->sum('total_price'),
            'total_fish_sold' => $sales->sum('quantity_fish'),
            'avg_price_per_kg' => $sales->avg('price_per_kg') ?: 0
        ];

        // Get dropdown data for form
        $fishBatches = DB::table('fish_batches as fb')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->join('fish_types as ft', 'fb.fish_type_id', '=', 'ft.id')
            ->where('p.branch_id', $branchId)
            ->whereNull('fb.deleted_at')
            ->select('fb.id', 'p.name as pond_name', 'ft.name as fish_type_name')
            ->get();

        return view('user.sales.index', compact('sales', 'branchInfo', 'stats', 'fishBatches'));
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
            // Verify batch belongs to current branch
            $batch = DB::table('fish_batches as fb')
                ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
                ->where('fb.id', $request->fish_batch_id)
                ->where('p.branch_id', $this->getCurrentBranchId())
                ->whereNull('fb.deleted_at')
                ->first();

            if (!$batch) {
                return response()->json(['success' => false, 'message' => 'Batch ikan tidak valid'], 400);
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
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan data penjualan. Silakan coba lagi.'
            ], 500);
        }
    }

    public function show($id)
    {
        $sale = DB::table('sales as s')
            ->join('fish_batches as fb', 's.fish_batch_id', '=', 'fb.id')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->where('s.id', $id)
            ->where('p.branch_id', $this->getCurrentBranchId())
            ->whereNull('s.deleted_at')
            ->select('s.*')
            ->first();

        if (!$sale) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
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
            // Verify sale belongs to current branch
            $sale = DB::table('sales as s')
                ->join('fish_batches as fb', 's.fish_batch_id', '=', 'fb.id')
                ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
                ->where('s.id', $id)
                ->where('p.branch_id', $this->getCurrentBranchId())
                ->whereNull('s.deleted_at')
                ->first();

            if (!$sale) {
                return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
            }

            // Verify new batch belongs to current branch
            $batch = DB::table('fish_batches as fb')
                ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
                ->where('fb.id', $request->fish_batch_id)
                ->where('p.branch_id', $this->getCurrentBranchId())
                ->whereNull('fb.deleted_at')
                ->first();

            if (!$batch) {
                return response()->json(['success' => false, 'message' => 'Batch ikan tidak valid'], 400);
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
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data penjualan. Silakan coba lagi.'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            // Verify sale belongs to current branch
            $sale = DB::table('sales as s')
                ->join('fish_batches as fb', 's.fish_batch_id', '=', 'fb.id')
                ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
                ->where('s.id', $id)
                ->where('p.branch_id', $this->getCurrentBranchId())
                ->whereNull('s.deleted_at')
                ->first();

            if (!$sale) {
                return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
            }

            // Soft delete
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
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data penjualan. Silakan coba lagi.'
            ], 500);
        }
    }
}
