<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WaterQualityController extends Controller
{
    private function getCurrentBranchId()
    {
        // Sementara hardcode untuk demo, nanti bisa dari session/auth
        return 1;
    }

    private function getCurrentUserId()
    {
        // Sementara hardcode untuk demo, nanti bisa dari auth
        return DB::table('users')->where('branch_id', $this->getCurrentBranchId())->first()->id ?? '550e8400-e29b-41d4-a716-446655440000';
    }

    public function index()
    {
        $branchId = $this->getCurrentBranchId();

        // Get branch info
        $branchInfo = DB::table('branches')->find($branchId);

        // Get water quality records with pond info - optimized query
        $waterQualities = DB::table('water_qualities as wq')
            ->join('ponds as p', 'wq.pond_id', '=', 'p.id')
            ->leftJoin('users as u', 'wq.created_by', '=', 'u.id')
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
                'p.name as pond_name',
                'p.code as pond_code',
                'u.full_name as created_by_name'
            )
            ->orderBy('wq.date_recorded', 'desc')
            ->orderBy('wq.created_at', 'desc')
            ->get();

        // Add quality status for each record
        foreach ($waterQualities as $wq) {
            $wq->ph_status = $this->getPhStatus($wq->ph);
            $wq->temp_status = $this->getTempStatus($wq->temperature_c);
            $wq->do_status = $this->getDoStatus($wq->do_mg_l);
            $wq->ammonia_status = $this->getAmmoniaStatus($wq->ammonia_mg_l);
            $wq->overall_status = $this->getOverallStatus($wq);
        }

        // Summary stats
        $stats = [
            'total_records' => $waterQualities->count(),
            'records_today' => $waterQualities->where('date_recorded', now()->format('Y-m-d'))->count(),
            'good_quality' => $waterQualities->where('overall_status', 'good')->count(),
            'monitored_ponds' => $waterQualities->unique('pond_name')->count()
        ];

        // Get ponds for dropdown
        $ponds = DB::table('ponds')->where('branch_id', $branchId)->select('id', 'name', 'code')->orderBy('name')->get();

        return view('user.water-qualities.index', compact('waterQualities', 'branchInfo', 'stats', 'ponds'));
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
            $pond = DB::table('ponds')->where('id', $request->pond_id)->where('branch_id', $this->getCurrentBranchId())->first();
            if (!$pond) {
                return response()->json(['success' => false, 'message' => 'Kolam tidak valid'], 400);
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
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan data kualitas air. Silakan coba lagi.'
            ], 500);
        }
    }

    public function show($id)
    {
        $waterQuality = DB::table('water_qualities as wq')
            ->join('ponds as p', 'wq.pond_id', '=', 'p.id')
            ->where('wq.id', $id)
            ->where('p.branch_id', $this->getCurrentBranchId())
            ->whereNull('wq.deleted_at')
            ->select('wq.*', 'p.name as pond_name')
            ->first();

        if (!$waterQuality) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
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
            // Verify record belongs to current branch
            $record = DB::table('water_qualities as wq')
                ->join('ponds as p', 'wq.pond_id', '=', 'p.id')
                ->where('wq.id', $id)
                ->where('p.branch_id', $this->getCurrentBranchId())
                ->whereNull('wq.deleted_at')
                ->first();

            if (!$record) {
                return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
            }

            // Verify new pond belongs to current branch
            $pond = DB::table('ponds')->where('id', $request->pond_id)->where('branch_id', $this->getCurrentBranchId())->first();
            if (!$pond) {
                return response()->json(['success' => false, 'message' => 'Kolam tidak valid'], 400);
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
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data kualitas air. Silakan coba lagi.'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            // Verify record belongs to current branch
            $record = DB::table('water_qualities as wq')
                ->join('ponds as p', 'wq.pond_id', '=', 'p.id')
                ->where('wq.id', $id)
                ->where('p.branch_id', $this->getCurrentBranchId())
                ->whereNull('wq.deleted_at')
                ->first();

            if (!$record) {
                return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
            }

            // Soft delete
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
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data kualitas air. Silakan coba lagi.'
            ], 500);
        }
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
}
