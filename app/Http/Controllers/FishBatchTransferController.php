<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FishBatchTransferController extends Controller
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

        // Get transfer logs with related data - optimized query
        $transfers = DB::table('fish_batch_transfers as fbt')
            ->join('fish_batches as fb_source', 'fbt.source_batch_id', '=', 'fb_source.id')
            ->join('fish_batches as fb_target', 'fbt.target_batch_id', '=', 'fb_target.id')
            ->join('ponds as p_source', 'fb_source.pond_id', '=', 'p_source.id')
            ->join('ponds as p_target', 'fb_target.pond_id', '=', 'p_target.id')
            ->join('fish_types as ft_source', 'fb_source.fish_type_id', '=', 'ft_source.id')
            ->join('fish_types as ft_target', 'fb_target.fish_type_id', '=', 'ft_target.id')
            ->leftJoin('users as u', 'fbt.created_by', '=', 'u.id')
            ->where('p_source.branch_id', $branchId)
            ->where('p_target.branch_id', $branchId)
            ->whereNull('fbt.deleted_at')
            ->whereNull('fb_source.deleted_at')
            ->whereNull('fb_target.deleted_at')
            ->select(
                'fbt.id',
                'fbt.transferred_count',
                'fbt.date_transfer',
                'fbt.notes',
                'fbt.created_at',
                'fb_source.id as source_batch_id',
                'fb_target.id as target_batch_id',
                'p_source.name as source_pond_name',
                'p_source.code as source_pond_code',
                'p_target.name as target_pond_name',
                'p_target.code as target_pond_code',
                'ft_source.name as source_fish_type',
                'ft_target.name as target_fish_type',
                'u.full_name as created_by_name'
            )
            ->orderBy('fbt.date_transfer', 'desc')
            ->get();

        // Calculate current stock for each batch involved
        foreach ($transfers as $transfer) {
            // Calculate source batch current stock
            $sourceSold = DB::table('sales')->where('fish_batch_id', $transfer->source_batch_id)->whereNull('deleted_at')->sum('quantity_fish');
            $sourceMortality = DB::table('mortalities')->where('fish_batch_id', $transfer->source_batch_id)->whereNull('deleted_at')->sum('dead_count');
            $sourceTransferredOut = DB::table('fish_batch_transfers')->where('source_batch_id', $transfer->source_batch_id)->whereNull('deleted_at')->sum('transferred_count');
            $sourceTransferredIn = DB::table('fish_batch_transfers')->where('target_batch_id', $transfer->source_batch_id)->whereNull('deleted_at')->sum('transferred_count');

            $sourceInitial = DB::table('fish_batches')->where('id', $transfer->source_batch_id)->value('initial_count');
            $transfer->source_current_stock = $sourceInitial + $sourceTransferredIn - $sourceSold - $sourceMortality - $sourceTransferredOut;

            // Calculate target batch current stock
            $targetSold = DB::table('sales')->where('fish_batch_id', $transfer->target_batch_id)->whereNull('deleted_at')->sum('quantity_fish');
            $targetMortality = DB::table('mortalities')->where('fish_batch_id', $transfer->target_batch_id)->whereNull('deleted_at')->sum('dead_count');
            $targetTransferredOut = DB::table('fish_batch_transfers')->where('source_batch_id', $transfer->target_batch_id)->whereNull('deleted_at')->sum('transferred_count');
            $targetTransferredIn = DB::table('fish_batch_transfers')->where('target_batch_id', $transfer->target_batch_id)->whereNull('deleted_at')->sum('transferred_count');

            $targetInitial = DB::table('fish_batches')->where('id', $transfer->target_batch_id)->value('initial_count');
            $transfer->target_current_stock = $targetInitial + $targetTransferredIn - $targetSold - $targetMortality - $targetTransferredOut;
        }

        // Summary stats
        $stats = [
            'total_transfers' => $transfers->count(),
            'total_fish_transferred' => $transfers->sum('transferred_count'),
            'this_month_transfers' => $transfers->where('date_transfer', '>=', now()->startOfMonth())->count(),
            'active_batches' => DB::table('fish_batches as fb')
                ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
                ->where('p.branch_id', $branchId)
                ->whereNull('fb.deleted_at')
                ->count()
        ];

        // Get dropdown data for form - only active batches with stock
        $activeBatches = DB::table('fish_batches as fb')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->join('fish_types as ft', 'fb.fish_type_id', '=', 'ft.id')
            ->where('p.branch_id', $branchId)
            ->whereNull('fb.deleted_at')
            ->select('fb.id', 'p.name as pond_name', 'p.code as pond_code', 'ft.name as fish_type_name', 'fb.initial_count')
            ->get();

        return view('user.fish-transfers.index', compact('transfers', 'branchInfo', 'stats', 'activeBatches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'source_batch_id' => 'required|exists:fish_batches,id|different:target_batch_id',
            'target_batch_id' => 'required|exists:fish_batches,id',
            'transferred_count' => 'required|integer|min:1',
            'date_transfer' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            // Verify both batches belong to current branch
            $sourceBatch = DB::table('fish_batches as fb')
                ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
                ->where('fb.id', $request->source_batch_id)
                ->where('p.branch_id', $this->getCurrentBranchId())
                ->whereNull('fb.deleted_at')
                ->first();

            $targetBatch = DB::table('fish_batches as fb')
                ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
                ->where('fb.id', $request->target_batch_id)
                ->where('p.branch_id', $this->getCurrentBranchId())
                ->whereNull('fb.deleted_at')
                ->first();

            if (!$sourceBatch || !$targetBatch) {
                return response()->json(['success' => false, 'message' => 'Batch tidak valid'], 400);
            }

            // Calculate source batch current stock
            $sold = DB::table('sales')->where('fish_batch_id', $request->source_batch_id)->whereNull('deleted_at')->sum('quantity_fish');
            $mortality = DB::table('mortalities')->where('fish_batch_id', $request->source_batch_id)->whereNull('deleted_at')->sum('dead_count');
            $transferredOut = DB::table('fish_batch_transfers')->where('source_batch_id', $request->source_batch_id)->whereNull('deleted_at')->sum('transferred_count');
            $transferredIn = DB::table('fish_batch_transfers')->where('target_batch_id', $request->source_batch_id)->whereNull('deleted_at')->sum('transferred_count');

            $currentStock = $sourceBatch->initial_count + $transferredIn - $sold - $mortality - $transferredOut;

            if ($currentStock < $request->transferred_count) {
                return response()->json(['success' => false, 'message' => 'Stok tidak mencukupi. Stok tersedia: ' . number_format($currentStock)], 400);
            }

            DB::table('fish_batch_transfers')->insert([
                'source_batch_id' => $request->source_batch_id,
                'target_batch_id' => $request->target_batch_id,
                'transferred_count' => $request->transferred_count,
                'date_transfer' => $request->date_transfer,
                'notes' => $request->notes ? trim($request->notes) : null,
                'created_by' => $this->getCurrentUserId(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transfer batch berhasil ditambahkan!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan transfer batch. Silakan coba lagi.'
            ], 500);
        }
    }

    public function show($id)
    {
        $transfer = DB::table('fish_batch_transfers as fbt')
            ->join('fish_batches as fb_source', 'fbt.source_batch_id', '=', 'fb_source.id')
            ->join('fish_batches as fb_target', 'fbt.target_batch_id', '=', 'fb_target.id')
            ->join('ponds as p_source', 'fb_source.pond_id', '=', 'p_source.id')
            ->join('ponds as p_target', 'fb_target.pond_id', '=', 'p_target.id')
            ->where('fbt.id', $id)
            ->where('p_source.branch_id', $this->getCurrentBranchId())
            ->where('p_target.branch_id', $this->getCurrentBranchId())
            ->whereNull('fbt.deleted_at')
            ->select('fbt.*')
            ->first();

        if (!$transfer) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json(['success' => true, 'data' => $transfer]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'source_batch_id' => 'required|exists:fish_batches,id|different:target_batch_id',
            'target_batch_id' => 'required|exists:fish_batches,id',
            'transferred_count' => 'required|integer|min:1',
            'date_transfer' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            // Verify transfer belongs to current branch
            $transfer = DB::table('fish_batch_transfers as fbt')
                ->join('fish_batches as fb_source', 'fbt.source_batch_id', '=', 'fb_source.id')
                ->join('fish_batches as fb_target', 'fbt.target_batch_id', '=', 'fb_target.id')
                ->join('ponds as p_source', 'fb_source.pond_id', '=', 'p_source.id')
                ->join('ponds as p_target', 'fb_target.pond_id', '=', 'p_target.id')
                ->where('fbt.id', $id)
                ->where('p_source.branch_id', $this->getCurrentBranchId())
                ->where('p_target.branch_id', $this->getCurrentBranchId())
                ->whereNull('fbt.deleted_at')
                ->first();

            if (!$transfer) {
                return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
            }

            DB::table('fish_batch_transfers')
                ->where('id', $id)
                ->update([
                    'source_batch_id' => $request->source_batch_id,
                    'target_batch_id' => $request->target_batch_id,
                    'transferred_count' => $request->transferred_count,
                    'date_transfer' => $request->date_transfer,
                    'notes' => $request->notes ? trim($request->notes) : null,
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Transfer batch berhasil diperbarui!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui transfer batch. Silakan coba lagi.'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            // Verify transfer belongs to current branch
            $transfer = DB::table('fish_batch_transfers as fbt')
                ->join('fish_batches as fb_source', 'fbt.source_batch_id', '=', 'fb_source.id')
                ->join('fish_batches as fb_target', 'fbt.target_batch_id', '=', 'fb_target.id')
                ->join('ponds as p_source', 'fb_source.pond_id', '=', 'p_source.id')
                ->join('ponds as p_target', 'fb_target.pond_id', '=', 'p_target.id')
                ->where('fbt.id', $id)
                ->where('p_source.branch_id', $this->getCurrentBranchId())
                ->where('p_target.branch_id', $this->getCurrentBranchId())
                ->whereNull('fbt.deleted_at')
                ->first();

            if (!$transfer) {
                return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
            }

            // Soft delete
            DB::table('fish_batch_transfers')
                ->where('id', $id)
                ->update([
                    'deleted_at' => now(),
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Transfer batch berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus transfer batch. Silakan coba lagi.'
            ], 500);
        }
    }
}
