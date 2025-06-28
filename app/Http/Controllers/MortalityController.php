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

            // Get current stock for the batch
            $initialCount = DB::table('fish_batches')->where('id', $mortality->batch_id)->value('initial_count');
            $totalSold = DB::table('sales')->where('fish_batch_id', $mortality->batch_id)->whereNull('deleted_at')->sum('quantity_fish');
            $totalMortality = DB::table('mortalities')->where('fish_batch_id', $mortality->batch_id)->whereNull('deleted_at')->sum('dead_count');

            $mortality->current_stock = $initialCount - $totalSold - $totalMortality;
            $mortality->mortality_rate = $initialCount > 0 ? round(($totalMortality / $initialCount) * 100, 2) : 0;
        }

        // Summary stats
        $stats = [
            'total_records' => $mortalities->count(),
            'total_dead_fish' => $mortalities->sum('dead_count'),
            'avg_mortality_rate' => $mortalities->avg('mortality_rate') ?: 0,
            'affected_batches' => $mortalities->unique('batch_id')->count()
        ];

        // Get dropdown data for form
        $fishBatches = DB::table('fish_batches as fb')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->join('fish_types as ft', 'fb.fish_type_id', '=', 'ft.id')
            ->where('p.branch_id', $branchId)
            ->whereNull('fb.deleted_at')
            ->select('fb.id', 'p.name as pond_name', 'ft.name as fish_type_name')
            ->get();

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
}
