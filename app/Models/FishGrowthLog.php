<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FishGrowthLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'fish_batch_id',
        'week_number',
        'avg_weight_gram',
        'avg_length_cm',
        'date_recorded',
        'documentation_file',
        'created_by',
    ];

    protected $casts = [
        'date_recorded' => 'date',
    ];

    public function fishBatch()
    {
        return $this->belongsTo(FishBatch::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Perhitungan pertumbuhan dari minggu sebelumnya
    public function getWeightGrowthFromPreviousAttribute()
    {
        $previousLog = $this->fishBatch
            ->fishGrowthLogs()
            ->where('week_number', '<', $this->week_number)
            ->orderBy('week_number', 'desc')
            ->first();

        if (!$previousLog) return 0;

        return round($this->avg_weight_gram - $previousLog->avg_weight_gram, 2);
    }

    // Perhitungan persentase pertumbuhan
    public function getGrowthPercentageAttribute()
    {
        $previousLog = $this->fishBatch
            ->fishGrowthLogs()
            ->where('week_number', '<', $this->week_number)
            ->orderBy('week_number', 'desc')
            ->first();

        if (!$previousLog || $previousLog->avg_weight_gram == 0) return 0;

        return round((($this->avg_weight_gram - $previousLog->avg_weight_gram) / $previousLog->avg_weight_gram) * 100, 2);
    }
}
