<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasUuids;

    protected $fillable = [
        'full_name',
        'address',
        'branch_id',
        'phone_number',
        'email',
        'password',
        'is_verified',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_verified' => 'boolean',
        ];
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function fishBatches()
    {
        return $this->hasMany(FishBatch::class, 'created_by');
    }

    public function fishGrowthLogs()
    {
        return $this->hasMany(FishGrowthLog::class, 'created_by');
    }

    public function mortalities()
    {
        return $this->hasMany(Mortality::class, 'created_by');
    }

    public function waterQualities()
    {
        return $this->hasMany(WaterQuality::class, 'created_by');
    }

    public function feedings()
    {
        return $this->hasMany(Feeding::class, 'created_by');
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'created_by');
    }

    public function fishBatchTransfers()
    {
        return $this->hasMany(FishBatchTransfer::class, 'created_by');
    }
}
