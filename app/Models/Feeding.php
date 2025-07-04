<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Feeding extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'fish_batch_id',
        'date',
        'feed_type',
        'feed_amount_kg',
        'notes',
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

    // Perhitungan feeding rate (% dari biomassa)
    public function getFeedingRateAttribute()
    {
        $currentBiomass = $this->fishBatch->current_biomass;
        if ($currentBiomass == 0) return 0;

        return round(($this->feed_amount_kg / $currentBiomass) * 100, 2);
    }

    // Status feeding rate
    public function getFeedingStatusAttribute()
    {
        $rate = $this->feeding_rate;

        if ($rate < 1) return 'underfeeding';
        if ($rate <= 3) return 'optimal';
        if ($rate <= 5) return 'moderate';
        return 'overfeeding';
    }

    // Perhitungan pakan per ekor
    public function getFeedPerFishAttribute()
    {
        $currentStock = $this->fishBatch->current_stock;
        if ($currentStock == 0) return 0;

        return round(($this->feed_amount_kg * 1000) / $currentStock, 2); // dalam gram
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
