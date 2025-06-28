<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FishBatchController extends Controller
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

        // Get fish batches with related data - optimized query
        $fishBatches = DB::table('fish_batches as fb')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->join('fish_types as ft', 'fb.fish_type_id', '=', 'ft.id')
            ->leftJoin('users as u', 'fb.created_by', '=', 'u.id')
            ->where('p.branch_id', $branchId)
            ->whereNull('fb.deleted_at')
            ->select(
                'fb.id',
                'fb.date_start',
                'fb.initial_count',
                'fb.notes',
                'fb.created_at',
                'p.name as pond_name',
                'p.code as pond_code',
                'ft.name as fish_type_name',
                'u.full_name as created_by_name'
            )
            ->orderBy('fb.created_at', 'desc')
            ->get();

        // Calculate current stock and age for each batch
        foreach ($fishBatches as $batch) {
            // Calculate current stock (initial - sold - mortality)
            $sold = DB::table('sales')
                ->where('fish_batch_id', $batch->id)
                ->whereNull('deleted_at')
                ->sum('quantity_fish');

            $mortality = DB::table('mortalities')
                ->where('fish_batch_id', $batch->id)
                ->whereNull('deleted_at')
                ->sum('dead_count');

            $batch->current_stock = $batch->initial_count - $sold - $mortality;

            // Calculate age in days
            $batch->age_days = \Carbon\Carbon::parse($batch->date_start)->diffInDays(now());
            $batch->age_weeks = floor($batch->age_days / 7);

            // Determine status
            if ($batch->current_stock <= 0) {
                $batch->status = 'finished';
            } elseif ($batch->age_days < 30) {
                $batch->status = 'new';
            } elseif ($batch->age_days < 90) {
                $batch->status = 'growing';
            } else {
                $batch->status = 'mature';
            }
        }

        // Summary stats
        $stats = [
            'total_batches' => $fishBatches->count(),
            'active_batches' => $fishBatches->where('status', '!=', 'finished')->count(),
            'total_current_stock' => $fishBatches->sum('current_stock'),
            'avg_age_days' => $fishBatches->where('status', '!=', 'finished')->avg('age_days') ?: 0
        ];

        // Get dropdown data for form
        $ponds = DB::table('ponds')->where('branch_id', $branchId)->select('id', 'name', 'code')->get();
        $fishTypes = DB::table('fish_types')->where('branch_id', $branchId)->select('id', 'name')->get();

        return view('user.fish-batches.index', compact('fishBatches', 'branchInfo', 'stats', 'ponds', 'fishTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'pond_id' => 'required|exists:ponds,id',
            'fish_type_id' => 'required|exists:fish_types,id',
            'date_start' => 'required|date|before_or_equal:today',
            'initial_count' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            // Verify pond belongs to current branch
            $pond = DB::table('ponds')->where('id', $request->pond_id)->where('branch_id', $this->getCurrentBranchId())->first();
            if (!$pond) {
                return response()->json(['success' => false, 'message' => 'Kolam tidak valid'], 400);
            }

            // Verify fish type belongs to current branch
            $fishType = DB::table('fish_types')->where('id', $request->fish_type_id)->where('branch_id', $this->getCurrentBranchId())->first();
            if (!$fishType) {
                return response()->json(['success' => false, 'message' => 'Jenis ikan tidak valid'], 400);
            }

            DB::table('fish_batches')->insert([
                'pond_id' => $request->pond_id,
                'fish_type_id' => $request->fish_type_id,
                'date_start' => $request->date_start,
                'initial_count' => $request->initial_count,
                'notes' => $request->notes ? trim($request->notes) : null,
                'created_by' => $this->getCurrentUserId(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Batch ikan berhasil ditambahkan!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan batch ikan. Silakan coba lagi.'
            ], 500);
        }
    }

    public function show($id)
    {
        $fishBatch = DB::table('fish_batches as fb')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->join('fish_types as ft', 'fb.fish_type_id', '=', 'ft.id')
            ->where('fb.id', $id)
            ->where('p.branch_id', $this->getCurrentBranchId())
            ->whereNull('fb.deleted_at')
            ->select('fb.*', 'p.name as pond_name', 'ft.name as fish_type_name')
            ->first();

        if (!$fishBatch) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json(['success' => true, 'data' => $fishBatch]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'pond_id' => 'required|exists:ponds,id',
            'fish_type_id' => 'required|exists:fish_types,id',
            'date_start' => 'required|date|before_or_equal:today',
            'initial_count' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            // Verify batch belongs to current branch
            $batch = DB::table('fish_batches as fb')
                ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
                ->where('fb.id', $id)
                ->where('p.branch_id', $this->getCurrentBranchId())
                ->whereNull('fb.deleted_at')
                ->first();

            if (!$batch) {
                return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
            }

            // Verify new pond belongs to current branch
            $pond = DB::table('ponds')->where('id', $request->pond_id)->where('branch_id', $this->getCurrentBranchId())->first();
            if (!$pond) {
                return response()->json(['success' => false, 'message' => 'Kolam tidak valid'], 400);
            }

            // Verify fish type belongs to current branch
            $fishType = DB::table('fish_types')->where('id', $request->fish_type_id)->where('branch_id', $this->getCurrentBranchId())->first();
            if (!$fishType) {
                return response()->json(['success' => false, 'message' => 'Jenis ikan tidak valid'], 400);
            }

            DB::table('fish_batches')
                ->where('id', $id)
                ->update([
                    'pond_id' => $request->pond_id,
                    'fish_type_id' => $request->fish_type_id,
                    'date_start' => $request->date_start,
                    'initial_count' => $request->initial_count,
                    'notes' => $request->notes ? trim($request->notes) : null,
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Batch ikan berhasil diperbarui!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui batch ikan. Silakan coba lagi.'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            // Verify batch belongs to current branch
            $batch = DB::table('fish_batches as fb')
                ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
                ->where('fb.id', $id)
                ->where('p.branch_id', $this->getCurrentBranchId())
                ->whereNull('fb.deleted_at')
                ->first();

            if (!$batch) {
                return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
            }

            // Check if batch has related data
            $hasRelatedData = DB::table('sales')->where('fish_batch_id', $id)->whereNull('deleted_at')->exists() ||
                DB::table('mortalities')->where('fish_batch_id', $id)->whereNull('deleted_at')->exists() ||
                DB::table('feedings')->where('fish_batch_id', $id)->whereNull('deleted_at')->exists();

            if ($hasRelatedData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Batch tidak dapat dihapus karena memiliki data terkait (penjualan, mortalitas, atau pemberian pakan).'
                ], 400);
            }

            // Soft delete
            DB::table('fish_batches')
                ->where('id', $id)
                ->update([
                    'deleted_at' => now(),
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Batch ikan berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus batch ikan. Silakan coba lagi.'
            ], 500);
        }
    }
}
