<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MortalityController extends Controller
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
        $totalMortalities = DB::table('mortalities as m')
            ->join('fish_batches as fb', 'm.fish_batch_id', '=', 'fb.id')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->where('p.branch_id', $branchId)
            ->whereNull('m.deleted_at')
            ->whereNull('fb.deleted_at')
            ->count();

        // Get mortalities with pagination - optimized query
        $mortalities = $this->getMortalitiesQuery($branchId)
            ->limit(self::ITEMS_PER_PAGE)
            ->offset($offset)
            ->get();

        // Process mortalities data
        $this->processMortalitiesData($mortalities);

        // Calculate stats
        $stats = $this->calculateStats($branchId);

        // Get active fish batches for form
        $fishBatches = $this->getActiveFishBatches($branchId);

        // Pagination info
        $pagination = [
            'current_page' => $page,
            'total_pages' => ceil($totalMortalities / self::ITEMS_PER_PAGE),
            'total_items' => $totalMortalities,
            'per_page' => self::ITEMS_PER_PAGE,
            'has_prev' => $page > 1,
            'has_next' => $page < ceil($totalMortalities / self::ITEMS_PER_PAGE),
            'prev_page' => $page > 1 ? $page - 1 : null,
            'next_page' => $page < ceil($totalMortalities / self::ITEMS_PER_PAGE) ? $page + 1 : null
        ];

        return view('user.mortalities.index', compact(
            'mortalities',
            'branchInfo',
            'stats',
            'fishBatches',
            'pagination'
        ));
    }

    private function getMortalitiesQuery($branchId)
    {
        return DB::table('mortalities as m')
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
                'fb.documentation_file as batch_image',
                'p.name as pond_name',
                'p.code as pond_code',
                'ft.name as fish_type_name',
                'u.full_name as created_by_name'
            )
            ->orderBy('m.date', 'desc')
            ->orderBy('m.created_at', 'desc');
    }

    private function processMortalitiesData($mortalities)
    {
        foreach ($mortalities as $mortality) {
            // Add image URLs
            $mortality->batch_image_url = $mortality->batch_image
                ? Storage::url($mortality->batch_image)
                : null;

            // Calculate batch age at mortality date
            $mortality->batch_age_days = \Carbon\Carbon::parse($mortality->batch_start)
                ->diffInDays($mortality->date);

            // Get stock at the time of mortality (before this mortality event)
            $stockBeforeMortality = $this->calculateCurrentStock($mortality->batch_id, $mortality->date);

            // Add back this mortality to get stock before this specific mortality
            $mortality->stock_before_mortality = $stockBeforeMortality + $mortality->dead_count;

            // Current stock after all events
            $mortality->current_stock = $this->calculateCurrentStock($mortality->batch_id);

            // Calculate mortality rate based on stock before this mortality
            $mortality->mortality_rate = $mortality->stock_before_mortality > 0 ?
                round(($mortality->dead_count / $mortality->stock_before_mortality) * 100, 2) : 0;

            // Format for mobile display
            $mortality->formatted_date = \Carbon\Carbon::parse($mortality->date)->format('d M');
            $mortality->formatted_dead_count = number_format($mortality->dead_count);
            $mortality->short_cause = $mortality->cause ? \Str::limit($mortality->cause, 25) : null;
        }
    }

    private function calculateStats($branchId)
    {
        $statsQuery = DB::table('mortalities as m')
            ->join('fish_batches as fb', 'm.fish_batch_id', '=', 'fb.id')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->where('p.branch_id', $branchId)
            ->whereNull('m.deleted_at')
            ->whereNull('fb.deleted_at');

        $totalRecords = $statsQuery->count();
        $totalDeadFish = $statsQuery->sum('m.dead_count');
        $affectedBatches = $statsQuery->distinct('fb.id')->count();

        // Calculate average mortality rate for last 30 days
        $recentMortalities = DB::table('mortalities as m')
            ->join('fish_batches as fb', 'm.fish_batch_id', '=', 'fb.id')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->where('p.branch_id', $branchId)
            ->where('m.date', '>=', now()->subDays(30))
            ->whereNull('m.deleted_at')
            ->whereNull('fb.deleted_at')
            ->get();

        $avgMortalityRate = 0;
        if ($recentMortalities->count() > 0) {
            $totalRate = 0;
            foreach ($recentMortalities as $mortality) {
                $stockBefore = $this->calculateCurrentStock($mortality->fish_batch_id, $mortality->date) + $mortality->dead_count;
                if ($stockBefore > 0) {
                    $totalRate += ($mortality->dead_count / $stockBefore) * 100;
                }
            }
            $avgMortalityRate = $totalRate / $recentMortalities->count();
        }

        return [
            'total_records' => $totalRecords,
            'total_dead_fish' => $totalDeadFish,
            'avg_mortality_rate' => $avgMortalityRate,
            'affected_batches' => $affectedBatches
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

    private function calculateCurrentStock($batchId, $upToDate = null)
    {
        // Get initial count
        $initialCount = DB::table('fish_batches')->where('id', $batchId)->value('initial_count');

        // Calculate transferred IN
        $transferredInQuery = DB::table('fish_batch_transfers')
            ->where('target_batch_id', $batchId)
            ->whereNull('deleted_at');

        if ($upToDate) {
            $transferredInQuery->where('date_transfer', '<=', $upToDate);
        }
        $transferredIn = $transferredInQuery->sum('transferred_count');

        // Calculate transferred OUT
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

        // Calculate mortality
        $mortalityQuery = DB::table('mortalities')
            ->where('fish_batch_id', $batchId)
            ->whereNull('deleted_at');

        if ($upToDate) {
            $mortalityQuery->where('date', '<=', $upToDate);
        }
        $mortality = $mortalityQuery->sum('dead_count');

        return $initialCount + $transferredIn - $transferredOut - $sold - $mortality;
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
            // Verify batch belongs to current branch and is active
            $batch = $this->validateBatch($request->fish_batch_id);
            if (!$batch) {
                return response()->json([
                    'success' => false,
                    'message' => 'Batch ikan tidak valid'
                ], 400);
            }

            // Check if mortality date is after batch start date
            if ($request->date < $batch->date_start) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tanggal mortalitas tidak boleh sebelum tanggal mulai batch'
                ], 400);
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
            \Log::error('Mortality store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan data mortalitas. Silakan coba lagi.'
            ], 500);
        }
    }

    public function show($id)
    {
        $mortality = $this->findMortality($id);

        if (!$mortality) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
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
            // Verify mortality exists
            $existingMortality = $this->findMortality($id);
            if (!$existingMortality) {
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

            // Check if mortality date is after batch start date
            if ($request->date < $batch->date_start) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tanggal mortalitas tidak boleh sebelum tanggal mulai batch'
                ], 400);
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
            \Log::error('Mortality update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data mortalitas. Silakan coba lagi.'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $mortality = $this->findMortality($id);
            if (!$mortality) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

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
            \Log::error('Mortality delete error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data mortalitas. Silakan coba lagi.'
            ], 500);
        }
    }

    private function findMortality($id)
    {
        return DB::table('mortalities as m')
            ->join('fish_batches as fb', 'm.fish_batch_id', '=', 'fb.id')
            ->join('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->where('m.id', $id)
            ->where('p.branch_id', $this->getCurrentBranchId())
            ->whereNull('m.deleted_at')
            ->select('m.*', 'fb.date_start')
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
