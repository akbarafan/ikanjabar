<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'contact_person',
        'pic_name',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function ponds()
    {
        return $this->hasMany(Pond::class);
    }

    public function fishTypes()
    {
        return $this->hasMany(FishType::class);
    }

    // Perhitungan total kolam per cabang
    public function getTotalPondsAttribute()
    {
        return $this->ponds()->count();
    }

    // Perhitungan total volume air per cabang
    public function getTotalVolumeAttribute()
    {
        return $this->ponds()->sum('volume_liters');
    }

    // Perhitungan total batch aktif per cabang
    public function getTotalActiveBatchesAttribute()
    {
        return $this->ponds()
            ->with('fishBatches')
            ->get()
            ->pluck('fishBatches')
            ->flatten()
            ->count();
    }

    // Perhitungan total stok ikan per cabang
    public function getTotalFishStockAttribute()
    {
        $totalStock = 0;
        foreach ($this->ponds as $pond) {
            foreach ($pond->fishBatches as $batch) {
                $totalStock += $batch->current_stock;
            }
        }
        return $totalStock;
    }

    // Perhitungan total penjualan per cabang
    public function getTotalSalesAttribute()
    {
        $totalSales = 0;
        foreach ($this->ponds as $pond) {
            foreach ($pond->fishBatches as $batch) {
                $totalSales += $batch->sales()->sum('total_price');
            }
        }
        return $totalSales;
    }

    // Perhitungan rata-rata kualitas air per cabang
    public function getAverageWaterQualityAttribute()
    {
        $allQualities = collect();
        foreach ($this->ponds as $pond) {
            $allQualities = $allQualities->merge($pond->waterQualities);
        }

        if ($allQualities->isEmpty()) {
            return null;
        }

        return [
            'avg_ph' => round($allQualities->avg('ph'), 2),
            'avg_temperature' => round($allQualities->avg('temperature_c'), 2),
            'avg_do' => round($allQualities->avg('do_mg_l'), 2),
            'avg_ammonia' => round($allQualities->avg('ammonia_mg_l'), 2),
        ];
    }
}
