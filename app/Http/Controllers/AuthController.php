<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login.index');
    }

    public function showRegisterForm()
    {
        $branches = Branch::orderBy('name')->get();
        return view('auth.register.index', compact('branches'));
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->only('email'));
        }

        $remember = $request->has('remember');

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password], $remember)) {
            $request->session()->regenerate();
            $user = Auth::user();

            // Check if user is verified
            if (!$user->is_verified) {
                Auth::logout();
                return back()
                    ->withErrors(['email' => 'Akun Anda belum diverifikasi. Silakan hubungi administrator.'])
                    ->withInput($request->only('email'));
            }

            // Redirect berdasarkan role
            if ($user->hasRole('admin')) {
                return redirect()->route('admin.dashboard')
                    ->with('success', 'Selamat datang kembali, Administrator!');
            }

            if ($user->hasRole('branches')) {
                return redirect()->route('user.dashboard')
                    ->with('success', 'Selamat datang kembali, Manager Cabang!');
            }

            if ($user->hasRole('student')) {
                return redirect()->route('user.dashboard')
                    ->with('success', 'Selamat datang kembali!');
            }

            // Default redirect jika tidak ada role khusus
            return redirect()->route('dashboard')
                ->with('success', 'Selamat datang kembali!');
        }

        return back()
            ->withErrors(['email' => 'Email atau password yang Anda masukkan salah.'])
            ->withInput($request->only('email'));
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:100',
            'email' => 'required|email|max:100|unique:users,email',
            'phone_number' => 'required|string|max:20',
            'address' => 'required|string',
            'branch_id' => 'required|exists:branches,id',
            'password' => 'required|min:8|confirmed',
            'terms' => 'required|accepted',
        ], [
            'full_name.required' => 'Nama lengkap wajib diisi.',
            'full_name.max' => 'Nama lengkap maksimal 100 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'phone_number.required' => 'Nomor telepon wajib diisi.',
            'address.required' => 'Alamat wajib diisi.',
            'branch_id.required' => 'Cabang wajib dipilih.',
            'branch_id.exists' => 'Cabang yang dipilih tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'terms.required' => 'Anda harus menyetujui syarat dan ketentuan.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->except('password', 'password_confirmation'));
        }

        try {
            // Create user dengan UUID
            $user = User::create([
                'id' => Str::uuid(),
                'full_name' => $request->full_name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'address' => $request->address,
                'branch_id' => $request->branch_id,
                'password' => Hash::make($request->password),
                'is_verified' => false, // Set false untuk testing, admin harus verifikasi
            ]);

            // Assign default role 'student' untuk user baru
            $user->assignRole('student');

            return redirect()->route('login')
                ->with('success', 'Pendaftaran berhasil! Silakan tunggu verifikasi dari administrator.');
        } catch (\Exception $e) {
            \Log::error('Registration error: ' . $e->getMessage());
            return back()
                ->withErrors(['error' => 'Terjadi kesalahan saat mendaftar. Silakan coba lagi.'])
                ->withInput($request->except('password', 'password_confirmation'));
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Anda telah berhasil logout.');
    }
}
