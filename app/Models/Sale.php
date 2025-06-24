<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'fish_batch_id',
        'date',
        'quantity_fish',
        'avg_weight_per_fish_kg',
        'price_per_kg',
        'buyer_name',
        'total_price',
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

    // Perhitungan total berat yang dijual
    public function getTotalWeightAttribute()
    {
        return round($this->quantity_fish * $this->avg_weight_per_fish_kg, 2);
    }

    // Perhitungan harga per ekor
    public function getPricePerFishAttribute()
    {
        if ($this->quantity_fish == 0) return 0;
        return round($this->total_price / $this->quantity_fish, 2);
    }

    // Perhitungan persentase dari total stok batch
    public function getPercentageOfBatchAttribute()
    {
        $totalStock = $this->fishBatch->current_stock + $this->quantity_fish;
        if ($totalStock == 0) return 0;

        return round(($this->quantity_fish / $totalStock) * 100, 2);
    }

    // Status penjualan berdasarkan ukuran ikan
    public function getSaleCategoryAttribute()
    {
        $weightInGram = $this->avg_weight_per_fish_kg * 1000;

        if ($weightInGram < 100) return 'fingerling';
        if ($weightInGram < 300) return 'juvenile';
        if ($weightInGram < 500) return 'consumption';
        return 'premium';
    }
}
