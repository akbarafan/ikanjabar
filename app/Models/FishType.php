<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FishType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description'
    ];

    /**
     * Relationship with FishBatch
     */
    public function fishBatches()
    {
        return $this->hasMany(FishBatch::class);
    }

    /**
     * Get total batches count
     */
    public function getTotalBatchesAttribute()
    {
        return $this->fishBatches()->count();
    }

    /**
     * Get average growth rate
     */
    public function getAverageGrowthRateAttribute()
    {
        $totalWeight = 0;
        $totalLogs = 0;

        foreach ($this->fishBatches as $batch) {
            if ($batch->fishGrowthLogs) {
                foreach ($batch->fishGrowthLogs as $log) {
                    $totalWeight += $log->avg_weight_gram;
                    $totalLogs++;
                }
            }
        }

        return $totalLogs > 0 ? round($totalWeight / $totalLogs, 2) : 0;
    }

    /**
     * Get mortality rate
     */
    public function getMortalityRateAttribute()
    {
        $totalInitial = $this->fishBatches()->sum('initial_count');
        $totalDeaths = 0;

        foreach ($this->fishBatches as $batch) {
            if ($batch->mortalities) {
                $totalDeaths += $batch->mortalities()->sum('dead_count');
            }
        }

        return $totalInitial > 0 ? round(($totalDeaths / $totalInitial) * 100, 2) : 0;
    }
}

