<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FishType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public function fishBatches()
    {
        return $this->hasMany(FishBatch::class);
    }

    // Perhitungan total batch per jenis ikan
    public function getTotalBatchesAttribute()
    {
        return $this->fishBatches()->count();
    }

    // Perhitungan rata-rata pertumbuhan per jenis ikan
    public function getAverageGrowthRateAttribute()
    {
        $allGrowthLogs = collect();
        foreach ($this->fishBatches as $batch) {
            $allGrowthLogs = $allGrowthLogs->merge($batch->fishGrowthLogs);
        }

        if ($allGrowthLogs->isEmpty()) return null;

        return [
            'avg_weight_growth' => round($allGrowthLogs->avg('avg_weight_gram'), 2),
            'avg_length_growth' => round($allGrowthLogs->avg('avg_length_cm'), 2),
        ];
    }

    // Perhitungan tingkat mortalitas per jenis ikan
    public function getMortalityRateAttribute()
    {
        $totalInitial = $this->fishBatches()->sum('initial_count');
        $totalDeaths = 0;

        foreach ($this->fishBatches as $batch) {
            $totalDeaths += $batch->mortalities()->sum('dead_count');
        }

        if ($totalInitial == 0) return 0;
        return round(($totalDeaths / $totalInitial) * 100, 2);
    }
}
