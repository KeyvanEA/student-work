<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;


class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles if they don't exist
        Role::firstOrCreate(['name' => 'user']);
        Role::firstOrCreate(['name' => 'admin']);

        // Find or create the admin user
        $admin = User::firstOrCreate(
            ['mobile' => '09938746857'],
            [
                'full_name' => 'Admin',
                'student_number' => 'ADMIN-001',
                'field_of_study' => 'Computer Engineering',
                'university_name' => 'StudentWork University',
                'bio' => 'System Administrator',
                'email' => null,
                'avatar' => null,
                'resume_file' => null,
                'is_active' => true,
            ]
        );

        // Make sure the user has admin role
        $admin->assignRole('admin');
    }
}
