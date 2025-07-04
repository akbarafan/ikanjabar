<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FishBatchController extends Controller
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
        $totalBatches = DB::table('fish_batches as fb')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->where('p.branch_id', $branchId)
            ->whereNull('fb.deleted_at')
            ->count();

        // Get fish batches with pagination - optimized query
        $fishBatches = $this->getFishBatchesQuery($branchId)
            ->limit(self::ITEMS_PER_PAGE)
            ->offset($offset)
            ->get();

        // Process fish batches data
        $this->processFishBatchesData($fishBatches);

        // Calculate stats
        $stats = $this->calculateStats($branchId);

        // Get dropdown data for form
        $ponds = $this->getAvailablePonds($branchId);
        $fishTypes = $this->getAvailableFishTypes($branchId);

        // Pagination info
        $pagination = [
            'current_page' => $page,
            'total_pages' => ceil($totalBatches / self::ITEMS_PER_PAGE),
            'total_items' => $totalBatches,
            'per_page' => self::ITEMS_PER_PAGE,
            'has_prev' => $page > 1,
            'has_next' => $page < ceil($totalBatches / self::ITEMS_PER_PAGE),
            'prev_page' => $page > 1 ? $page - 1 : null,
            'next_page' => $page < ceil($totalBatches / self::ITEMS_PER_PAGE) ? $page + 1 : null
        ];

        return view('user.fish-batches.index', compact(
            'fishBatches',
            'branchInfo',
            'stats',
            'ponds',
            'fishTypes',
            'pagination'
        ));
    }

    private function getFishBatchesQuery($branchId)
    {
        return DB::table('fish_batches as fb')
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
                'fb.documentation_file',
                'fb.created_at',
                'p.name as pond_name',
                'p.code as pond_code',
                'p.documentation_file as pond_documentation_file',
                'ft.name as fish_type_name',
                'u.full_name as created_by_name'
            )
            ->orderBy('fb.created_at', 'desc');
    }

    private function processFishBatchesData($fishBatches)
    {
        foreach ($fishBatches as $batch) {
            // Add image URLs
            $batch->image_url = $batch->documentation_file
                ? Storage::url($batch->documentation_file)
                : null;

            $batch->pond_image_url = $batch->pond_documentation_file
                ? Storage::url($batch->pond_documentation_file)
                : null;

            // Calculate stock data
            $stockData = $this->calculateStockData($batch->id);
            $batch->current_stock = $stockData['current_stock'];
            $batch->transferred_in = $stockData['transferred_in'];
            $batch->transferred_out = $stockData['transferred_out'];
            $batch->sold = $stockData['sold'];
            $batch->mortality = $stockData['mortality'];

            // Calculate age
            $batch->age_days = \Carbon\Carbon::parse($batch->date_start)->diffInDays(now());
            $batch->age_weeks = floor($batch->age_days / 7);

            // Determine status
            $batch->status = $this->determineStatus($batch->current_stock, $batch->age_days);

            // Format for mobile display
            $batch->formatted_date = \Carbon\Carbon::parse($batch->date_start)->format('d M');
            $batch->formatted_stock = number_format($batch->current_stock);
            $batch->short_notes = $batch->notes ? \Str::limit($batch->notes, 25) : null;
        }
    }

    private function calculateStockData($batchId)
    {
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

        $batch = DB::table('fish_batches')->where('id', $batchId)->first();
        $initialCount = $batch ? $batch->initial_count : 0;

        return [
            'current_stock' => $initialCount + $transferredIn - $sold - $mortality - $transferredOut,
            'transferred_in' => $transferredIn,
            'transferred_out' => $transferredOut,
            'sold' => $sold,
            'mortality' => $mortality
        ];
    }

    private function determineStatus($currentStock, $ageDays)
    {
        if ($currentStock <= 0) {
            return 'finished';
        } elseif ($ageDays < 30) {
            return 'new';
        } elseif ($ageDays < 90) {
            return 'growing';
        } else {
            return 'mature';
        }
    }

    private function calculateStats($branchId)
    {
        $allBatches = DB::table('fish_batches as fb')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->where('p.branch_id', $branchId)
            ->whereNull('fb.deleted_at')
            ->select('fb.id', 'fb.date_start', 'fb.initial_count')
            ->get();

        $totalBatches = $allBatches->count();
        $activeBatches = 0;
        $totalCurrentStock = 0;
        $totalTransferred = 0;
        $totalAgeDays = 0;

        foreach ($allBatches as $batch) {
            $stockData = $this->calculateStockData($batch->id);
            $currentStock = $stockData['current_stock'];

            if ($currentStock > 0) {
                $activeBatches++;
                $totalCurrentStock += $currentStock;
                $totalAgeDays += \Carbon\Carbon::parse($batch->date_start)->diffInDays(now());
            }

            $totalTransferred += $stockData['transferred_out'];
        }

        return [
            'total_batches' => $totalBatches,
            'active_batches' => $activeBatches,
            'total_current_stock' => $totalCurrentStock,
            'avg_age_days' => $activeBatches > 0 ? round($totalAgeDays / $activeBatches) : 0,
            'total_transferred' => $totalTransferred
        ];
    }

    private function getAvailablePonds($branchId)
    {
        return DB::table('ponds')
            ->where('branch_id', $branchId)
            ->select('id', 'name', 'code', 'type', 'volume_liters', 'documentation_file')
            ->get()
            ->map(function ($pond) {
                $pond->image_url = $pond->documentation_file
                    ? Storage::url($pond->documentation_file)
                    : null;
                return $pond;
            });
    }

    private function getAvailableFishTypes($branchId)
    {
        return DB::table('fish_types')
            ->where('branch_id', $branchId)
            ->select('id', 'name')
            ->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'pond_id' => 'required|exists:ponds,id',
            'fish_type_id' => 'required|exists:fish_types,id',
            'date_start' => 'required|date|before_or_equal:today',
            'initial_count' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
            'documentation_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            // Verify pond belongs to current branch
            $pond = $this->validatePond($request->pond_id);
            if (!$pond) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kolam tidak valid'
                ], 400);
            }

            // Verify fish type belongs to current branch
            $fishType = $this->validateFishType($request->fish_type_id);
            if (!$fishType) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jenis ikan tidak valid'
                ], 400);
            }

            $documentationFile = null;

            // Handle file upload
            if ($request->hasFile('documentation_file')) {
                $file = $request->file('documentation_file');
                $filename = 'fish_batch_' . time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $documentationFile = $file->storeAs('fish-batches', $filename, 'public');
            }

            DB::table('fish_batches')->insert([
                'pond_id' => $request->pond_id,
                'fish_type_id' => $request->fish_type_id,
                'date_start' => $request->date_start,
                'initial_count' => $request->initial_count,
                'notes' => $request->notes ? trim($request->notes) : null,
                'documentation_file' => $documentationFile,
                'created_by' => $this->getCurrentUserId(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Batch ikan berhasil ditambahkan!'
            ]);
        } catch (\Exception $e) {
            \Log::error('Fish batch store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan batch ikan. Silakan coba lagi.'
            ], 500);
        }
    }

    public function show($id)
    {
        $fishBatch = $this->findFishBatch($id);

        if (!$fishBatch) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        // Add image URL
        $fishBatch->image_url = $fishBatch->documentation_file
            ? Storage::url($fishBatch->documentation_file)
            : null;

        return response()->json(['success' => true, 'data' => $fishBatch]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'pond_id' => 'required|exists:ponds,id',
            'fish_type_id' => 'required|exists:fish_types,id',
            'date_start' => 'required|date|before_or_equal:today',
            'initial_count' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
            'documentation_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            // Verify batch belongs to current branch
            $batch = $this->findFishBatch($id);
            if (!$batch) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            // Verify new pond belongs to current branch
            $pond = $this->validatePond($request->pond_id);
            if (!$pond) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kolam tidak valid'
                ], 400);
            }

            // Verify fish type belongs to current branch
            $fishType = $this->validateFishType($request->fish_type_id);
            if (!$fishType) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jenis ikan tidak valid'
                ], 400);
            }

            $documentationFile = $batch->documentation_file;

            // Handle file upload
            if ($request->hasFile('documentation_file')) {
                // Delete old file if exists
                if ($batch->documentation_file) {
                    Storage::disk('public')->delete($batch->documentation_file);
                }

                // Upload new file
                $file = $request->file('documentation_file');
                $filename = 'fish_batch_' . time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $documentationFile = $file->storeAs('fish-batches', $filename, 'public');
            }

            DB::table('fish_batches')
                ->where('id', $id)
                ->update([
                    'pond_id' => $request->pond_id,
                    'fish_type_id' => $request->fish_type_id,
                    'date_start' => $request->date_start,
                    'initial_count' => $request->initial_count,
                    'notes' => $request->notes ? trim($request->notes) : null,
                    'documentation_file' => $documentationFile,
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Batch ikan berhasil diperbarui!'
            ]);
        } catch (\Exception $e) {
            \Log::error('Fish batch update error: ' . $e->getMessage());
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
            $batch = $this->findFishBatch($id);
            if (!$batch) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            // Check if batch has related data
            $hasRelatedData = $this->checkRelatedData($id);
            if ($hasRelatedData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Batch tidak dapat dihapus karena memiliki data terkait (penjualan, mortalitas, transfer, atau pemberian pakan).'
                ], 400);
            }

            // Delete file if exists
            if ($batch->documentation_file) {
                Storage::disk('public')->delete($batch->documentation_file);
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
            \Log::error('Fish batch delete error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus batch ikan. Silakan coba lagi.'
            ], 500);
        }
    }

    private function findFishBatch($id)
    {
        return DB::table('fish_batches as fb')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->join('fish_types as ft', 'fb.fish_type_id', '=', 'ft.id')
            ->where('fb.id', $id)
            ->where('p.branch_id', $this->getCurrentBranchId())
            ->whereNull('fb.deleted_at')
            ->select('fb.*', 'p.name as pond_name', 'ft.name as fish_type_name')
            ->first();
    }

    private function validatePond($pondId)
    {
        return DB::table('ponds')
            ->where('id', $pondId)
            ->where('branch_id', $this->getCurrentBranchId())
            ->first();
    }

    private function validateFishType($fishTypeId)
    {
        return DB::table('fish_types')
            ->where('id', $fishTypeId)
            ->where('branch_id', $this->getCurrentBranchId())
            ->first();
    }

    private function checkRelatedData($batchId)
    {
        return DB::table('sales')->where('fish_batch_id', $batchId)->whereNull('deleted_at')->exists() ||
            DB::table('mortalities')->where('fish_batch_id', $batchId)->whereNull('deleted_at')->exists() ||
            DB::table('feedings')->where('fish_batch_id', $batchId)->whereNull('deleted_at')->exists() ||
            DB::table('fish_batch_transfers')->where('source_batch_id', $batchId)->whereNull('deleted_at')->exists() ||
            DB::table('fish_batch_transfers')->where('target_batch_id', $batchId)->whereNull('deleted_at')->exists();
    }
}
