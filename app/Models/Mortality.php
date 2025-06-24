<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mortality extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'fish_batch_id',
        'date',
        'dead_count',
        'cause',
        'documentation_file',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function fishBatch()
    {
        return $this->belongsTo(FishBatch::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Perhitungan persentase kematian dari stok saat itu
    public function getMortalityPercentageAttribute()
    {
        $stockBeforeDeath = $this->fishBatch->current_stock + $this->dead_count;
        if ($stockBeforeDeath == 0) return 0;

        return round(($this->dead_count / $stockBeforeDeath) * 100, 2);
    }

    // Status tingkat kematian
    public function getMortalityLevelAttribute()
    {
        $percentage = $this->mortality_percentage;

        if ($percentage <= 2) return 'normal';
        if ($percentage <= 5) return 'moderate';
        if ($percentage <= 10) return 'high';
        return 'critical';
    }
}
