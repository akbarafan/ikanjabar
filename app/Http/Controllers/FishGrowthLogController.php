<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FishGrowthLogController extends Controller
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
        $totalGrowthLogs = DB::table('fish_growth_logs as fgl')
            ->join('fish_batches as fb', 'fgl.fish_batch_id', '=', 'fb.id')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->where('p.branch_id', $branchId)
            ->whereNull('fgl.deleted_at')
            ->whereNull('fb.deleted_at')
            ->count();

        // Get growth logs with pagination - optimized query
        $growthLogs = $this->getGrowthLogsQuery($branchId)
            ->limit(self::ITEMS_PER_PAGE)
            ->offset($offset)
            ->get();

        // Process growth logs data
        $this->processGrowthLogsData($growthLogs);

        // Calculate stats
        $stats = $this->calculateStats($branchId);

        // Get active fish batches for form
        $fishBatches = $this->getActiveFishBatches($branchId);

        // Pagination info
        $pagination = [
            'current_page' => $page,
            'total_pages' => ceil($totalGrowthLogs / self::ITEMS_PER_PAGE),
            'total_items' => $totalGrowthLogs,
            'per_page' => self::ITEMS_PER_PAGE,
            'has_prev' => $page > 1,
            'has_next' => $page < ceil($totalGrowthLogs / self::ITEMS_PER_PAGE),
            'prev_page' => $page > 1 ? $page - 1 : null,
            'next_page' => $page < ceil($totalGrowthLogs / self::ITEMS_PER_PAGE) ? $page + 1 : null
        ];

        return view('user.fish-growth.index', compact(
            'growthLogs',
            'branchInfo',
            'stats',
            'fishBatches',
            'pagination'
        ));
    }

    private function getGrowthLogsQuery($branchId)
    {
        return DB::table('fish_growth_logs as fgl')
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
                'fb.date_start as batch_date_start',
                'fb.documentation_file as batch_image',
                'p.name as pond_name',
                'p.code as pond_code',
                'p.documentation_file as pond_image',
                'ft.name as fish_type_name',
                'u.full_name as created_by_name'
            )
            ->orderBy('fgl.date_recorded', 'desc')
            ->orderBy('fgl.week_number', 'desc');
    }

    private function processGrowthLogsData($growthLogs)
    {
        foreach ($growthLogs as $log) {
            // Add image URLs
            $log->batch_image_url = $log->batch_image
                ? Storage::url($log->batch_image)
                : null;

            $log->pond_image_url = $log->pond_image
                ? Storage::url($log->pond_image)
                : null;

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
            $log->batch_age_days = \Carbon\Carbon::parse($log->batch_date_start)
                ->diffInDays($log->date_recorded);

            // Format for mobile display
            $log->formatted_date = \Carbon\Carbon::parse($log->date_recorded)->format('d M');
            $log->formatted_weight = number_format($log->avg_weight_gram, 1);
            $log->formatted_length = number_format($log->avg_length_cm, 1);
            $log->growth_status = $this->getGrowthStatus($log->weight_growth);
        }
    }

    private function getGrowthStatus($weightGrowth)
    {
        if ($weightGrowth > 0) {
            return ['status' => 'naik', 'icon' => 'fa-arrow-up', 'color' => 'green'];
        } elseif ($weightGrowth < 0) {
            return ['status' => 'turun', 'icon' => 'fa-arrow-down', 'color' => 'red'];
        } else {
            return ['status' => 'stabil', 'icon' => 'fa-minus', 'color' => 'gray'];
        }
    }

    private function calculateStats($branchId)
    {
        $statsQuery = DB::table('fish_growth_logs as fgl')
            ->join('fish_batches as fb', 'fgl.fish_batch_id', '=', 'fb.id')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->where('p.branch_id', $branchId)
            ->whereNull('fgl.deleted_at')
            ->whereNull('fb.deleted_at');

        $totalRecords = $statsQuery->count();
        $avgWeight = $statsQuery->avg('fgl.avg_weight_gram') ?: 0;
        $avgLength = $statsQuery->avg('fgl.avg_length_cm') ?: 0;
        $activeBatches = $statsQuery->distinct('fb.id')->count();

        return [
            'total_records' => $totalRecords,
            'avg_weight' => $avgWeight,
            'avg_length' => $avgLength,
            'active_batches' => $activeBatches
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
                'p.documentation_file as pond_documentation_file',
                'ft.name as fish_type_name'
            )
            ->orderBy('fb.created_at', 'desc')
            ->get();

        // Add image URLs and calculate current stock for each batch
        foreach ($fishBatches as $batch) {
            // Add image URLs
            $batch->image_url = $batch->documentation_file
                ? Storage::url($batch->documentation_file)
                : null;

            $batch->pond_image_url = $batch->pond_documentation_file
                ? Storage::url($batch->pond_documentation_file)
                : null;

            // Calculate current stock
            $batch->current_stock = $this->calculateCurrentStock($batch->id);

            // Calculate age in days
            $batch->age_days = \Carbon\Carbon::parse($batch->date_start)->diffInDays(now());
            $batch->age_weeks = floor($batch->age_days / 7);

            // Only show active batches (with stock > 0)
            $batch->is_active = $batch->current_stock > 0;
        }

        return $fishBatches->where('is_active', true)->values();
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
            // Verify batch belongs to current branch and is active
            $batch = $this->validateBatch($request->fish_batch_id);
            if (!$batch) {
                return response()->json([
                    'success' => false,
                    'message' => 'Batch ikan tidak valid'
                ], 400);
            }

            // Check if batch has stock
            $currentStock = $this->calculateCurrentStock($batch->id);
            if ($currentStock <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Batch tidak memiliki stok ikan'
                ], 400);
            }

            // Check if record for this batch and week already exists
            $exists = DB::table('fish_growth_logs')
                ->where('fish_batch_id', $request->fish_batch_id)
                ->where('week_number', $request->week_number)
                ->whereNull('deleted_at')
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pertumbuhan untuk minggu ini sudah ada'
                ], 400);
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
            \Log::error('Growth log store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan data pertumbuhan. Silakan coba lagi.'
            ], 500);
        }
    }

    public function show($id)
    {
        $growthLog = $this->findGrowthLog($id);

        if (!$growthLog) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
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
            // Verify growth log exists
            $growthLog = $this->findGrowthLog($id);
            if (!$growthLog) {
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

            // Check if record for this batch and week already exists (excluding current record)
            $exists = DB::table('fish_growth_logs')
                ->where('fish_batch_id', $request->fish_batch_id)
                ->where('week_number', $request->week_number)
                ->where('id', '!=', $id)
                ->whereNull('deleted_at')
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pertumbuhan untuk minggu ini sudah ada'
                ], 400);
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
            \Log::error('Growth log update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data pertumbuhan. Silakan coba lagi.'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $growthLog = $this->findGrowthLog($id);
            if (!$growthLog) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
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
            \Log::error('Growth log delete error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data pertumbuhan. Silakan coba lagi.'
            ], 500);
        }
    }

    public function getBatchInfo($batchId)
    {
        try {
            $batch = DB::table('fish_batches as fb')
                ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
                ->join('fish_types as ft', 'fb.fish_type_id', '=', 'ft.id')
                ->where('fb.id', $batchId)
                ->where('p.branch_id', $this->getCurrentBranchId())
                ->whereNull('fb.deleted_at')
                ->select(
                    'fb.id',
                    'fb.date_start',
                    'fb.initial_count',
                    'p.name as pond_name',
                    'ft.name as fish_type_name'
                )
                ->first();

            if (!$batch) {
                return response()->json([
                    'success' => false,
                    'message' => 'Batch tidak ditemukan'
                ], 404);
            }

            $currentStock = $this->calculateCurrentStock($batchId);
            $ageDays = \Carbon\Carbon::parse($batch->date_start)->diffInDays(now());
            $ageWeeks = floor($ageDays / 7);

            // Get latest growth record
            $latestGrowth = DB::table('fish_growth_logs')
                ->where('fish_batch_id', $batchId)
                ->whereNull('deleted_at')
                ->orderBy('week_number', 'desc')
                ->first();

            return response()->json([
                'success' => true,
                'current_stock' => $currentStock,
                'age_days' => $ageDays,
                'age_weeks' => $ageWeeks,
                'suggested_week' => $ageWeeks > 0 ? $ageWeeks : 1,
                'latest_growth' => $latestGrowth ? [
                    'week_number' => $latestGrowth->week_number,
                    'avg_weight_gram' => $latestGrowth->avg_weight_gram,
                    'avg_length_cm' => $latestGrowth->avg_length_cm
                ] : null
            ]);
        } catch (\Exception $e) {
            \Log::error('Get batch info error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data batch'
            ], 500);
        }
    }

    private function findGrowthLog($id)
    {
        return DB::table('fish_growth_logs as fgl')
            ->join('fish_batches as fb', 'fgl.fish_batch_id', '=', 'fb.id')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->where('fgl.id', $id)
            ->where('p.branch_id', $this->getCurrentBranchId())
            ->whereNull('fgl.deleted_at')
            ->select('fgl.*')
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

        // Calculate sold fish
        $sold = DB::table('sales')
            ->where('fish_batch_id', $batchId)
            ->whereNull('deleted_at')
            ->sum('quantity_fish');

        // Calculate mortality
        $mortality = DB::table('mortalities')
            ->where('fish_batch_id', $batchId)
            ->whereNull('deleted_at')
            ->sum('dead_count');

        // Calculate transferred OUT (fish moved from this batch to other batches)
        $transferredOut = DB::table('fish_batch_transfers')
            ->where('source_batch_id', $batchId)
            ->whereNull('deleted_at')
            ->sum('transferred_count');

        // Calculate transferred IN (fish moved from other batches to this batch)
        $transferredIn = DB::table('fish_batch_transfers')
            ->where('target_batch_id', $batchId)
            ->whereNull('deleted_at')
            ->sum('transferred_count');

        // Current stock = initial + transferred_in - sold - mortality - transferred_out
        return $batch->initial_count + $transferredIn - $sold - $mortality - $transferredOut;
    }
}
