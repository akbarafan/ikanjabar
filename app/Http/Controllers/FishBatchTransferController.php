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
        'created_by'
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

    // Boot method untuk update stok otomatis
    protected static function boot()
    {
        parent::boot();

        static::created(function ($transfer) {
            // Update snapshot stok setelah transfer
            $transfer->updateStockSnapshots();
        });

        static::updated(function ($transfer) {
            $transfer->updateStockSnapshots();
        });

        static::deleted(function ($transfer) {
            $transfer->updateStockSnapshots();
        });
    }

    private function updateStockSnapshots()
    {
        // Update stok untuk source batch
        $sourceCurrentStock = $this->calculateBatchStock($this->sourceBatch);
        FishStockSnapshot::updateOrCreate(
            ['fish_batch_id' => $this->source_batch_id],
            ['current_stock' => $sourceCurrentStock]
        );

        // Update stok untuk target batch
        $targetCurrentStock = $this->calculateBatchStock($this->targetBatch);
        FishStockSnapshot::updateOrCreate(
            ['fish_batch_id' => $this->target_batch_id],
            ['current_stock' => $targetCurrentStock]
        );
    }

    private function calculateBatchStock($batch)
    {
        if (!$batch) return 0;

        $dead = $batch->mortalities()->sum('dead_count');
        $sold = $batch->sales()->sum('quantity_fish');
        $transferredOut = self::where('source_batch_id', $batch->id)->sum('transferred_count');
        $transferredIn = self::where('target_batch_id', $batch->id)->sum('transferred_count');

        return max(0, $batch->initial_count + $transferredIn - $transferredOut - $dead - $sold);
    }
}
