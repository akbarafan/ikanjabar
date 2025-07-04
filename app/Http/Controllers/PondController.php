<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PondController extends Controller
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
                'p.documentation_file',
                'p.created_at',
                DB::raw('COUNT(DISTINCT fb.id) as active_batches'),
                DB::raw('COALESCE(SUM(fb.initial_count), 0) as total_initial_stock')
            )
            ->groupBy('p.id', 'p.name', 'p.code', 'p.type', 'p.volume_liters', 'p.description', 'p.documentation_file', 'p.created_at')
            ->orderBy('p.created_at', 'desc')
            ->get();

        // Calculate current stock and get fish types for each pond
        foreach ($ponds as $pond) {
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

            // Add image URL
            $pond->image_url = $pond->documentation_file ? Storage::url($pond->documentation_file) : null;
        }

        // Summary stats
        $stats = [
            'total_ponds' => $ponds->count(),
            'active_ponds' => $ponds->where('status', 'active')->count(),
            'total_volume' => $ponds->sum('volume_liters'),
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
            'description' => 'nullable|string|max:500',
            'documentation_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            $documentationFile = null;

            // Handle file upload
            if ($request->hasFile('documentation_file')) {
                $file = $request->file('documentation_file');
                $filename = 'pond_' . time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $documentationFile = $file->storeAs('ponds', $filename, 'public');
            }

            DB::table('ponds')->insert([
                'branch_id' => $this->getCurrentBranchId(),
                'name' => trim($request->name),
                'code' => trim($request->code),
                'type' => $request->type,
                'volume_liters' => $request->volume_liters,
                'description' => $request->description ? trim($request->description) : null,
                'documentation_file' => $documentationFile,
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

        // Add image URL
        $pond->image_url = $pond->documentation_file ? Storage::url($pond->documentation_file) : null;

        return response()->json(['success' => true, 'data' => $pond]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:ponds,code,' . $id . ',id,branch_id,' . $this->getCurrentBranchId(),
            'type' => 'required|in:tanah,beton,viber,terpal',
            'volume_liters' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:500',
            'documentation_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            // Get current pond data
            $currentPond = DB::table('ponds')
                ->where('id', $id)
                ->where('branch_id', $this->getCurrentBranchId())
                ->first();

            if (!$currentPond) {
                return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
            }

            $documentationFile = $currentPond->documentation_file;

            // Handle file upload
            if ($request->hasFile('documentation_file')) {
                // Delete old file if exists
                if ($currentPond->documentation_file) {
                    Storage::disk('public')->delete($currentPond->documentation_file);
                }

                $file = $request->file('documentation_file');
                $filename = 'pond_' . time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $documentationFile = $file->storeAs('ponds', $filename, 'public');
            }

            $updated = DB::table('ponds')
                ->where('id', $id)
                ->where('branch_id', $this->getCurrentBranchId())
                ->update([
                    'name' => trim($request->name),
                    'code' => trim($request->code),
                    'type' => $request->type,
                    'volume_liters' => $request->volume_liters,
                    'description' => $request->description ? trim($request->description) : null,
                    'documentation_file' => $documentationFile,
                    'updated_at' => now()
                ]);

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

            // Get pond data to delete file
            $pond = DB::table('ponds')
                ->where('id', $id)
                ->where('branch_id', $this->getCurrentBranchId())
                ->first();

            if (!$pond) {
                return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
            }

            // Delete file if exists
            if ($pond->documentation_file) {
                Storage::disk('public')->delete($pond->documentation_file);
            }

            $deleted = DB::table('ponds')
                ->where('id', $id)
                ->where('branch_id', $this->getCurrentBranchId())
                ->delete();

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
