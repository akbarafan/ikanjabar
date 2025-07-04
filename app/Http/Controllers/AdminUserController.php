<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AdminUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with('branch')
            ->orderBy('created_at', 'desc')
            ->get();

        $branches = Branch::orderBy('name')->get();

        $stats = [
            'total_users' => User::count(),
            'verified_users' => User::where('is_verified', true)->count(),
            'unverified_users' => User::where('is_verified', false)->count(),
            'total_branches' => Branch::count()
        ];

        return view('admin.users.index', compact('users', 'branches', 'stats'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'full_name' => 'required|string|max:100',
                'email' => 'required|email|max:100|unique:users,email',
                'phone_number' => 'required|string|max:20',
                'branch_id' => 'required|exists:branches,id',
                'address' => 'required|string',
                'password' => 'required|string|min:8|confirmed',
                'is_verified' => 'boolean'
            ], [
                'full_name.required' => 'Nama lengkap wajib diisi',
                'full_name.max' => 'Nama lengkap maksimal 100 karakter',
                'email.required' => 'Email wajib diisi',
                'email.email' => 'Format email tidak valid',
                'email.unique' => 'Email sudah digunakan',
                'email.max' => 'Email maksimal 100 karakter',
                'phone_number.required' => 'Nomor telepon wajib diisi',
                'phone_number.max' => 'Nomor telepon maksimal 20 karakter',
                'branch_id.required' => 'Cabang wajib dipilih',
                'branch_id.exists' => 'Cabang tidak valid',
                'address.required' => 'Alamat wajib diisi',
                'password.required' => 'Password wajib diisi',
                'password.min' => 'Password minimal 8 karakter',
                'password.confirmed' => 'Konfirmasi password tidak cocok'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::create([
                'id' => Str::uuid(),
                'full_name' => $request->full_name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'branch_id' => $request->branch_id,
                'address' => $request->address,
                'password' => Hash::make($request->password),
                'is_verified' => $request->boolean('is_verified', false)
            ]);

            $user->load('branch');

            return response()->json([
                'success' => true,
                'message' => 'Pengguna berhasil ditambahkan!',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            Log::error('User store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan pengguna. Silakan coba lagi.'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $user = User::with('branch')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $user = User::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'full_name' => 'required|string|max:100',
                'email' => 'required|email|max:100|unique:users,email,' . $id,
                'phone_number' => 'required|string|max:20',
                'branch_id' => 'required|exists:branches,id',
                'address' => 'required|string',
                'password' => 'nullable|string|min:8|confirmed',
                'is_verified' => 'boolean'
            ], [
                'full_name.required' => 'Nama lengkap wajib diisi',
                'full_name.max' => 'Nama lengkap maksimal 100 karakter',
                'email.required' => 'Email wajib diisi',
                'email.email' => 'Format email tidak valid',
                'email.unique' => 'Email sudah digunakan',
                'email.max' => 'Email maksimal 100 karakter',
                'phone_number.required' => 'Nomor telepon wajib diisi',
                'phone_number.max' => 'Nomor telepon maksimal 20 karakter',
                'branch_id.required' => 'Cabang wajib dipilih',
                'branch_id.exists' => 'Cabang tidak valid',
                'address.required' => 'Alamat wajib diisi',
                'password.min' => 'Password minimal 8 karakter',
                'password.confirmed' => 'Konfirmasi password tidak cocok'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            $updateData = [
                'full_name' => $request->full_name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'branch_id' => $request->branch_id,
                'address' => $request->address,
                'is_verified' => $request->boolean('is_verified', false)
            ];

            // Only update password if provided
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);
            $user->load('branch');

            return response()->json([
                'success' => true,
                'message' => 'Pengguna berhasil diperbarui!',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            Log::error('User update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui pengguna. Silakan coba lagi.'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $user = User::findOrFail($id);

            // Check if user has related data
            $hasRelatedData = $user->fishBatches()->exists() ||
                $user->fishGrowthLogs()->exists() ||
                $user->mortalities()->exists() ||
                $user->waterQualities()->exists() ||
                $user->feedings()->exists() ||
                $user->sales()->exists() ||
                $user->fishBatchTransfers()->exists();

            if ($hasRelatedData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus pengguna yang masih memiliki data terkait (batch ikan, log pertumbuhan, dll).'
                ], 400);
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Pengguna berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            Log::error('User delete error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus pengguna. Silakan coba lagi.'
            ], 500);
        }
    }

    /**
     * Toggle user verification status.
     */
    public function toggleVerification(string $id)
    {
        try {
            $user = User::findOrFail($id);

            $user->update([
                'is_verified' => !$user->is_verified
            ]);

            $status = $user->is_verified ? 'diverifikasi' : 'dibatalkan verifikasinya';

            return response()->json([
                'success' => true,
                'message' => "Pengguna berhasil {$status}!",
                'data' => [
                    'is_verified' => $user->is_verified
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('User toggle verification error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status verifikasi. Silakan coba lagi.'
            ], 500);
        }
    }
}
