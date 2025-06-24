<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Feeding extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['fish_batch_id', 'date', 'feed_type', 'feed_amount_kg', 'notes', 'created_by'];
}
