<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class WaterQualityController extends Controller
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
        $totalRecords = DB::table('water_qualities as wq')
            ->join('ponds as p', 'wq.pond_id', '=', 'p.id')
            ->where('p.branch_id', $branchId)
            ->whereNull('wq.deleted_at')
            ->count();

        // Get water quality records with pagination - optimized query
        $waterQualities = $this->getWaterQualitiesQuery($branchId)
            ->limit(self::ITEMS_PER_PAGE)
            ->offset($offset)
            ->get();

        // Process water quality data
        $this->processWaterQualityData($waterQualities);

        // Calculate stats
        $stats = $this->calculateStats($branchId);

        // Get active ponds for form
        $ponds = $this->getActivePonds($branchId);

        // Pagination info
        $pagination = [
            'current_page' => $page,
            'total_pages' => ceil($totalRecords / self::ITEMS_PER_PAGE),
            'total_items' => $totalRecords,
            'per_page' => self::ITEMS_PER_PAGE,
            'has_prev' => $page > 1,
            'has_next' => $page < ceil($totalRecords / self::ITEMS_PER_PAGE),
            'prev_page' => $page > 1 ? $page - 1 : null,
            'next_page' => $page < ceil($totalRecords / self::ITEMS_PER_PAGE) ? $page + 1 : null
        ];

        return view('user.water-qualities.index', compact(
            'waterQualities',
            'branchInfo',
            'stats',
            'ponds',
            'pagination'
        ));
    }

    private function getWaterQualitiesQuery($branchId)
    {
        return DB::table('water_qualities as wq')
            ->join('ponds as p', 'wq.pond_id', '=', 'p.id')
            ->leftJoin('users as u', 'wq.created_by', '=', 'u.id')
            ->leftJoin('fish_batches as fb', function ($join) {
                $join->on('p.id', '=', 'fb.pond_id')
                    ->whereNull('fb.deleted_at');
            })
            ->leftJoin('fish_types as ft', 'fb.fish_type_id', '=', 'ft.id')
            ->where('p.branch_id', $branchId)
            ->whereNull('wq.deleted_at')
            ->select(
                'wq.id',
                'wq.date_recorded',
                'wq.ph',
                'wq.temperature_c',
                'wq.do_mg_l',
                'wq.ammonia_mg_l',
                'wq.created_at',
                'p.id as pond_id',
                'p.name as pond_name',
                'p.code as pond_code',
                'p.type as pond_type',
                'p.volume_liters',
                'p.documentation_file as pond_image',
                'u.full_name as created_by_name',
                DB::raw('GROUP_CONCAT(DISTINCT ft.name) as fish_types'),
                DB::raw('COUNT(DISTINCT fb.id) as active_batches')
            )
            ->groupBy(
                'wq.id',
                'wq.date_recorded',
                'wq.ph',
                'wq.temperature_c',
                'wq.do_mg_l',
                'wq.ammonia_mg_l',
                'wq.created_at',
                'p.id',
                'p.name',
                'p.code',
                'p.type',
                'p.volume_liters',
                'p.documentation_file',
                'u.full_name'
            )
            ->orderBy('wq.date_recorded', 'desc')
            ->orderBy('wq.created_at', 'desc');
    }

    private function processWaterQualityData($waterQualities)
    {
        foreach ($waterQualities as $wq) {
            // Add pond image URL
            $wq->pond_image_url = $wq->pond_image
                ? Storage::url($wq->pond_image)
                : null;

            // Convert fish_types string to array
            $wq->fish_types_array = $wq->fish_types
                ? explode(',', $wq->fish_types)
                : [];

            // Add quality status
            $wq->ph_status = $this->getPhStatus($wq->ph);
            $wq->temp_status = $this->getTempStatus($wq->temperature_c);
            $wq->do_status = $this->getDoStatus($wq->do_mg_l);
            $wq->ammonia_status = $this->getAmmoniaStatus($wq->ammonia_mg_l);
            $wq->overall_status = $this->getOverallStatus($wq);

            // Format for mobile display
            $wq->formatted_date = \Carbon\Carbon::parse($wq->date_recorded)->format('d M');
            $wq->formatted_ph = number_format($wq->ph, 1);
            $wq->formatted_temp = number_format($wq->temperature_c, 1);
            $wq->formatted_do = number_format($wq->do_mg_l, 1);
            $wq->formatted_ammonia = $wq->ammonia_mg_l ? number_format($wq->ammonia_mg_l, 2) : null;

            // Overall status text for mobile
            $wq->status_text = $this->getStatusText($wq->overall_status);
            $wq->status_color = $this->getStatusColor($wq->overall_status);

            // Critical parameters count
            $wq->critical_params = $this->getCriticalParametersCount($wq);
        }
    }

    private function calculateStats($branchId)
    {
        $statsQuery = DB::table('water_qualities as wq')
            ->join('ponds as p', 'wq.pond_id', '=', 'p.id')
            ->where('p.branch_id', $branchId)
            ->whereNull('wq.deleted_at');

        $totalRecords = $statsQuery->count();
        $recordsToday = $statsQuery->whereDate('wq.date_recorded', today())->count();

        // Get records from last 7 days for quality assessment
        $recentRecords = DB::table('water_qualities as wq')
            ->join('ponds as p', 'wq.pond_id', '=', 'p.id')
            ->where('p.branch_id', $branchId)
            ->where('wq.date_recorded', '>=', now()->subDays(7))
            ->whereNull('wq.deleted_at')
            ->get();

        $goodQuality = 0;
        $warningQuality = 0;
        $criticalQuality = 0;

        foreach ($recentRecords as $record) {
            $phStatus = $this->getPhStatus($record->ph);
            $tempStatus = $this->getTempStatus($record->temperature_c);
            $doStatus = $this->getDoStatus($record->do_mg_l);
            $ammoniaStatus = $this->getAmmoniaStatus($record->ammonia_mg_l);

            $statuses = [$phStatus, $tempStatus, $doStatus];
            if ($record->ammonia_mg_l !== null) {
                $statuses[] = $ammoniaStatus;
            }

            if (in_array('danger', $statuses)) {
                $criticalQuality++;
            } elseif (in_array('warning', $statuses)) {
                $warningQuality++;
            } else {
                $goodQuality++;
            }
        }

        $monitoredPonds = DB::table('water_qualities as wq')
            ->join('ponds as p', 'wq.pond_id', '=', 'p.id')
            ->where('p.branch_id', $branchId)
            ->whereNull('wq.deleted_at')
            ->distinct('p.id')
            ->count();

        return [
            'total_records' => $totalRecords,
            'records_today' => $recordsToday,
            'good_quality' => $goodQuality,
            'warning_quality' => $warningQuality,
            'critical_quality' => $criticalQuality,
            'monitored_ponds' => $monitoredPonds
        ];
    }

    private function getActivePonds($branchId)
    {
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
                'p.documentation_file',
                DB::raw('COUNT(DISTINCT fb.id) as active_batches'),
                DB::raw('GROUP_CONCAT(DISTINCT ft.name) as fish_types')
            )
            ->groupBy('p.id', 'p.name', 'p.code', 'p.type', 'p.volume_liters', 'p.documentation_file')
            ->orderBy('p.name')
            ->get();

        // Add image URLs and format data for each pond
        foreach ($ponds as $pond) {
            $pond->image_url = $pond->documentation_file
                ? Storage::url($pond->documentation_file)
                : null;

            $pond->fish_types_array = $pond->fish_types
                ? explode(',', $pond->fish_types)
                : [];

            $pond->has_fish = $pond->active_batches > 0;
        }

        return $ponds;
    }

    public function store(Request $request)
    {
        $request->validate([
            'pond_id' => 'required|exists:ponds,id',
            'date_recorded' => 'required|date|before_or_equal:today',
            'ph' => 'required|numeric|between:0,14',
            'temperature_c' => 'required|numeric|between:0,50',
            'do_mg_l' => 'required|numeric|between:0,20',
            'ammonia_mg_l' => 'nullable|numeric|between:0,10'
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

            DB::table('water_qualities')->insert([
                'pond_id' => $request->pond_id,
                'date_recorded' => $request->date_recorded,
                'ph' => $request->ph,
                'temperature_c' => $request->temperature_c,
                'do_mg_l' => $request->do_mg_l,
                'ammonia_mg_l' => $request->ammonia_mg_l,
                'created_by' => $this->getCurrentUserId(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data kualitas air berhasil ditambahkan!'
            ]);
        } catch (\Exception $e) {
            \Log::error('Water quality store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan data kualitas air. Silakan coba lagi.'
            ], 500);
        }
    }

    public function show($id)
    {
        $waterQuality = $this->findWaterQuality($id);

        if (!$waterQuality) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        return response()->json(['success' => true, 'data' => $waterQuality]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'pond_id' => 'required|exists:ponds,id',
            'date_recorded' => 'required|date|before_or_equal:today',
            'ph' => 'required|numeric|between:0,14',
            'temperature_c' => 'required|numeric|between:0,50',
            'do_mg_l' => 'required|numeric|between:0,20',
            'ammonia_mg_l' => 'nullable|numeric|between:0,10'
        ]);

        try {
            // Verify record exists
            $waterQuality = $this->findWaterQuality($id);
            if (!$waterQuality) {
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

            DB::table('water_qualities')
                ->where('id', $id)
                ->update([
                    'pond_id' => $request->pond_id,
                    'date_recorded' => $request->date_recorded,
                    'ph' => $request->ph,
                    'temperature_c' => $request->temperature_c,
                    'do_mg_l' => $request->do_mg_l,
                    'ammonia_mg_l' => $request->ammonia_mg_l,
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Data kualitas air berhasil diperbarui!'
            ]);
        } catch (\Exception $e) {
            \Log::error('Water quality update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data kualitas air. Silakan coba lagi.'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $waterQuality = $this->findWaterQuality($id);
            if (!$waterQuality) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            DB::table('water_qualities')
                ->where('id', $id)
                ->update([
                    'deleted_at' => now(),
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Data kualitas air berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            \Log::error('Water quality delete error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data kualitas air. Silakan coba lagi.'
            ], 500);
        }
    }

    private function findWaterQuality($id)
    {
        return DB::table('water_qualities as wq')
            ->join('ponds as p', 'wq.pond_id', '=', 'p.id')
            ->where('wq.id', $id)
            ->where('p.branch_id', $this->getCurrentBranchId())
            ->whereNull('wq.deleted_at')
            ->select('wq.*')
            ->first();
    }

    private function validatePond($pondId)
    {
        return DB::table('ponds')
            ->where('id', $pondId)
            ->where('branch_id', $this->getCurrentBranchId())
            ->first();
    }

    // Helper methods for quality assessment
    private function getPhStatus($ph)
    {
        if ($ph >= 6.5 && $ph <= 8.5) return 'good';
        if ($ph >= 6.0 && $ph <= 9.0) return 'warning';
        return 'danger';
    }

    private function getTempStatus($temp)
    {
        if ($temp >= 25 && $temp <= 30) return 'good';
        if ($temp >= 20 && $temp <= 35) return 'warning';
        return 'danger';
    }

    private function getDoStatus($do)
    {
        if ($do >= 5) return 'good';
        if ($do >= 3) return 'warning';
        return 'danger';
    }

    private function getAmmoniaStatus($ammonia)
    {
        if ($ammonia === null) return 'unknown';
        if ($ammonia <= 0.1) return 'good';
        if ($ammonia <= 0.5) return 'warning';
        return 'danger';
    }

    private function getOverallStatus($wq)
    {
        $statuses = [$wq->ph_status, $wq->temp_status, $wq->do_status];
        if ($wq->ammonia_mg_l !== null) {
            $statuses[] = $wq->ammonia_status;
        }

        if (in_array('danger', $statuses)) return 'danger';
        if (in_array('warning', $statuses)) return 'warning';
        return 'good';
    }

    private function getStatusText($status)
    {
        switch ($status) {
            case 'good':
                return 'Baik';
            case 'warning':
                return 'Perhatian';
            case 'danger':
                return 'Kritis';
            default:
                return 'Unknown';
        }
    }

    private function getStatusColor($status)
    {
        switch ($status) {
            case 'good':
                return 'green';
            case 'warning':
                return 'yellow';
            case 'danger':
                return 'red';
            default:
                return 'gray';
        }
    }

    private function getCriticalParametersCount($wq)
    {
        $critical = 0;
        if ($wq->ph_status === 'danger') $critical++;
        if ($wq->temp_status === 'danger') $critical++;
        if ($wq->do_status === 'danger') $critical++;
        if ($wq->ammonia_status === 'danger') $critical++;
        return $critical;
    }
}
