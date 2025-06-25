<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WaterQuality extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'pond_id',
        'date_recorded',
        'ph',
        'temperature_c',
        'do_mg_l',
        'ammonia_mg_l',
        'documentation_file',
        'created_by'
    ];

    protected $casts = [
        'date_recorded' => 'date',
        'ph' => 'float',
        'temperature_c' => 'float',
        'do_mg_l' => 'float',
        'ammonia_mg_l' => 'float',
    ];

    // Relationship ke Pond
    public function pond()
    {
        return $this->belongsTo(Pond::class);
    }

    // Relationship ke User (creator)
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Accessor untuk status kualitas air (termasuk ammonia)
    public function getWaterQualityStatusAttribute()
    {
        // Kriteria danger: parameter ekstrem
        if (
            $this->ph < 6.5 || $this->ph > 8.5 ||
            $this->temperature_c > 30 || $this->do_mg_l < 5 ||
            $this->ammonia_mg_l > 0.5
        ) {
            return 'danger';
        }
        // Kriteria warning: parameter mendekati batas
        elseif (
            $this->ph < 7 || $this->ph > 8 ||
            $this->temperature_c > 28 || $this->do_mg_l < 6 ||
            $this->ammonia_mg_l > 0.25
        ) {
            return 'warning';
        }

        return 'healthy';
    }

    // Accessor untuk mendapatkan parameter yang bermasalah
    public function getProblematicParametersAttribute()
    {
        $problems = [];

        if ($this->ph < 6.5) $problems[] = 'pH Rendah (' . $this->ph . ')';
        if ($this->ph > 8.5) $problems[] = 'pH Tinggi (' . $this->ph . ')';
        if ($this->temperature_c > 30) $problems[] = 'Suhu Tinggi (' . $this->temperature_c . '°C)';
        if ($this->do_mg_l < 5) $problems[] = 'DO Rendah (' . $this->do_mg_l . ' mg/L)';
        if ($this->ammonia_mg_l > 0.5) $problems[] = 'Ammonia Tinggi (' . $this->ammonia_mg_l . ' mg/L)';

        return $problems;
    }
}
