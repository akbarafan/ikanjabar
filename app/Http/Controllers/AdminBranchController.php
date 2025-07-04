<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class AdminBranchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $branches = Branch::withCount(['users', 'ponds', 'fishTypes'])
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'total_branches' => Branch::count(),
            'total_users' => \App\Models\User::count(),
            'total_ponds' => \App\Models\Pond::count(),
            'active_branches' => Branch::whereHas('users')->count()
        ];

        return view('admin.branches.index', compact('branches', 'stats'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100|unique:branches,name',
                'location' => 'required|string',
                'contact_person' => 'required|string|max:100',
                'pic_name' => 'required|string|max:100'
            ], [
                'name.required' => 'Nama cabang wajib diisi',
                'name.unique' => 'Nama cabang sudah digunakan',
                'name.max' => 'Nama cabang maksimal 100 karakter',
                'location.required' => 'Lokasi cabang wajib diisi',
                'contact_person.required' => 'Kontak person wajib diisi',
                'contact_person.max' => 'Kontak person maksimal 100 karakter',
                'pic_name.required' => 'Nama PIC wajib diisi',
                'pic_name.max' => 'Nama PIC maksimal 100 karakter'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            $branch = Branch::create([
                'name' => $request->name,
                'location' => $request->location,
                'contact_person' => $request->contact_person,
                'pic_name' => $request->pic_name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cabang berhasil ditambahkan!',
                'data' => $branch
            ]);
        } catch (\Exception $e) {
            Log::error('Branch store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan cabang. Silakan coba lagi.'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $branch = Branch::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $branch
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cabang tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $branch = Branch::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100|unique:branches,name,' . $id,
                'location' => 'required|string',
                'contact_person' => 'required|string|max:100',
                'pic_name' => 'required|string|max:100'
            ], [
                'name.required' => 'Nama cabang wajib diisi',
                'name.unique' => 'Nama cabang sudah digunakan',
                'name.max' => 'Nama cabang maksimal 100 karakter',
                'location.required' => 'Lokasi cabang wajib diisi',
                'contact_person.required' => 'Kontak person wajib diisi',
                'contact_person.max' => 'Kontak person maksimal 100 karakter',
                'pic_name.required' => 'Nama PIC wajib diisi',
                'pic_name.max' => 'Nama PIC maksimal 100 karakter'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            $branch->update([
                'name' => $request->name,
                'location' => $request->location,
                'contact_person' => $request->contact_person,
                'pic_name' => $request->pic_name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cabang berhasil diperbarui!',
                'data' => $branch
            ]);
        } catch (\Exception $e) {
            Log::error('Branch update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui cabang. Silakan coba lagi.'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $branch = Branch::findOrFail($id);

            // Check if branch has related data
            $hasUsers = $branch->users()->exists();
            $hasPonds = $branch->ponds()->exists();
            $hasFishTypes = $branch->fishTypes()->exists();

            if ($hasUsers || $hasPonds || $hasFishTypes) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus cabang yang masih memiliki data terkait (pengguna, kolam, atau jenis ikan).'
                ], 400);
            }

            $branch->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cabang berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            Log::error('Branch delete error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus cabang. Silakan coba lagi.'
            ], 500);
        }
    }
}
