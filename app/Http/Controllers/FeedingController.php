<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeedingController extends Controller
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

        // Get feedings with related data - optimized query
        $feedings = DB::table('feedings as f')
            ->join('fish_batches as fb', 'f.fish_batch_id', '=', 'fb.id')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->join('fish_types as ft', 'fb.fish_type_id', '=', 'ft.id')
            ->leftJoin('users as u', 'f.created_by', '=', 'u.id')
            ->where('p.branch_id', $branchId)
            ->whereNull('f.deleted_at')
            ->whereNull('fb.deleted_at')
            ->select(
                'f.id',
                'f.date',
                'f.feed_type',
                'f.feed_amount_kg',
                'f.notes',
                'f.created_at',
                'fb.id as batch_id',
                'p.name as pond_name',
                'p.code as pond_code',
                'ft.name as fish_type_name',
                'u.full_name as created_by_name'
            )
            ->orderBy('f.date', 'desc')
            ->get();

        // Calculate additional info for each feeding
        foreach ($feedings as $feeding) {
            // Get current stock for the batch
            $batch = DB::table('fish_batches')->where('id', $feeding->batch_id)->first();
            
            // Calculate batch age at feeding date
            $feeding->batch_age_days = \Carbon\Carbon::parse($batch->date_start)->diffInDays($feeding->date);
        }

        // Summary stats
        $stats = [
            'total_feedings' => $feedings->count(),
            'total_feed_kg' => $feedings->sum('feed_amount_kg'),
            'avg_feed_per_day' => $feedings->groupBy('date')->avg(function ($group) {
                return $group->sum('feed_amount_kg');
            }) ?: 0,
            'active_batches' => $feedings->unique('batch_id')->count()
        ];

        // Get dropdown data for form
        $fishBatches = DB::table('fish_batches as fb')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->join('fish_types as ft', 'fb.fish_type_id', '=', 'ft.id')
            ->where('p.branch_id', $branchId)
            ->whereNull('fb.deleted_at')
            ->select('fb.id', 'p.name as pond_name', 'ft.name as fish_type_name')
            ->get();

        return view('user.feedings.index', compact('feedings', 'branchInfo', 'stats', 'fishBatches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'fish_batch_id' => 'required|exists:fish_batches,id',
            'date' => 'required|date|before_or_equal:today',
            'feed_type' => 'required|string|max:100',
            'feed_amount_kg' => 'required|numeric|min:0.1',
            'notes' => 'nullable|string|max:500'
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

            DB::table('feedings')->insert([
                'fish_batch_id' => $request->fish_batch_id,
                'date' => $request->date,
                'feed_type' => trim($request->feed_type),
                'feed_amount_kg' => $request->feed_amount_kg,
                'notes' => $request->notes ? trim($request->notes) : null,
                'created_by' => $this->getCurrentUserId(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data pemberian pakan berhasil ditambahkan!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan data pemberian pakan. Silakan coba lagi.'
            ], 500);
        }
    }

    public function show($id)
    {
        $feeding = DB::table('feedings as f')
            ->join('fish_batches as fb', 'f.fish_batch_id', '=', 'fb.id')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->where('f.id', $id)
            ->where('p.branch_id', $this->getCurrentBranchId())
            ->whereNull('f.deleted_at')
            ->select('f.*')
            ->first();

        if (!$feeding) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json(['success' => true, 'data' => $feeding]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'fish_batch_id' => 'required|exists:fish_batches,id',
            'date' => 'required|date|before_or_equal:today',
            'feed_type' => 'required|string|max:100',
            'feed_amount_kg' => 'required|numeric|min:0.1',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            // Verify feeding belongs to current branch
            $feeding = DB::table('feedings as f')
                ->join('fish_batches as fb', 'f.fish_batch_id', '=', 'fb.id')
                ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
                ->where('f.id', $id)
                ->where('p.branch_id', $this->getCurrentBranchId())
                ->whereNull('f.deleted_at')
                ->first();

            if (!$feeding) {
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

            DB::table('feedings')
                ->where('id', $id)
                ->update([
                    'fish_batch_id' => $request->fish_batch_id,
                    'date' => $request->date,
                    'feed_type' => trim($request->feed_type),
                    'feed_amount_kg' => $request->feed_amount_kg,
                    'notes' => $request->notes ? trim($request->notes) : null,
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Data pemberian pakan berhasil diperbarui!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data pemberian pakan. Silakan coba lagi.'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            // Verify feeding belongs to current branch
            $feeding = DB::table('feedings as f')
                ->join('fish_batches as fb', 'f.fish_batch_id', '=', 'fb.id')
                ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
                ->where('f.id', $id)
                ->where('p.branch_id', $this->getCurrentBranchId())
                ->whereNull('f.deleted_at')
                ->first();

            if (!$feeding) {
                return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
            }

            // Soft delete
            DB::table('feedings')
                ->where('id', $id)
                ->update([
                    'deleted_at' => now(),
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Data pemberian pakan berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data pemberian pakan. Silakan coba lagi.'
            ], 500);
        }
    }
}
