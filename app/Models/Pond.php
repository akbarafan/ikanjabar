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
        'documentation_file'
    ];

    // Relationship ke Branch
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    // Relationship ke Fish Batches
    public function fishBatches()
    {
        return $this->hasMany(FishBatch::class);
    }

    // Relationship ke Water Quality (semua data)
    public function waterQualities()
    {
        return $this->hasMany(WaterQuality::class);
    }

    // Relationship ke Water Quality terbaru
    public function latestWaterQuality()
    {
        return $this->hasOne(WaterQuality::class)->latest('date_recorded');
    }

    // Accessor untuk status kualitas air (termasuk ammonia)
    public function getWaterQualityStatusAttribute()
    {
        $latest = $this->latestWaterQuality;

        if (!$latest) return 'unknown';

        // Kriteria danger
        if (
            $latest->ph < 6.5 || $latest->ph > 8.5 ||
            $latest->temperature_c > 30 || $latest->do_mg_l < 5 ||
            $latest->ammonia_mg_l > 0.5
        ) {
            return 'danger';
        }
        // Kriteria warning
        elseif (
            $latest->ph < 7 || $latest->ph > 8 ||
            $latest->temperature_c > 28 || $latest->do_mg_l < 6 ||
            $latest->ammonia_mg_l > 0.25
        ) {
            return 'warning';
        }

        return 'healthy';
    }

    // Accessor untuk density percentage (simulasi)
    public function getDensityPercentageAttribute()
    {
        $activeBatches = $this->fishBatches()->count();
        return min(($activeBatches * 25), 100); // Simulasi density
    }

    // Accessor untuk density status
    public function getDensityStatusAttribute()
    {
        $density = $this->density_percentage;

        if ($density > 80) return 'high';
        if ($density > 60) return 'medium';
        return 'low';
    }
}
