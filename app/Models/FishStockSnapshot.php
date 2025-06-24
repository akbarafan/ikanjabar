<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FishStockSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'fish_batch_id',
        'current_stock',
    ];

    public function fishBatch()
    {
        return $this->belongsTo(FishBatch::class);
    }
}
