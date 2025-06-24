<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FishBatch extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['pond_id', 'fish_type_id', 'date_start', 'initial_count', 'notes', 'documentation_file', 'created_by'];
}
