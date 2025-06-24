<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FishBatchTransfer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'source_batch_id',
        'target_batch_id',
        'transferred_count',
        'date_transfer',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'date_transfer' => 'date',
    ];

    public function sourceBatch()
    {
        return $this->belongsTo(FishBatch::class, 'source_batch_id');
    }

    public function targetBatch()
    {
        return $this->belongsTo(FishBatch::class, 'target_batch_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Perhitungan persentase transfer dari batch sumber
    public function getTransferPercentageAttribute()
    {
        $sourceStock = $this->sourceBatch->current_stock + $this->transferred_count;
        if ($sourceStock == 0) return 0;

        return round(($this->transferred_count / $sourceStock) * 100, 2);
    }

    // Status transfer berdasarkan jumlah
    public function getTransferStatusAttribute()
    {
        $percentage = $this->transfer_percentage;

        if ($percentage <= 10) return 'minor';
        if ($percentage <= 30) return 'moderate';
        if ($percentage <= 50) return 'major';
        return 'massive';
    }
}
