<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\FishBatch;
use App\Models\FishType;
use App\Models\Pond;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Create Permissions
        $this->createPermissions();

        // 2. Create Roles
        $this->createRoles();

        // 3. Create Branches
        $this->createBranches();

        // 4. Create Users with Roles
        $this->createUsers();

        // 5. Create Fish Types
        $this->createFishTypes();

        // 6. Create Ponds
        $this->createPonds();

        // 7. Create Fish Batches
        $this->createFishBatches();

        // 8. Create Sample Data
        $this->createSampleData();

        $this->command->info('Database seeded successfully!');
        $this->command->info('Login credentials:');
        $this->command->info('Admin: admin@ikanjabar.com / password123');
        $this->command->info('Branch Manager: manager@ikanjabar.com / password123');
        $this->command->info('Student: student@ikanjabar.com / password123');
    }

    private function createPermissions()
    {
        $permissions = [
            // User Management
            'view users', 'create users', 'edit users', 'delete users', 'verify users',

            // Branch Management
            'view branches', 'create branches', 'edit branches', 'delete branches',

            // Fish Type Management
            'view fish-types', 'create fish-types', 'edit fish-types', 'delete fish-types',

            // Pond Management
            'view ponds', 'create ponds', 'edit ponds', 'delete ponds',

            // Fish Batch Management
            'view fish-batches', 'create fish-batches', 'edit fish-batches', 'delete fish-batches',

            // Water Quality Management
            'view water-qualities', 'create water-qualities', 'edit water-qualities', 'delete water-qualities',

            // Fish Growth Management
            'view fish-growth', 'create fish-growth', 'edit fish-growth', 'delete fish-growth',

            // Feeding Management
            'view feedings', 'create feedings', 'edit feedings', 'delete feedings',

            // Mortality Management
            'view mortalities', 'create mortalities', 'edit mortalities', 'delete mortalities',

            // Sales Management
            'view sales', 'create sales', 'edit sales', 'delete sales',

            // Transfer Management
            'view transfers', 'create transfers', 'edit transfers', 'delete transfers',

            // Report Management
            'view reports', 'export reports',

            // Dashboard Access
            'view admin-dashboard', 'view branch-dashboard', 'view student-dashboard',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        $this->command->info('Permissions created successfully!');
    }

    private function createRoles()
    {
        // Admin Role - Full Access
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        // Branch Manager Role
        $branchRole = Role::create(['name' => 'branches']);
        $branchRole->givePermissionTo([
            'view fish-types', 'create fish-types', 'edit fish-types', 'delete fish-types',
            'view ponds', 'create ponds', 'edit ponds', 'delete ponds',
            'view fish-batches', 'create fish-batches', 'edit fish-batches', 'delete fish-batches',
            'view water-qualities', 'create water-qualities', 'edit water-qualities', 'delete water-qualities',
            'view fish-growth', 'create fish-growth', 'edit fish-growth', 'delete fish-growth',
            'view feedings', 'create feedings', 'edit feedings', 'delete feedings',
            'view mortalities', 'create mortalities', 'edit mortalities', 'delete mortalities',
            'view sales', 'create sales', 'edit sales', 'delete sales',
            'view transfers', 'create transfers', 'edit transfers', 'delete transfers',
            'view reports', 'export reports',
            'view branch-dashboard',
        ]);

        // Student Role - Limited Access
        $studentRole = Role::create(['name' => 'student']);
        $studentRole->givePermissionTo([
            'view fish-types', 'view ponds', 'view fish-batches',
            'view water-qualities', 'create water-qualities',
            'view fish-growth', 'create fish-growth',
            'view feedings', 'create feedings',
            'view mortalities', 'create mortalities',
            'view sales', 'view transfers', 'view reports',
            'view student-dashboard',
        ]);

        $this->command->info('Roles created successfully!');
    }

    private function createBranches()
    {
        $branches = [
            [
                'name' => 'Cabang Jakarta',
                'location' => 'Jl. Merdeka No. 123, Jakarta Pusat, DKI Jakarta 10110',
                'contact_person' => '021-12345678',
                'pic_name' => 'Budi Santoso',
            ],
            [
                'name' => 'Cabang Bandung',
                'location' => 'Jl. Asia Afrika No. 45, Bandung, Jawa Barat 40111',
                'contact_person' => '022-87654321',
                'pic_name' => 'Siti Nurhaliza',
            ],
        ];

        foreach ($branches as $branch) {
            Branch::create($branch);
        }

        $this->command->info('Branches created successfully!');
    }

    private function createUsers()
    {
        $branches = Branch::all();
        $jakartaBranch = $branches->first();
        $bandungBranch = $branches->last();

        // Admin User
        $admin = User::create([
            'id' => Str::uuid(),
            'full_name' => 'Super Administrator',
            'email' => 'admin@ikanjabar.com',
            'phone_number' => '08111111111',
            'address' => 'Jl. Admin Utama No. 1, Jakarta Pusat',
            'branch_id' => $jakartaBranch->id,
            'password' => Hash::make('password123'),
            'is_verified' => true,
        ]);
        $admin->assignRole('admin');

        // Branch Manager Jakarta
        $managerJakarta = User::create([
            'id' => Str::uuid(),
            'full_name' => 'Manager Jakarta',
            'email' => 'manager@ikanjabar.com',
            'phone_number' => '08222222222',
            'address' => 'Jl. Manager Jakarta No. 10, Jakarta Pusat',
            'branch_id' => $jakartaBranch->id,
            'password' => Hash::make('password123'),
            'is_verified' => true,
        ]);
        $managerJakarta->assignRole('branches');

        // Branch Manager Bandung
        $managerBandung = User::create([
            'id' => Str::uuid(),
            'full_name' => 'Manager Bandung',
            'email' => 'manager.bandung@ikanjabar.com',
            'phone_number' => '08222222223',
            'address' => 'Jl. Manager Bandung No. 11, Bandung',
            'branch_id' => $bandungBranch->id,
            'password' => Hash::make('password123'),
            'is_verified' => true,
        ]);
        $managerBandung->assignRole('branches');

        // Student Jakarta
        $studentJakarta = User::create([
            'id' => Str::uuid(),
            'full_name' => 'Andi Pratama',
            'email' => 'student@ikanjabar.com',
            'phone_number' => '08333333333',
            'address' => 'Jl. Student Jakarta No. 20, Jakarta Pusat',
            'branch_id' => $jakartaBranch->id,
            'password' => Hash::make('password123'),
            'is_verified' => true,
        ]);
        $studentJakarta->assignRole('student');

        // Student Bandung
        $studentBandung = User::create([
            'id' => Str::uuid(),
            'full_name' => 'Sari Dewi',
            'email' => 'student.bandung@ikanjabar.com',
            'phone_number' => '08333333334',
            'address' => 'Jl. Student Bandung No. 21, Bandung',
            'branch_id' => $bandungBranch->id,
            'password' => Hash::make('password123'),
            'is_verified' => true,
        ]);
        $studentBandung->assignRole('student');

        $this->command->info('Users created successfully!');
    }

    private function createFishTypes()
    {
        $branches = Branch::all();
        $fishTypeNames = ['Nila Merah', 'Lele Dumbo'];

        foreach ($branches as $branch) {
            foreach ($fishTypeNames as $fishTypeName) {
                FishType::create([
                    'branch_id' => $branch->id,
                    'name' => $fishTypeName,
                    'description' => "Jenis ikan {$fishTypeName} unggul untuk budidaya di {$branch->name}",
                ]);
            }
        }

        $this->command->info('Fish types created successfully!');
    }

    private function createPonds()
    {
        $branches = Branch::all();

        foreach ($branches as $branch) {
            // 2 kolam per cabang
            for ($i = 1; $i <= 2; $i++) {
                $types = ['tanah', 'beton'];
                $type = $types[$i - 1];

                Pond::create([
                    'branch_id' => $branch->id,
                    'name' => "Kolam {$i} - {$branch->name}",
                    'code' => strtoupper(substr($branch->name, 7, 3)) . sprintf('%03d', $i),
                    'type' => $type,
                    'volume_liters' => $i == 1 ? 8000 : 12000,
                    'description' => "Kolam {$type} untuk budidaya ikan di {$branch->name}",
                ]);
            }
        }

        $this->command->info('Ponds created successfully!');
    }

    private function createFishBatches()
    {
        $ponds = Pond::all();
        $fishTypes = FishType::all();
        $users = User::all();

        foreach ($ponds as $pond) {
            // Ambil fish type untuk branch yang sama
            $branchFishTypes = $fishTypes->where('branch_id', $pond->branch_id);
            $fishType = $branchFishTypes->first();

            // Ambil user untuk branch yang sama (prioritas manager, fallback ke user manapun)
            $branchUsers = $users->where('branch_id', $pond->branch_id);
            $creator = $branchUsers->where('roles.0.name', 'branches')->first() ??
                      $branchUsers->first() ??
                      $users->first(); // fallback ke user pertama jika tidak ada

            if ($fishType && $creator) {
                FishBatch::create([
                    'pond_id' => $pond->id,
                    'fish_type_id' => $fishType->id,
                    'date_start' => now()->subDays(30),
                    'initial_count' => 1000,
                    'notes' => "Batch ikan {$fishType->name} periode " . now()->subDays(30)->format('M Y'),
                    'created_by' => $creator->id,
                ]);
            }
        }

        $this->command->info('Fish batches created successfully!');
    }

    private function createSampleData()
    {
        $fishBatches = FishBatch::all();
        $ponds = Pond::all();
        $users = User::all();

        foreach ($fishBatches as $batch) {
            // Ambil creator berdasarkan branch
            $branchUsers = $users->where('branch_id', $batch->pond->branch_id);
            $creator = $branchUsers->first() ?? $users->first();

            if (!$creator) continue;

            // Fish Growth Logs (2 entries per batch)
            for ($i = 1; $i <= 2; $i++) {
                DB::table('fish_growth_logs')->insert([
                    'fish_batch_id' => $batch->id,
                    'week_number' => $i,
                    'avg_weight_gram' => 10 + ($i * 15),
                    'avg_length_cm' => 5 + ($i * 2),
                    'date_recorded' => $batch->date_start->addWeeks($i - 1),
                    'created_by' => $creator->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Mortality (1 entry per batch)
            DB::table('mortalities')->insert([
                'fish_batch_id' => $batch->id,
                'date' => $batch->date_start->addDays(15),
                'dead_count' => 25,
                'cause' => 'Perubahan suhu mendadak',
                'created_by' => $creator->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Feeding (2 entries per batch)
            for ($i = 0; $i < 2; $i++) {
                $feedTypes = ['Pelet A1', 'Pelet Premium'];
                DB::table('feedings')->insert([
                    'fish_batch_id' => $batch->id,
                    'date' => $batch->date_start->addDays($i * 10),
                    'feed_type' => $feedTypes[$i % 2],
                    'feed_amount_kg' => 5 + ($i * 1),
                    'notes' => 'Pemberian pakan ' . ['pagi', 'sore'][$i % 2],
                    'created_by' => $creator->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Sales (1 entry per batch)
            $quantity = 200;
            $avgWeight = 0.3;
            $pricePerKg = 35000;
            $totalPrice = $quantity * $avgWeight * $pricePerKg;

            DB::table('sales')->insert([
                'fish_batch_id' => $batch->id,
                'date' => $batch->date_start->addDays(25),
                'quantity_fish' => $quantity,
                'avg_weight_per_fish_kg' => $avgWeight,
                'price_per_kg' => $pricePerKg,
                'buyer_name' => $batch->pond->branch_id == 1 ? 'PT Segar Ikan Jakarta' : 'CV Mina Jaya Bandung',
                'total_price' => $totalPrice,
                'created_by' => $creator->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Water Quality (1 entry per pond)
        foreach ($ponds as $pond) {
            $branchUsers = $users->where('branch_id', $pond->branch_id);
            $creator = $branchUsers->first() ?? $users->first();

            if (!$creator) continue;

            DB::table('water_qualities')->insert([
                'pond_id' => $pond->id,
                'date_recorded' => now()->subDays(5),
                'ph' => 7.2,
                'temperature_c' => 28,
                'do_mg_l' => 6.5,
                'ammonia_mg_l' => 0.1,
                'created_by' => $creator->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Fish Batch Transfer (1 transfer)
        if ($fishBatches->count() >= 2) {
            $sourceBatch = $fishBatches->first();
            $targetBatch = $fishBatches->where('pond.branch_id', $sourceBatch->pond->branch_id)
                ->where('id', '!=', $sourceBatch->id)
                ->first();

            if ($targetBatch) {
                $branchUsers = $users->where('branch_id', $sourceBatch->pond->branch_id);
                $creator = $branchUsers->first() ?? $users->first();

                if ($creator) {
                    DB::table('fish_batch_transfers')->insert([
                        'source_batch_id' => $sourceBatch->id,
                        'target_batch_id' => $targetBatch->id,
                        'transferred_count' => 100,
                        'date_transfer' => $sourceBatch->date_start->addDays(20),
                        'notes' => 'Transfer dilakukan untuk mengurangi kepadatan kolam',
                        'created_by' => $creator->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        $this->command->info('Sample data created successfully!');
    }
}
