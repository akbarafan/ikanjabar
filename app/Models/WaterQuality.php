<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WaterQuality extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['pond_id', 'date_recorded', 'ph', 'temperature_c', 'do_mg_l', 'ammonia_mg_l', 'documentation_file', 'created_by'];
}
