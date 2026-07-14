<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Default Admin Account
        User::firstOrCreate(
            ['email' => 'admin@eoms.gov.ph'],
            [
                'name'     => 'System Administrator',
                'password' => Hash::make('Admin@1234'),
                'role'     => 'admin',
                'position' => 'System Administrator',
            ]
        );

        $this->command->info('✅ Default admin account created:');
        $this->command->info('   Email:    admin@eoms.gov.ph');
        $this->command->info('   Password: Admin@1234');
        $this->command->warn('   ⚠ Change this password after first login!');
    }
}
