<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pond extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'name',
        'code',
        'type',
        'volume_liters',
        'description',
        'documentation_file',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function fishBatches()
    {
        return $this->hasMany(FishBatch::class);
    }

    public function waterQualities()
    {
        return $this->hasMany(WaterQuality::class);
    }

    // Perhitungan kapasitas optimal berdasarkan tipe kolam
    public function getOptimalCapacityAttribute()
    {
        $densityPerLiter = match ($this->type) {
            'tanah' => 0.5,
            'beton' => 0.8,
            'viber' => 0.7,
            'terpal' => 0.6,
            default => 0.5
        };

        return (int) ($this->volume_liters * $densityPerLiter);
    }

    // Perhitungan stok ikan saat ini
    public function getCurrentStockAttribute()
    {
        return $this->fishBatches()->sum(function ($batch) {
            return $batch->current_stock;
        });
    }

    // Perhitungan tingkat kepadatan
    public function getDensityPercentageAttribute()
    {
        if ($this->optimal_capacity == 0) return 0;
        return round(($this->current_stock / $this->optimal_capacity) * 100, 2);
    }

    // Status kepadatan kolam
    public function getDensityStatusAttribute()
    {
        $percentage = $this->density_percentage;

        if ($percentage <= 60) return 'optimal';
        if ($percentage <= 80) return 'moderate';
        if ($percentage <= 100) return 'high';
        return 'overcrowded';
    }

    // Kualitas air terbaru
    public function getLatestWaterQualityAttribute()
    {
        return $this->waterQualities()->latest('date_recorded')->first();
    }
}
