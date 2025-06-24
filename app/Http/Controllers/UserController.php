<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('branch')
            ->when(request('search'), function ($query) {
                $query->where('full_name', 'like', '%' . request('search') . '%')
                    ->orWhere('email', 'like', '%' . request('search') . '%');
            })
            ->when(request('branch_id'), function ($query) {
                $query->where('branch_id', request('branch_id'));
            })
            ->when(request('is_verified') !== null, function ($query) {
                $query->where('is_verified', request('is_verified'));
            })
            ->paginate(15);

        $branches = Branch::all();

        return view('users.index', compact('users', 'branches'));
    }

    public function create()
    {
        $branches = Branch::all();
        return view('users.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:100',
            'address' => 'required|string',
            'branch_id' => 'required|exists:branches,id',
            'phone_number' => 'required|string|max:20',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,manager,staff',
            'is_verified' => 'boolean',
        ]);

        $validated['id'] = Str::uuid();
        $validated['password'] = Hash::make($validated['password']);
        $validated['is_verified'] = $request->has('is_verified');

        User::create($validated);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil ditambahkan');
    }

    public function show(User $user)
    {
        $user->load(['branch', 'fishBatches', 'fishGrowthLogs', 'mortalities', 'waterQualities', 'feedings', 'sales']);

        // Statistik aktivitas user
        $statistics = [
            'total_batches_created' => $user->fishBatches()->count(),
            'total_growth_logs' => $user->fishGrowthLogs()->count(),
            'total_mortality_reports' => $user->mortalities()->count(),
            'total_water_quality_tests' => $user->waterQualities()->count(),
            'total_feeding_records' => $user->feedings()->count(),
            'total_sales_records' => $user->sales()->count(),
            'total_transfers' => $user->fishBatchTransfers()->count(),
        ];

        // Aktivitas bulanan
        $monthlyActivity = $this->getUserMonthlyActivity($user);

        // Performance score
        $performanceScore = $this->calculateUserPerformanceScore($user);

        return view('users.show', compact('user', 'statistics', 'monthlyActivity', 'performanceScore'));
    }

    public function edit(User $user)
    {
        $branches = Branch::all();
        return view('users.edit', compact('user', 'branches'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:100',
            'address' => 'required|string',
            'branch_id' => 'required|exists:branches,id',
            'phone_number' => 'required|string|max:20',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,manager,staff',
            'is_verified' => 'boolean',
        ]);

        $validated['is_verified'] = $request->has('is_verified');

        if ($request->filled('password')) {
            $request->validate([
                'password' => 'string|min:8|confirmed'
            ]);
            $validated['password'] = Hash::make($request->password);
        }

        $user->update($validated);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil diperbarui');
    }

    public function destroy(User $user)
    {
        // Cek apakah user masih memiliki data terkait
        if ($user->fishBatches()->count() > 0) {
            return redirect()->route('users.index')
                ->with('error', 'User tidak dapat dihapus karena masih memiliki data terkait');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User berhasil dihapus');
    }

    public function toggleVerification(User $user)
    {
        $user->update([
            'is_verified' => !$user->is_verified
        ]);

        $status = $user->is_verified ? 'diverifikasi' : 'dibatalkan verifikasinya';

        return redirect()->back()
            ->with('success', "User {$user->full_name} berhasil {$status}");
    }

    public function bulkVerify(Request $request)
    {
        $userIds = $request->input('user_ids', []);
        $action = $request->input('action');

        if (empty($userIds)) {
            return redirect()->back()
                ->with('error', 'Pilih minimal satu user');
        }

        $isVerified = $action === 'verify';

        User::whereIn('id', $userIds)->update([
            'is_verified' => $isVerified
        ]);

        $message = $isVerified ? 'diverifikasi' : 'dibatalkan verifikasinya';

        return redirect()->back()
            ->with('success', count($userIds) . " user berhasil {$message}");
    }

    // Helper methods
    private function getUserMonthlyActivity($user)
    {
        $activity = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthKey = $month->format('Y-m');

            $totalActivities = 0;
            $totalActivities += $user->fishBatches()->whereYear('created_at', $month->year)->whereMonth('created_at', $month->month)->count();
            $totalActivities += $user->fishGrowthLogs()->whereYear('created_at', $month->year)->whereMonth('created_at', $month->month)->count();
            $totalActivities += $user->mortalities()->whereYear('created_at', $month->year)->whereMonth('created_at', $month->month)->count();
            $totalActivities += $user->waterQualities()->whereYear('created_at', $month->year)->whereMonth('created_at', $month->month)->count();
            $totalActivities += $user->feedings()->whereYear('created_at', $month->year)->whereMonth('created_at', $month->month)->count();
            $totalActivities += $user->sales()->whereYear('created_at', $month->year)->whereMonth('created_at', $month->month)->count();

            $activity[] = [
                'month' => $month->format('M Y'),
                'activities' => $totalActivities
            ];
        }

        return $activity;
    }

    private function calculateUserPerformanceScore($user)
    {
        $scores = [];

        // Consistency score (berdasarkan aktivitas rutin)
        $monthlyActivity = $this->getUserMonthlyActivity($user);
        $activeMonths = collect($monthlyActivity)->where('activities', '>', 0)->count();
        $consistencyScore = ($activeMonths / 12) * 100;
        $scores['consistency'] = round($consistencyScore, 1);

        // Data quality score (berdasarkan kelengkapan data)
        $totalRecords = $user->fishGrowthLogs()->count() + $user->waterQualities()->count() + $user->feedings()->count();
        $recordsWithFiles = $user->fishGrowthLogs()->whereNotNull('documentation_file')->count() +
            $user->waterQualities()->whereNotNull('documentation_file')->count();

        $dataQualityScore = $totalRecords > 0 ? ($recordsWithFiles / $totalRecords) * 100 : 0;
        $scores['data_quality'] = round($dataQualityScore, 1);

        // Productivity score (berdasarkan jumlah aktivitas)
        $totalActivities = array_sum(array_column($monthlyActivity, 'activities'));
        $productivityScore = min($totalActivities * 2, 100); // Max 100
        $scores['productivity'] = round($productivityScore, 1);

        // Overall score
        $scores['overall'] = round(array_sum($scores) / count($scores), 1);

        return $scores;
    }
}
