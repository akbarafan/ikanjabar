<?php

namespace Database\Seeders;

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
            'name' => 'Cabang Utama',
            'location' => 'Jl. Laut Selatan No. 88',
            'contact_person' => 'Pak Budi',
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
            'branch_id' => 1,
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
                'branch_id' => 1,
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
                'branch_id' => 1,
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
            'name' => 'Nila Merah',
            'description' => 'Jenis ikan nila unggul',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Fish Batches
        DB::table('fish_batches')->insert([
            'pond_id' => 1,
            'fish_type_id' => 1,
            'date_start' => now()->subDays(14),
            'initial_count' => 1000,
            'notes' => 'Batch awal bulan ini',
            'documentation_file' => null,
            'created_by' => $userId,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Fish Stock Snapshots
        DB::table('fish_stock_snapshots')->insert([
            'fish_batch_id' => 1,
            'current_stock' => 980,
            'updated_at' => now()
        ]);

        // Fish Growth Logs
        DB::table('fish_growth_logs')->insert([
            'fish_batch_id' => 1,
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
            'fish_batch_id' => 1,
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
            'pond_id' => 1,
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
            'fish_batch_id' => 1,
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
            'fish_batch_id' => 1,
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

        // Fish Batch Transfers
        DB::table('fish_batch_transfers')->insert([
            'source_batch_id' => 1,
            'target_batch_id' => 1,
            'transferred_count' => 200,
            'date_transfer' => now()->subDays(1),
            'notes' => 'Grading ke kolam 2',
            'created_by' => $userId,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
