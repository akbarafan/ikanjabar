<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FishGrowthLog extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['fish_batch_id', 'week_number', 'avg_weight_gram', 'avg_length_cm', 'date_recorded', 'documentation_file', 'created_by'];
}
