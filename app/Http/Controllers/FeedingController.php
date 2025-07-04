<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FeedingController extends Controller
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
        $totalFeedings = DB::table('feedings as f')
            ->join('fish_batches as fb', 'f.fish_batch_id', '=', 'fb.id')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->where('p.branch_id', $branchId)
            ->whereNull('f.deleted_at')
            ->whereNull('fb.deleted_at')
            ->count();

        // Get feedings with pagination - optimized query
        $feedings = $this->getFeedingsQuery($branchId)
            ->limit(self::ITEMS_PER_PAGE)
            ->offset($offset)
            ->get();

        // Process feedings data
        $this->processFeedingsData($feedings);

        // Calculate stats
        $stats = $this->calculateStats($branchId);

        // Get active fish batches for form
        $fishBatches = $this->getActiveFishBatches($branchId);

        // Pagination info
        $pagination = [
            'current_page' => $page,
            'total_pages' => ceil($totalFeedings / self::ITEMS_PER_PAGE),
            'total_items' => $totalFeedings,
            'per_page' => self::ITEMS_PER_PAGE,
            'has_prev' => $page > 1,
            'has_next' => $page < ceil($totalFeedings / self::ITEMS_PER_PAGE),
            'prev_page' => $page > 1 ? $page - 1 : null,
            'next_page' => $page < ceil($totalFeedings / self::ITEMS_PER_PAGE) ? $page + 1 : null
        ];

        return view('user.feedings.index', compact(
            'feedings',
            'branchInfo',
            'stats',
            'fishBatches',
            'pagination'
        ));
    }

    private function getFeedingsQuery($branchId)
    {
        return DB::table('feedings as f')
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
                'fb.date_start as batch_date_start',
                'fb.documentation_file as batch_image',
                'p.name as pond_name',
                'p.code as pond_code',
                'ft.name as fish_type_name',
                'u.full_name as created_by_name'
            )
            ->orderBy('f.date', 'desc')
            ->orderBy('f.created_at', 'desc');
    }

    private function processFeedingsData($feedings)
    {
        foreach ($feedings as $feeding) {
            // Add image URLs
            $feeding->batch_image_url = $feeding->batch_image
                ? Storage::url($feeding->batch_image)
                : null;

            // Calculate batch age at feeding date
            $feeding->batch_age_days = \Carbon\Carbon::parse($feeding->batch_date_start)
                ->diffInDays($feeding->date);

            // Format for mobile display
            $feeding->formatted_date = \Carbon\Carbon::parse($feeding->date)->format('d M');
            $feeding->formatted_amount = number_format($feeding->feed_amount_kg, 1);
            $feeding->short_notes = $feeding->notes ? \Str::limit($feeding->notes, 20) : null;
        }
    }

    private function calculateStats($branchId)
    {
        $statsQuery = DB::table('feedings as f')
            ->join('fish_batches as fb', 'f.fish_batch_id', '=', 'fb.id')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->where('p.branch_id', $branchId)
            ->whereNull('f.deleted_at')
            ->whereNull('fb.deleted_at');

        $totalFeedings = $statsQuery->count();
        $totalFeedKg = $statsQuery->sum('f.feed_amount_kg');
        $activeBatches = $statsQuery->distinct('fb.id')->count();

        // Calculate average per day for last 30 days
        $avgFeedPerDay = DB::table('feedings as f')
            ->join('fish_batches as fb', 'f.fish_batch_id', '=', 'fb.id')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->where('p.branch_id', $branchId)
            ->where('f.date', '>=', now()->subDays(30))
            ->whereNull('f.deleted_at')
            ->whereNull('fb.deleted_at')
            ->selectRaw('DATE(f.date) as feed_date, SUM(f.feed_amount_kg) as daily_total')
            ->groupBy('feed_date')
            ->get()
            ->avg('daily_total') ?: 0;

        return [
            'total_feedings' => $totalFeedings,
            'total_feed_kg' => $totalFeedKg,
            'avg_feed_per_day' => $avgFeedPerDay,
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
            'feed_type' => 'required|string|max:100',
            'feed_amount_kg' => 'required|numeric|min:0.1',
            'notes' => 'nullable|string|max:500'
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
            \Log::error('Feeding store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan data pemberian pakan. Silakan coba lagi.'
            ], 500);
        }
    }

    public function show($id)
    {
        $feeding = $this->findFeeding($id);

        if (!$feeding) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
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
            // Verify feeding exists
            $feeding = $this->findFeeding($id);
            if (!$feeding) {
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
            \Log::error('Feeding update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data pemberian pakan. Silakan coba lagi.'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $feeding = $this->findFeeding($id);
            if (!$feeding) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

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
            \Log::error('Feeding delete error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data pemberian pakan. Silakan coba lagi.'
            ], 500);
        }
    }

    private function findFeeding($id)
    {
        return DB::table('feedings as f')
            ->join('fish_batches as fb', 'f.fish_batch_id', '=', 'fb.id')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->where('f.id', $id)
            ->where('p.branch_id', $this->getCurrentBranchId())
            ->whereNull('f.deleted_at')
            ->select('f.*')
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
}
