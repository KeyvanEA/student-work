<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::insert([
            [
                'name' => 'حل تمرین',
                'slug' => 'homework',
            ],
            [
                'name' => 'پروژه درسی',
                'slug' => 'course-project',
            ],
            [
                'name' => 'طراحی پاورپوینت',
                'slug' => 'powerpoint-design',
            ],
            [
                'name' => 'تحقیق و مقاله',
                'slug' => 'research-article',
            ],
            [
                'name' => 'تایپ و ترجمه',
                'slug' => 'typing-translation',
            ],
            [
                'name' => 'تدریس خصوصی',
                'slug' => 'private-tutoring',
            ],
            [
                'name' => 'رفع اشکال درس',
                'slug' => 'problem-solving',
            ],
            [
                'name' => 'پروژه پایانی',
                'slug' => 'final-project',
            ],
            [
                'name' => 'پایان‌نامه',
                'slug' => 'thesis',
            ],
            [
                'name' => 'طراحی گرافیکی پروژه دانشگاهی',
                'slug' => 'graphic-design',
            ],
        ]);
    }
}
