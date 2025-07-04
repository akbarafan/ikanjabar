<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FishTypeController extends Controller
{
    private function getCurrentBranchId()
    {
        // Sementara hardcode untuk demo, nanti bisa dari session/auth
        return Auth::user()->branch_id;
    }

    public function index()
    {
        $branchId = $this->getCurrentBranchId();

        // Get branch info
        $branchInfo = DB::table('branches')->find($branchId);

        // Get fish types with stock summary - optimized query
        $fishTypes = DB::table('fish_types as ft')
            ->leftJoin('fish_batches as fb', function ($join) {
                $join->on('ft.id', '=', 'fb.fish_type_id')
                    ->whereNull('fb.deleted_at');
            })
            ->leftJoin('ponds as p', 'fb.pond_id', '=', 'p.id')
            ->where('ft.branch_id', $branchId)
            ->select(
                'ft.id',
                'ft.name',
                'ft.description',
                'ft.created_at',
                DB::raw('COUNT(DISTINCT p.id) as used_ponds'),
                DB::raw('COALESCE(SUM(fb.initial_count), 0) as total_initial_stock')
            )
            ->groupBy('ft.id', 'ft.name', 'ft.description', 'ft.created_at')
            ->orderBy('ft.created_at', 'desc')
            ->get();

        // Calculate current stock (initial - sold - mortality)
        foreach ($fishTypes as $fishType) {
            $sold = DB::table('sales as s')
                ->join('fish_batches as fb', 's.fish_batch_id', '=', 'fb.id')
                ->where('fb.fish_type_id', $fishType->id)
                ->whereNull('s.deleted_at')
                ->sum('s.quantity_fish');

            $mortality = DB::table('mortalities as m')
                ->join('fish_batches as fb', 'm.fish_batch_id', '=', 'fb.id')
                ->where('fb.fish_type_id', $fishType->id)
                ->whereNull('m.deleted_at')
                ->sum('m.dead_count');

            $fishType->current_stock = $fishType->total_initial_stock - $sold - $mortality;
        }

        // Summary stats
        $stats = [
            'total_types' => $fishTypes->count(),
            'total_current_stock' => $fishTypes->sum('current_stock'),
            'used_ponds' => $fishTypes->sum('used_ponds')
        ];

        return view('user.fish-types.index', compact('fishTypes', 'branchInfo', 'stats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:fish_types,name,NULL,id,branch_id,' . $this->getCurrentBranchId(),
            'description' => 'nullable|string|max:500'
        ]);

        try {
            DB::table('fish_types')->insert([
                'branch_id' => $this->getCurrentBranchId(),
                'name' => trim($request->name),
                'description' => $request->description ? trim($request->description) : null,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Jenis ikan berhasil ditambahkan!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan jenis ikan. Silakan coba lagi.'
            ], 500);
        }
    }

    public function show($id)
    {
        $fishType = DB::table('fish_types')
            ->where('id', $id)
            ->where('branch_id', $this->getCurrentBranchId())
            ->first();

        if (!$fishType) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json(['success' => true, 'data' => $fishType]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:fish_types,name,' . $id . ',id,branch_id,' . $this->getCurrentBranchId(),
            'description' => 'nullable|string|max:500'
        ]);

        try {
            $updated = DB::table('fish_types')
                ->where('id', $id)
                ->where('branch_id', $this->getCurrentBranchId())
                ->update([
                    'name' => trim($request->name),
                    'description' => $request->description ? trim($request->description) : null,
                    'updated_at' => now()
                ]);

            if (!$updated) {
                return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Jenis ikan berhasil diperbarui!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui jenis ikan. Silakan coba lagi.'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            // Check if fish type is being used
            $isUsed = DB::table('fish_batches')
                ->where('fish_type_id', $id)
                ->whereNull('deleted_at')
                ->exists();

            if ($isUsed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jenis ikan tidak dapat dihapus karena masih digunakan dalam batch aktif.'
                ], 400);
            }

            $deleted = DB::table('fish_types')
                ->where('id', $id)
                ->where('branch_id', $this->getCurrentBranchId())
                ->delete();

            if (!$deleted) {
                return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Jenis ikan berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus jenis ikan. Silakan coba lagi.'
            ], 500);
        }
    }
}
