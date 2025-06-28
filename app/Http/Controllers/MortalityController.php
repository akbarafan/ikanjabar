<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MortalityController extends Controller
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

    private function calculateCurrentStock($batchId, $upToDate = null)
    {
        // Get initial count
        $initialCount = DB::table('fish_batches')->where('id', $batchId)->value('initial_count');

        // Calculate transferred IN (fish moved from other batches to this batch)
        $transferredInQuery = DB::table('fish_batch_transfers')
            ->where('target_batch_id', $batchId)
            ->whereNull('deleted_at');

        if ($upToDate) {
            $transferredInQuery->where('date_transfer', '<=', $upToDate);
        }
        $transferredIn = $transferredInQuery->sum('transferred_count');

        // Calculate transferred OUT (fish moved from this batch to other batches)
        $transferredOutQuery = DB::table('fish_batch_transfers')
            ->where('source_batch_id', $batchId)
            ->whereNull('deleted_at');

        if ($upToDate) {
            $transferredOutQuery->where('date_transfer', '<=', $upToDate);
        }
        $transferredOut = $transferredOutQuery->sum('transferred_count');

        // Calculate sold fish
        $soldQuery = DB::table('sales')
            ->where('fish_batch_id', $batchId)
            ->whereNull('deleted_at');

        if ($upToDate) {
            $soldQuery->where('date', '<=', $upToDate);
        }
        $sold = $soldQuery->sum('quantity_fish');

        // Calculate mortality (up to the specified date, excluding current record if updating)
        $mortalityQuery = DB::table('mortalities')
            ->where('fish_batch_id', $batchId)
            ->whereNull('deleted_at');

        if ($upToDate) {
            $mortalityQuery->where('date', '<=', $upToDate);
        }
        $mortality = $mortalityQuery->sum('dead_count');

        // Current stock = initial + transferred_in - transferred_out - sold - mortality
        return $initialCount + $transferredIn - $transferredOut - $sold - $mortality;
    }

    public function index()
    {
        $branchId = $this->getCurrentBranchId();

        // Get branch info
        $branchInfo = DB::table('branches')->find($branchId);

        // Get mortalities with related data - optimized query
        $mortalities = DB::table('mortalities as m')
            ->join('fish_batches as fb', 'm.fish_batch_id', '=', 'fb.id')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->join('fish_types as ft', 'fb.fish_type_id', '=', 'ft.id')
            ->leftJoin('users as u', 'm.created_by', '=', 'u.id')
            ->where('p.branch_id', $branchId)
            ->whereNull('m.deleted_at')
            ->whereNull('fb.deleted_at')
            ->select(
                'm.id',
                'm.date',
                'm.dead_count',
                'm.cause',
                'm.created_at',
                'fb.id as batch_id',
                'fb.date_start as batch_start',
                'fb.initial_count',
                'p.name as pond_name',
                'p.code as pond_code',
                'ft.name as fish_type_name',
                'u.full_name as created_by_name'
            )
            ->orderBy('m.date', 'desc')
            ->get();

        // Calculate additional data for each mortality
        foreach ($mortalities as $mortality) {
            // Calculate batch age at mortality date
            $mortality->batch_age_days = \Carbon\Carbon::parse($mortality->batch_start)->diffInDays($mortality->date);

            // Get stock at the time of mortality (before this mortality event)
            $stockBeforeMortality = $this->calculateCurrentStock($mortality->batch_id, $mortality->date);

            // Add back this mortality to get stock before this specific mortality
            $mortality->stock_before_mortality = $stockBeforeMortality + $mortality->dead_count;

            // Current stock after all events
            $mortality->current_stock = $this->calculateCurrentStock($mortality->batch_id);

            // Calculate mortality rate based on stock before this mortality
            $mortality->mortality_rate = $mortality->stock_before_mortality > 0 ?
                round(($mortality->dead_count / $mortality->stock_before_mortality) * 100, 2) : 0;

            // Get transfer data for this batch
            $transferredIn = DB::table('fish_batch_transfers')
                ->where('target_batch_id', $mortality->batch_id)
                ->where('date_transfer', '<=', $mortality->date)
                ->whereNull('deleted_at')
                ->sum('transferred_count');

            $transferredOut = DB::table('fish_batch_transfers')
                ->where('source_batch_id', $mortality->batch_id)
                ->where('date_transfer', '<=', $mortality->date)
                ->whereNull('deleted_at')
                ->sum('transferred_count');

            $mortality->transferred_in = $transferredIn;
            $mortality->transferred_out = $transferredOut;
        }

        // Summary stats
        $stats = [
            'total_records' => $mortalities->count(),
            'total_dead_fish' => $mortalities->sum('dead_count'),
            'avg_mortality_rate' => $mortalities->avg('mortality_rate') ?: 0,
            'affected_batches' => $mortalities->unique('batch_id')->count()
        ];

        // Get dropdown data for form - only active batches with stock > 0
        $fishBatches = DB::table('fish_batches as fb')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->join('fish_types as ft', 'fb.fish_type_id', '=', 'ft.id')
            ->where('p.branch_id', $branchId)
            ->whereNull('fb.deleted_at')
            ->select('fb.id', 'p.name as pond_name', 'ft.name as fish_type_name', 'fb.initial_count')
            ->get();

        // Filter batches with current stock > 0
        $fishBatches = $fishBatches->filter(function ($batch) {
            $currentStock = $this->calculateCurrentStock($batch->id);
            return $currentStock > 0;
        });

        return view('user.mortalities.index', compact('mortalities', 'branchInfo', 'stats', 'fishBatches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'fish_batch_id' => 'required|exists:fish_batches,id',
            'date' => 'required|date|before_or_equal:today',
            'dead_count' => 'required|integer|min:1',
            'cause' => 'nullable|string|max:500'
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

            // Check if mortality date is after batch start date
            $batchStart = DB::table('fish_batches')->where('id', $request->fish_batch_id)->value('date_start');
            if ($request->date < $batchStart) {
                return response()->json(['success' => false, 'message' => 'Tanggal mortalitas tidak boleh sebelum tanggal mulai batch'], 400);
            }

            // Check if there's enough stock at the mortality date
            $stockAtDate = $this->calculateCurrentStock($request->fish_batch_id, $request->date);
            if ($request->dead_count > $stockAtDate) {
                return response()->json([
                    'success' => false,
                    'message' => "Jumlah kematian ({$request->dead_count}) melebihi stok yang tersedia ({$stockAtDate}) pada tanggal tersebut"
                ], 400);
            }

            DB::table('mortalities')->insert([
                'fish_batch_id' => $request->fish_batch_id,
                'date' => $request->date,
                'dead_count' => $request->dead_count,
                'cause' => $request->cause ? trim($request->cause) : null,
                'created_by' => $this->getCurrentUserId(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data mortalitas berhasil ditambahkan!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan data mortalitas. Silakan coba lagi.'
            ], 500);
        }
    }

    public function show($id)
    {
        $mortality = DB::table('mortalities as m')
            ->join('fish_batches as fb', 'm.fish_batch_id', '=', 'fb.id')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->where('m.id', $id)
            ->where('p.branch_id', $this->getCurrentBranchId())
            ->whereNull('m.deleted_at')
            ->select('m.*')
            ->first();

        if (!$mortality) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json(['success' => true, 'data' => $mortality]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'fish_batch_id' => 'required|exists:fish_batches,id',
            'date' => 'required|date|before_or_equal:today',
            'dead_count' => 'required|integer|min:1',
            'cause' => 'nullable|string|max:500'
        ]);

        try {
            // Get existing mortality record
            $existingMortality = DB::table('mortalities as m')
                ->join('fish_batches as fb', 'm.fish_batch_id', '=', 'fb.id')
                ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
                ->where('m.id', $id)
                ->where('p.branch_id', $this->getCurrentBranchId())
                ->whereNull('m.deleted_at')
                ->select('m.*')
                ->first();

            if (!$existingMortality) {
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

            // Check if mortality date is after batch start date
            $batchStart = DB::table('fish_batches')->where('id', $request->fish_batch_id)->value('date_start');
            if ($request->date < $batchStart) {
                return response()->json(['success' => false, 'message' => 'Tanggal mortalitas tidak boleh sebelum tanggal mulai batch'], 400);
            }

            // Calculate stock at the new date (excluding current mortality record)
            $stockAtDate = $this->calculateCurrentStock($request->fish_batch_id, $request->date);

            // If updating the same batch, add back the old mortality count
            if ($existingMortality->fish_batch_id == $request->fish_batch_id) {
                $stockAtDate += $existingMortality->dead_count;
            }

            if ($request->dead_count > $stockAtDate) {
                return response()->json([
                    'success' => false,
                    'message' => "Jumlah kematian ({$request->dead_count}) melebihi stok yang tersedia ({$stockAtDate}) pada tanggal tersebut"
                ], 400);
            }

            DB::table('mortalities')
                ->where('id', $id)
                ->update([
                    'fish_batch_id' => $request->fish_batch_id,
                    'date' => $request->date,
                    'dead_count' => $request->dead_count,
                    'cause' => $request->cause ? trim($request->cause) : null,
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Data mortalitas berhasil diperbarui!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data mortalitas. Silakan coba lagi.'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            // Verify mortality belongs to current branch
            $mortality = DB::table('mortalities as m')
                ->join('fish_batches as fb', 'm.fish_batch_id', '=', 'fb.id')
                ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
                ->where('m.id', $id)
                ->where('p.branch_id', $this->getCurrentBranchId())
                ->whereNull('m.deleted_at')
                ->first();

            if (!$mortality) {
                return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
            }

            // Soft delete
            DB::table('mortalities')
                ->where('id', $id)
                ->update([
                    'deleted_at' => now(),
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Data mortalitas berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data mortalitas. Silakan coba lagi.'
            ], 500);
        }
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
