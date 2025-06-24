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
        'created_by',
    ];

    protected $casts = [
        'date_recorded' => 'date',
    ];

    public function pond()
    {
        return $this->belongsTo(Pond::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Status kualitas air berdasarkan parameter
    public function getWaterQualityStatusAttribute()
    {
        $phStatus = $this->getPhStatus();
        $tempStatus = $this->getTemperatureStatus();
        $doStatus = $this->getDoStatus();
        $ammoniaStatus = $this->getAmmoniaStatus();

        $statuses = [$phStatus, $tempStatus, $doStatus, $ammoniaStatus];

        if (in_array('critical', $statuses)) return 'critical';
        if (in_array('poor', $statuses)) return 'poor';
        if (in_array('moderate', $statuses)) return 'moderate';
        return 'good';
    }

    private function getPhStatus()
    {
        if ($this->ph < 6.0 || $this->ph > 9.0) return 'critical';
        if ($this->ph < 6.5 || $this->ph > 8.5) return 'poor';
        if ($this->ph < 7.0 || $this->ph > 8.0) return 'moderate';
        return 'good';
    }

    private function getTemperatureStatus()
    {
        if ($this->temperature_c < 20 || $this->temperature_c > 35) return 'critical';
        if ($this->temperature_c < 22 || $this->temperature_c > 32) return 'poor';
        if ($this->temperature_c < 25 || $this->temperature_c > 30) return 'moderate';
        return 'good';
    }

    private function getDoStatus()
    {
        if ($this->do_mg_l < 3) return 'critical';
        if ($this->do_mg_l < 4) return 'poor';
        if ($this->do_mg_l < 5) return 'moderate';
        return 'good';
    }

    private function getAmmoniaStatus()
    {
        if ($this->ammonia_mg_l === null) return 'good';
        if ($this->ammonia_mg_l > 1.0) return 'critical';
        if ($this->ammonia_mg_l > 0.5) return 'poor';
        if ($this->ammonia_mg_l > 0.25) return 'moderate';
        return 'good';
    }
}
