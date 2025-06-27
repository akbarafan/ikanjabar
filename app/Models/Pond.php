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

    public function latestWaterQuality()
    {
        return $this->hasOne(WaterQuality::class)->latest('date_recorded');
    }

    // Get current stock for this pond
    public function getCurrentStockAttribute()
    {
        return $this->fishBatches->sum(function ($batch) {
            $dead = $batch->mortalities()->sum('dead_count');
            $sold = $batch->sales()->sum('quantity_fish');
            $transferredOut = $batch->transfersOut()->sum('transferred_count');
            $transferredIn = $batch->transfersIn()->sum('transferred_count');

            return max(0, $batch->initial_count + $transferredIn - $transferredOut - $dead - $sold);
        });
    }
}
