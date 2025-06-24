<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mortality extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['fish_batch_id', 'date', 'dead_count', 'cause', 'documentation_file', 'created_by'];
}
