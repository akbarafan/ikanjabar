<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['fish_batch_id', 'date', 'quantity_fish', 'avg_weight_per_fish_kg', 'price_per_kg', 'buyer_name', 'total_price', 'documentation_file', 'created_by'];
}
