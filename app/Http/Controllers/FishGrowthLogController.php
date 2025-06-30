<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FishGrowthLogController extends Controller
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

        // Get fish growth logs with related data - optimized query
        $growthLogs = DB::table('fish_growth_logs as fgl')
            ->join('fish_batches as fb', 'fgl.fish_batch_id', '=', 'fb.id')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->join('fish_types as ft', 'fb.fish_type_id', '=', 'ft.id')
            ->leftJoin('users as u', 'fgl.created_by', '=', 'u.id')
            ->where('p.branch_id', $branchId)
            ->whereNull('fgl.deleted_at')
            ->whereNull('fb.deleted_at')
            ->select(
                'fgl.id',
                'fgl.week_number',
                'fgl.avg_weight_gram',
                'fgl.avg_length_cm',
                'fgl.date_recorded',
                'fgl.created_at',
                'fb.id as batch_id',
                'p.name as pond_name',
                'p.code as pond_code',
                'ft.name as fish_type_name',
                'u.full_name as created_by_name'
            )
            ->orderBy('fgl.date_recorded', 'desc')
            ->get();

        // Calculate growth trends for each log
        foreach ($growthLogs as $log) {
            // Get previous week data for comparison
            $previousWeek = DB::table('fish_growth_logs')
                ->where('fish_batch_id', $log->batch_id)
                ->where('week_number', $log->week_number - 1)
                ->whereNull('deleted_at')
                ->first();

            if ($previousWeek) {
                $log->weight_growth = $log->avg_weight_gram - $previousWeek->avg_weight_gram;
                $log->length_growth = $log->avg_length_cm - $previousWeek->avg_length_cm;
            } else {
                $log->weight_growth = 0;
                $log->length_growth = 0;
            }

            // Calculate batch age at recording
            $batchStart = DB::table('fish_batches')->where('id', $log->batch_id)->value('date_start');
            $log->batch_age_days = \Carbon\Carbon::parse($batchStart)->diffInDays($log->date_recorded);
        }

        // Summary stats
        $stats = [
            'total_records' => $growthLogs->count(),
            'avg_weight' => $growthLogs->avg('avg_weight_gram') ?: 0,
            'avg_length' => $growthLogs->avg('avg_length_cm') ?: 0,
            'active_batches' => $growthLogs->unique('batch_id')->count()
        ];

        // Get dropdown data for form
        $fishBatches = DB::table('fish_batches as fb')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->join('fish_types as ft', 'fb.fish_type_id', '=', 'ft.id')
            ->where('p.branch_id', $branchId)
            ->whereNull('fb.deleted_at')
            ->select('fb.id', 'p.name as pond_name', 'ft.name as fish_type_name')
            ->get();

        return view('user.fish-growth.index', compact('growthLogs', 'branchInfo', 'stats', 'fishBatches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'fish_batch_id' => 'required|exists:fish_batches,id',
            'week_number' => 'required|integer|min:1',
            'avg_weight_gram' => 'required|numeric|min:0',
            'avg_length_cm' => 'required|numeric|min:0',
            'date_recorded' => 'required|date|before_or_equal:today'
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

            // Check if record for this batch and week already exists
            $exists = DB::table('fish_growth_logs')
                ->where('fish_batch_id', $request->fish_batch_id)
                ->where('week_number', $request->week_number)
                ->whereNull('deleted_at')
                ->exists();

            if ($exists) {
                return response()->json(['success' => false, 'message' => 'Data pertumbuhan untuk minggu ini sudah ada'], 400);
            }

            DB::table('fish_growth_logs')->insert([
                'fish_batch_id' => $request->fish_batch_id,
                'week_number' => $request->week_number,
                'avg_weight_gram' => $request->avg_weight_gram,
                'avg_length_cm' => $request->avg_length_cm,
                'date_recorded' => $request->date_recorded,
                'created_by' => $this->getCurrentUserId(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data pertumbuhan berhasil ditambahkan!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan data pertumbuhan. Silakan coba lagi.'
            ], 500);
        }
    }

    public function show($id)
    {
        $growthLog = DB::table('fish_growth_logs as fgl')
            ->join('fish_batches as fb', 'fgl.fish_batch_id', '=', 'fb.id')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->where('fgl.id', $id)
            ->where('p.branch_id', $this->getCurrentBranchId())
            ->whereNull('fgl.deleted_at')
            ->select('fgl.*')
            ->first();

        if (!$growthLog) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json(['success' => true, 'data' => $growthLog]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'fish_batch_id' => 'required|exists:fish_batches,id',
            'week_number' => 'required|integer|min:1',
            'avg_weight_gram' => 'required|numeric|min:0',
            'avg_length_cm' => 'required|numeric|min:0',
            'date_recorded' => 'required|date|before_or_equal:today'
        ]);

        try {
            // Verify growth log belongs to current branch
            $growthLog = DB::table('fish_growth_logs as fgl')
                ->join('fish_batches as fb', 'fgl.fish_batch_id', '=', 'fb.id')
                ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
                ->where('fgl.id', $id)
                ->where('p.branch_id', $this->getCurrentBranchId())
                ->whereNull('fgl.deleted_at')
                ->first();

            if (!$growthLog) {
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

            // Check if record for this batch and week already exists (excluding current record)
            $exists = DB::table('fish_growth_logs')
                ->where('fish_batch_id', $request->fish_batch_id)
                ->where('week_number', $request->week_number)
                ->where('id', '!=', $id)
                ->whereNull('deleted_at')
                ->exists();

            if ($exists) {
                return response()->json(['success' => false, 'message' => 'Data pertumbuhan untuk minggu ini sudah ada'], 400);
            }

            DB::table('fish_growth_logs')
                ->where('id', $id)
                ->update([
                    'fish_batch_id' => $request->fish_batch_id,
                    'week_number' => $request->week_number,
                    'avg_weight_gram' => $request->avg_weight_gram,
                    'avg_length_cm' => $request->avg_length_cm,
                    'date_recorded' => $request->date_recorded,
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Data pertumbuhan berhasil diperbarui!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data pertumbuhan. Silakan coba lagi.'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            // Verify growth log belongs to current branch
            $growthLog = DB::table('fish_growth_logs as fgl')
                ->join('fish_batches as fb', 'fgl.fish_batch_id', '=', 'fb.id')
                ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
                ->where('fgl.id', $id)
                ->where('p.branch_id', $this->getCurrentBranchId())
                ->whereNull('fgl.deleted_at')
                ->first();

            if (!$growthLog) {
                return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
            }

            // Soft delete
            DB::table('fish_growth_logs')
                ->where('id', $id)
                ->update([
                    'deleted_at' => now(),
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Data pertumbuhan berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data pertumbuhan. Silakan coba lagi.'
            ], 500);
        }
    }
}
