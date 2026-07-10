<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'full_name' => 'علی احمدی',
            'mobile' => '09120000001',
            'email' => 'ali@test.com',
            'student_number' => '401000001',
        ]);

        User::factory()->create([
            'full_name' => 'رضا محمدی',
            'mobile' => '09120000002',
            'email' => 'reza@test.com',
            'student_number' => '401000002',
        ]);

        User::factory()->create([
            'full_name' => 'حسن کریمی',
            'mobile' => '09120000003',
            'email' => 'hasan@test.com',
            'student_number' => '401000003',
        ]);

        User::factory()->create([
            'full_name' => 'حسین رضایی',
            'mobile' => '09120000004',
            'email' => 'hossein@test.com',
            'student_number' => '401000004',
        ]);

        User::factory()->create([
            'full_name' => 'مهدی موسوی',
            'mobile' => '09120000005',
            'email' => 'mahdi@test.com',
            'student_number' => '402000001',
        ]);

        User::factory()->create([
            'full_name' => 'محمد امیری',
            'mobile' => '09120000006',
            'email' => 'mohammad@test.com',
            'student_number' => '402000002',
        ]);

        User::factory()->create([
            'full_name' => 'امیر حسینی',
            'mobile' => '09120000007',
            'email' => 'amir@test.com',
            'student_number' => '402000003',
        ]);

        User::factory()->create([
            'full_name' => 'پوریا اکبری',
            'mobile' => '09120000008',
            'email' => 'pouria@test.com',
            'student_number' => '402000004',
        ]);

        User::factory()->create([
            'full_name' => 'آرمان رستمی',
            'mobile' => '09120000009',
            'email' => 'arman@test.com',
            'student_number' => '403000001',
        ]);

        User::factory()->create([
            'full_name' => 'نوید صادقی',
            'mobile' => '09120000010',
            'email' => 'navid@test.com',
            'student_number' => '403000002',
        ]);

        User::factory()->create([
            'full_name' => 'فاطمه محمدی',
            'mobile' => '09120000011',
            'email' => 'fateme@test.com',
            'student_number' => '403000003',
        ]);

        User::factory()->create([
            'full_name' => 'زهرا احمدی',
            'mobile' => '09120000012',
            'email' => 'zahra@test.com',
            'student_number' => '403000004',
        ]);

        User::factory()->create([
            'full_name' => 'نگار کریمی',
            'mobile' => '09120000013',
            'email' => 'negar@test.com',
            'student_number' => '404000001',
        ]);

        User::factory()->create([
            'full_name' => 'سارا رضایی',
            'mobile' => '09120000014',
            'email' => 'sara@test.com',
            'student_number' => '404000002',
        ]);

        User::factory()->create([
            'full_name' => 'مریم اکبری',
            'mobile' => '09120000015',
            'email' => 'maryam@test.com',
            'student_number' => '404000003',
        ]);

        User::factory()->create([
            'full_name' => 'ریحانه موسوی',
            'mobile' => '09120000016',
            'email' => 'reyhane@test.com',
            'student_number' => '404000004',
        ]);

        User::factory()->create([
            'full_name' => 'هانیه قاسمی',
            'mobile' => '09120000017',
            'email' => 'haniye@test.com',
            'student_number' => '405000001',
        ]);

        User::factory()->create([
            'full_name' => 'الناز مرادی',
            'mobile' => '09120000018',
            'email' => 'elnaz@test.com',
            'student_number' => '405000002',
        ]);

        User::factory()->create([
            'full_name' => 'نرگس شریفی',
            'mobile' => '09120000019',
            'email' => 'narges@test.com',
            'student_number' => '405000003',
        ]);

        User::factory()->create([
            'full_name' => 'آیدا سلطانی',
            'mobile' => '09120000020',
            'email' => 'ayda@test.com',
            'student_number' => '405000004',
        ]);
    }
}
