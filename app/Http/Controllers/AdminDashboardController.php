<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Pond;
use App\Models\FishBatch;
use App\Models\WaterQuality;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Mengambil data branches sesuai dengan struktur tabel
        $branches = Branch::select('id', 'name', 'location', 'contact_person', 'pic_name')
            ->withCount('ponds')
            ->paginate(5);

        // Stats Cards Data
        $totalBranches = Branch::count();
        $totalPonds = Pond::count();
        $totalUsers = User::count();
        $activeBatches = FishBatch::count();

        // Sales Data
        $totalSales = Sale::sum('total_price') ?? 0;
        $lastMonthSales = Sale::whereMonth('date', Carbon::now()->subMonth()->month)
            ->whereYear('date', Carbon::now()->subMonth()->year)
            ->sum('total_price') ?? 0;
        $currentMonthSales = Sale::whereMonth('date', Carbon::now()->month)
            ->whereYear('date', Carbon::now()->year)
            ->sum('total_price') ?? 0;

        // Calculate sales growth percentage
        $salesGrowth = $lastMonthSales > 0
            ? (($currentMonthSales - $lastMonthSales) / $lastMonthSales) * 100
            : 0;

        // Monthly sales data for chart
        $monthlySales = collect();
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $amount = Sale::whereYear('date', $date->year)
                ->whereMonth('date', $date->month)
                ->sum('total_price') ?? 0;

            $monthlySales->push([
                'month' => $date->format('M Y'),
                'amount' => $amount
            ]);
        }

        // Pond types distribution for chart
        $pondTypes = Pond::select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        // Jika tidak ada data, berikan data dummy
        if (empty($pondTypes)) {
            $pondTypes = [
                'Kolam Tanah' => 5,
                'Kolam Terpal' => 3,
                'Kolam Beton' => 2
            ];
        }

        // Water quality averages
        $avgWaterQuality = [
            'avg_ph' => WaterQuality::avg('ph') ?? 7.2,
            'avg_temperature' => WaterQuality::avg('temperature_c') ?? 27.5,
            'avg_do' => WaterQuality::avg('do_mg_l') ?? 6.0,
            'avg_ammonia' => WaterQuality::avg('ammonia_mg_l') ?? 0.2,
        ];

        return view('admin.dashboard', compact(
            'totalBranches',
            'totalPonds',
            'totalUsers',
            'activeBatches',
            'totalSales',
            'salesGrowth',
            'branches',
            'monthlySales',
            'pondTypes',
            'avgWaterQuality'
        ));
    }
}
