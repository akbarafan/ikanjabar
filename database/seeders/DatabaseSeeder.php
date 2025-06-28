<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\FishBatch;
use App\Models\FishType;
use App\Models\Pond;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Branches
        DB::table('branches')->insert([
            'name' => 'Cabang Sukabumi',
            'location' => 'Jl. Laut Selatan No. 88',
            'contact_person' => '081234567890',
            'pic_name' => 'Budi Santoso',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Users
        $userId = (string) Str::uuid();
        DB::table('users')->insert([
            'id' => $userId,
            'full_name' => 'Admin Utama',
            'address' => 'Jl. Ikan Mas No. 5',
            'branch_id' => Branch::first()->id,
            'phone_number' => '081234567890',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'is_verified' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Ponds
        DB::table('ponds')->insert([
            [
                'branch_id' => Branch::first()->id,
                'name' => 'Kolam 1',
                'code' => 'KLM001',
                'type' => 'beton',
                'volume_liters' => 10000,
                'description' => 'Kolam utama',
                'documentation_file' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'branch_id' => Branch::first()->id,
                'name' => 'Kolam 2',
                'code' => 'KLM002',
                'type' => 'terpal',
                'volume_liters' => 8000,
                'description' => 'Kolam pendederan',
                'documentation_file' => null,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        // Fish Types
        DB::table('fish_types')->insert([
            'branch_id' => Branch::first()->id,
            'name' => 'Nila Merah',
            'description' => 'Jenis ikan nila unggul',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Fish Batches
        DB::table('fish_batches')->insert([
            'pond_id' => Pond::first()->id,
            'fish_type_id' => FishType::first()->id,
            'date_start' => now()->subDays(14),
            'initial_count' => 1000,
            'notes' => 'Batch awal bulan ini',
            'documentation_file' => null,
            'created_by' => $userId,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Fish Growth Logs
        DB::table('fish_growth_logs')->insert([
            'fish_batch_id' => FishBatch::first()->id,
            'week_number' => 2,
            'avg_weight_gram' => 15,
            'avg_length_cm' => 6,
            'date_recorded' => now()->subDays(1),
            'documentation_file' => null,
            'created_by' => $userId,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Mortalities
        DB::table('mortalities')->insert([
            'fish_batch_id' => FishBatch::first()->id,
            'date' => now()->subDays(2),
            'dead_count' => 10,
            'cause' => 'Perubahan suhu mendadak',
            'documentation_file' => null,
            'created_by' => $userId,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Water Quality
        DB::table('water_qualities')->insert([
            'pond_id' => Pond::first()->id,
            'date_recorded' => now()->subDays(1),
            'ph' => 7.2,
            'temperature_c' => 28,
            'do_mg_l' => 6.5,
            'ammonia_mg_l' => 0.1,
            'documentation_file' => null,
            'created_by' => $userId,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Feedings
        DB::table('feedings')->insert([
            'fish_batch_id' => FishBatch::first()->id,
            'date' => now()->subDay(),
            'feed_type' => 'Pelet A1',
            'feed_amount_kg' => 5,
            'notes' => 'Pagi hari',
            'created_by' => $userId,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Sales
        DB::table('sales')->insert([
            'fish_batch_id' => FishBatch::first()->id,
            'date' => now(),
            'quantity_fish' => 100,
            'avg_weight_per_fish_kg' => 0.2,
            'price_per_kg' => 30000,
            'buyer_name' => 'PT Segar Ikan',
            'total_price' => 600000,
            'documentation_file' => null,
            'created_by' => $userId,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
