<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FishBatchTransfer extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['source_batch_id', 'target_batch_id', 'transferred_count', 'date_transfer', 'notes', 'created_by'];
}
