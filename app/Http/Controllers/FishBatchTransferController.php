<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FishBatchTransferController extends Controller
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
        $totalTransfers = DB::table('fish_batch_transfers as fbt')
            ->join('fish_batches as fb_source', 'fbt.source_batch_id', '=', 'fb_source.id')
            ->join('fish_batches as fb_target', 'fbt.target_batch_id', '=', 'fb_target.id')
            ->join('ponds as p_source', 'fb_source.pond_id', '=', 'p_source.id')
            ->join('ponds as p_target', 'fb_target.pond_id', '=', 'p_target.id')
            ->where('p_source.branch_id', $branchId)
            ->where('p_target.branch_id', $branchId)
            ->whereNull('fbt.deleted_at')
            ->whereNull('fb_source.deleted_at')
            ->whereNull('fb_target.deleted_at')
            ->count();

        // Get transfers with pagination
        $transfers = $this->getTransfersQuery($branchId)
            ->limit(self::ITEMS_PER_PAGE)
            ->offset($offset)
            ->get();

        // Process transfers data
        $this->processTransfersData($transfers);

        // Calculate stats
        $stats = $this->calculateStats($branchId);

        // Get active fish batches for form
        $activeBatches = $this->getActiveFishBatches($branchId);

        // Pagination info
        $pagination = [
            'current_page' => $page,
            'total_pages' => ceil($totalTransfers / self::ITEMS_PER_PAGE),
            'total_items' => $totalTransfers,
            'per_page' => self::ITEMS_PER_PAGE,
            'has_prev' => $page > 1,
            'has_next' => $page < ceil($totalTransfers / self::ITEMS_PER_PAGE),
            'prev_page' => $page > 1 ? $page - 1 : null,
            'next_page' => $page < ceil($totalTransfers / self::ITEMS_PER_PAGE) ? $page + 1 : null
        ];

        return view('user.fish-transfers.index', compact(
            'transfers',
            'branchInfo',
            'stats',
            'activeBatches',
            'pagination'
        ));
    }

    private function getTransfersQuery($branchId)
    {
        return DB::table('fish_batch_transfers as fbt')
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
                'fb_source.documentation_file as source_batch_image',
                'fb_target.id as target_batch_id',
                'fb_target.documentation_file as target_batch_image',
                'p_source.name as source_pond_name',
                'p_source.code as source_pond_code',
                'p_target.name as target_pond_name',
                'p_target.code as target_pond_code',
                'ft_source.name as source_fish_type',
                'ft_target.name as target_fish_type',
                'u.full_name as created_by_name'
            )
            ->orderBy('fbt.date_transfer', 'desc')
            ->orderBy('fbt.created_at', 'desc');
    }

    private function processTransfersData($transfers)
    {
        foreach ($transfers as $transfer) {
            // Add image URLs
            $transfer->source_batch_image_url = $transfer->source_batch_image
                ? Storage::url($transfer->source_batch_image)
                : null;

            $transfer->target_batch_image_url = $transfer->target_batch_image
                ? Storage::url($transfer->target_batch_image)
                : null;

            // Calculate current stock for both batches
            $transfer->source_current_stock = $this->calculateCurrentStock($transfer->source_batch_id);
            $transfer->target_current_stock = $this->calculateCurrentStock($transfer->target_batch_id);

            // Format for mobile display
            $transfer->formatted_date = \Carbon\Carbon::parse($transfer->date_transfer)->format('d M');
            $transfer->formatted_count = number_format($transfer->transferred_count);
            $transfer->short_notes = $transfer->notes ? \Str::limit($transfer->notes, 20) : null;
        }
    }

    private function calculateStats($branchId)
    {
        $statsQuery = DB::table('fish_batch_transfers as fbt')
            ->join('fish_batches as fb_source', 'fbt.source_batch_id', '=', 'fb_source.id')
            ->join('fish_batches as fb_target', 'fbt.target_batch_id', '=', 'fb_target.id')
            ->join('ponds as p_source', 'fb_source.pond_id', '=', 'p_source.id')
            ->join('ponds as p_target', 'fb_target.pond_id', '=', 'p_target.id')
            ->where('p_source.branch_id', $branchId)
            ->where('p_target.branch_id', $branchId)
            ->whereNull('fbt.deleted_at')
            ->whereNull('fb_source.deleted_at')
            ->whereNull('fb_target.deleted_at');

        $totalTransfers = $statsQuery->count();
        $totalFishTransferred = $statsQuery->sum('fbt.transferred_count');
        $thisMonthTransfers = $statsQuery->where('fbt.date_transfer', '>=', now()->startOfMonth())->count();

        $activeBatches = DB::table('fish_batches as fb')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->where('p.branch_id', $branchId)
            ->whereNull('fb.deleted_at')
            ->count();

        return [
            'total_transfers' => $totalTransfers,
            'total_fish_transferred' => $totalFishTransferred,
            'this_month_transfers' => $thisMonthTransfers,
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
            'source_batch_id' => 'required|exists:fish_batches,id|different:target_batch_id',
            'target_batch_id' => 'required|exists:fish_batches,id',
            'transferred_count' => 'required|integer|min:1',
            'date_transfer' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            // Verify both batches belong to current branch
            $sourceBatch = $this->validateBatch($request->source_batch_id);
            $targetBatch = $this->validateBatch($request->target_batch_id);

            if (!$sourceBatch || !$targetBatch) {
                return response()->json([
                    'success' => false,
                    'message' => 'Batch tidak valid'
                ], 400);
            }

            // Calculate source batch current stock
            $currentStock = $this->calculateCurrentStock($request->source_batch_id);

            if ($currentStock < $request->transferred_count) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stok tidak mencukupi. Stok tersedia: ' . number_format($currentStock)
                ], 400);
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
            \Log::error('Transfer store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan transfer batch. Silakan coba lagi.'
            ], 500);
        }
    }

    public function show($id)
    {
        $transfer = $this->findTransfer($id);

        if (!$transfer) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
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
            // Verify transfer exists
            $transfer = $this->findTransfer($id);
            if (!$transfer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            // Verify new batches
            $sourceBatch = $this->validateBatch($request->source_batch_id);
            $targetBatch = $this->validateBatch($request->target_batch_id);

            if (!$sourceBatch || !$targetBatch) {
                return response()->json([
                    'success' => false,
                    'message' => 'Batch tidak valid'
                ], 400);
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
            \Log::error('Transfer update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui transfer batch. Silakan coba lagi.'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $transfer = $this->findTransfer($id);
            if (!$transfer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

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
            \Log::error('Transfer delete error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus transfer batch. Silakan coba lagi.'
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
            \Log::error('Get batch stock error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data stok'
            ], 500);
        }
    }

    private function findTransfer($id)
    {
        return DB::table('fish_batch_transfers as fbt')
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
