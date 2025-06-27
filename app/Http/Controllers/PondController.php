<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PondController extends Controller
{
    private function getCurrentBranchId()
    {
        // Sementara hardcode untuk demo, nanti bisa dari session/auth
        return 1;
    }

    public function index()
    {
        $branchId = $this->getCurrentBranchId();

        // Get branch info
        $branchInfo = DB::table('branches')->find($branchId);

        // Get ponds with batch summary - optimized query
        $ponds = DB::table('ponds as p')
            ->leftJoin('fish_batches as fb', function ($join) {
                $join->on('p.id', '=', 'fb.pond_id')
                    ->whereNull('fb.deleted_at');
            })
            ->leftJoin('fish_types as ft', 'fb.fish_type_id', '=', 'ft.id')
            ->where('p.branch_id', $branchId)
            ->select(
                'p.id',
                'p.name',
                'p.code',
                'p.type',
                'p.volume_liters',
                'p.description',
                'p.created_at',
                DB::raw('COUNT(DISTINCT fb.id) as active_batches'),
                DB::raw('COALESCE(SUM(fb.initial_count), 0) as total_initial_stock')
            )
            ->groupBy('p.id', 'p.name', 'p.code', 'p.type', 'p.volume_liters', 'p.description', 'p.created_at')
            ->orderBy('p.created_at', 'desc')
            ->get();

        // Calculate current stock and get fish types for each pond
        foreach ($ponds as $pond) {
            // Calculate current stock (initial - sold - mortality)
            $sold = DB::table('sales as s')
                ->join('fish_batches as fb', 's.fish_batch_id', '=', 'fb.id')
                ->where('fb.pond_id', $pond->id)
                ->whereNull('s.deleted_at')
                ->sum('s.quantity_fish');

            $mortality = DB::table('mortalities as m')
                ->join('fish_batches as fb', 'm.fish_batch_id', '=', 'fb.id')
                ->where('fb.pond_id', $pond->id)
                ->whereNull('m.deleted_at')
                ->sum('m.dead_count');

            $pond->current_stock = $pond->total_initial_stock - $sold - $mortality;

            // Get fish types in this pond
            $fishTypes = DB::table('fish_batches as fb')
                ->join('fish_types as ft', 'fb.fish_type_id', '=', 'ft.id')
                ->where('fb.pond_id', $pond->id)
                ->whereNull('fb.deleted_at')
                ->distinct()
                ->pluck('ft.name')
                ->toArray();

            $pond->fish_types = $fishTypes;
            $pond->status = $pond->active_batches > 0 ? 'active' : 'empty';
        }

        // Summary stats
        $stats = [
            'total_ponds' => $ponds->count(),
            'active_ponds' => $ponds->where('status', 'active')->count(),
            'total_volume' => $ponds->sum('volume_liters'),
            'total_current_stock' => $ponds->sum('current_stock')
        ];

        return view('user.ponds.index', compact('ponds', 'branchInfo', 'stats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:ponds,code,NULL,id,branch_id,' . $this->getCurrentBranchId(),
            'type' => 'required|in:tanah,beton,viber,terpal',
            'volume_liters' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:500'
        ]);

        try {
            DB::table('ponds')->insert([
                'branch_id' => $this->getCurrentBranchId(),
                'name' => trim($request->name),
                'code' => trim($request->code),
                'type' => $request->type,
                'volume_liters' => $request->volume_liters,
                'description' => $request->description ? trim($request->description) : null,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Kolam berhasil ditambahkan!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan kolam. Silakan coba lagi.'
            ], 500);
        }
    }

    public function show($id)
    {
        $pond = DB::table('ponds')
            ->where('id', $id)
            ->where('branch_id', $this->getCurrentBranchId())
            ->first();

        if (!$pond) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json(['success' => true, 'data' => $pond]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:ponds,code,' . $id . ',id,branch_id,' . $this->getCurrentBranchId(),
            'type' => 'required|in:tanah,beton,viber,terpal',
            'volume_liters' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:500'
        ]);

        try {
            $updated = DB::table('ponds')
                ->where('id', $id)
                ->where('branch_id', $this->getCurrentBranchId())
                ->update([
                    'name' => trim($request->name),
                    'code' => trim($request->code),
                    'type' => $request->type,
                    'volume_liters' => $request->volume_liters,
                    'description' => $request->description ? trim($request->description) : null,
                    'updated_at' => now()
                ]);

            if (!$updated) {
                return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Kolam berhasil diperbarui!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui kolam. Silakan coba lagi.'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            // Check if pond is being used
            $isUsed = DB::table('fish_batches')
                ->where('pond_id', $id)
                ->whereNull('deleted_at')
                ->exists();

            if ($isUsed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kolam tidak dapat dihapus karena masih digunakan dalam batch aktif.'
                ], 400);
            }

            $deleted = DB::table('ponds')
                ->where('id', $id)
                ->where('branch_id', $this->getCurrentBranchId())
                ->delete();

            if (!$deleted) {
                return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Kolam berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus kolam. Silakan coba lagi.'
            ], 500);
        }
    }
}
