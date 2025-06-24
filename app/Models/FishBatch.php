<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FishBatch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'pond_id',
        'fish_type_id',
        'date_start',
        'initial_count',
        'notes',
        'documentation_file',
        'created_by',
    ];

    protected $casts = [
        'date_start' => 'date',
    ];

    public function pond()
    {
        return $this->belongsTo(Pond::class);
    }

    public function fishType()
    {
        return $this->belongsTo(FishType::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function fishStockSnapshots()
    {
        return $this->hasMany(FishStockSnapshot::class);
    }

    public function fishGrowthLogs()
    {
        return $this->hasMany(FishGrowthLog::class);
    }

    public function mortalities()
    {
        return $this->hasMany(Mortality::class);
    }

    public function feedings()
    {
        return $this->hasMany(Feeding::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function sourceTransfers()
    {
        return $this->hasMany(FishBatchTransfer::class, 'source_batch_id');
    }

    public function targetTransfers()
    {
        return $this->hasMany(FishBatchTransfer::class, 'target_batch_id');
    }

    // Perhitungan stok saat ini
    public function getCurrentStockAttribute()
    {
        $initialStock = $this->initial_count;
        $totalDeaths = $this->mortalities()->sum('dead_count');
        $totalSold = $this->sales()->sum('quantity_fish');
        $totalTransferredOut = $this->sourceTransfers()->sum('transferred_count');
        $totalTransferredIn = $this->targetTransfers()->sum('transferred_count');

        return $initialStock - $totalDeaths - $totalSold - $totalTransferredOut + $totalTransferredIn;
    }

    // Perhitungan umur batch (dalam hari)
    public function getAgeInDaysAttribute()
    {
        return $this->date_start->diffInDays(now());
    }

    // Perhitungan umur batch (dalam minggu)
    public function getAgeInWeeksAttribute()
    {
        return (int) ($this->age_in_days / 7);
    }

    // Perhitungan tingkat mortalitas
    public function getMortalityRateAttribute()
    {
        if ($this->initial_count == 0) return 0;
        $totalDeaths = $this->mortalities()->sum('dead_count');
        return round(($totalDeaths / $this->initial_count) * 100, 2);
    }

    // Perhitungan tingkat kelangsungan hidup
    public function getSurvivalRateAttribute()
    {
        return round(100 - $this->mortality_rate, 2);
    }

    // Perhitungan total pakan yang diberikan
    public function getTotalFeedGivenAttribute()
    {
        return round($this->feedings()->sum('feed_amount_kg'), 2);
    }

    // Perhitungan FCR (Feed Conversion Ratio)
    public function getFcrAttribute()
    {
        $totalFeed = $this->total_feed_given;
        $latestGrowth = $this->fishGrowthLogs()->latest('date_recorded')->first();

        if (!$latestGrowth || $latestGrowth->avg_weight_gram == 0) return null;

        $totalBiomass = ($this->current_stock * $latestGrowth->avg_weight_gram) / 1000; // Convert to kg

        if ($totalBiomass == 0) return null;
        return round($totalFeed / $totalBiomass, 2);
    }

    // Perhitungan pertumbuhan rata-rata per minggu
    public function getWeeklyGrowthRateAttribute()
    {
        $growthLogs = $this->fishGrowthLogs()->orderBy('week_number')->get();

        if ($growthLogs->count() < 2) return null;

        $firstLog = $growthLogs->first();
        $lastLog = $growthLogs->last();

        $weeksDiff = $lastLog->week_number - $firstLog->week_number;
        if ($weeksDiff == 0) return null;

        $weightGrowth = $lastLog->avg_weight_gram - $firstLog->avg_weight_gram;

        return round($weightGrowth / $weeksDiff, 2);
    }

    // Perhitungan total pendapatan dari penjualan
    public function getTotalRevenueAttribute()
    {
        return round($this->sales()->sum('total_price'), 2);
    }

    // Estimasi berat total biomassa saat ini
    public function getCurrentBiomassAttribute()
    {
        $latestGrowth = $this->fishGrowthLogs()->latest('date_recorded')->first();
        if (!$latestGrowth) return 0;

        return round(($this->current_stock * $latestGrowth->avg_weight_gram) / 1000, 2); // dalam kg
    }

    // Status batch berdasarkan umur dan kondisi
    public function getStatusAttribute()
    {
        if ($this->current_stock <= 0) return 'completed';
        if ($this->age_in_days < 30) return 'juvenile';
        if ($this->age_in_days < 90) return 'growing';
        if ($this->age_in_days < 150) return 'mature';
        return 'ready_harvest';
    }
}
