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
        'created_by'
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

    public function mortalities()
    {
        return $this->hasMany(Mortality::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function transfersOut()
    {
        return $this->hasMany(FishBatchTransfer::class, 'source_batch_id');
    }

    public function transfersIn()
    {
        return $this->hasMany(FishBatchTransfer::class, 'target_batch_id');
    }

    public function getCurrentStockAttribute()
    {
        $dead = $this->mortalities()->sum('dead_count');
        $sold = $this->sales()->sum('quantity_fish');
        $transferredOut = $this->transfersOut()->sum('transferred_count');
        $transferredIn = $this->transfersIn()->sum('transferred_count');

        return max(0, $this->initial_count + $transferredIn - $transferredOut - $dead - $sold);
    }


    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
