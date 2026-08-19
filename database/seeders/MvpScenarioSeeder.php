<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Task;
use App\Models\Application;
use App\Models\Category;
use Illuminate\Database\Seeder;

class MvpScenarioSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()
            ->orderBy('id')
            ->take(5)
            ->get();

        if ($users->count() < 5) {
            throw new \RuntimeException(
                'برای اجرای این Seeder حداقل 5 کاربر لازم است.'
            );
        }

        $categories = Category::query()
            ->whereIn('slug', [
                'homework',
                'course-project',
                'powerpoint-design',
                'research-article',
                'typing-translation',
            ])
            ->get()
            ->keyBy('slug');

        if ($categories->count() < 5) {
            throw new \RuntimeException(
                'دسته‌بندی‌های موردنیاز پیدا نشدند.'
            );
        }

        $task1 = Task::create([
            'user_id' => $users[0]->id,
            'category_id' => $categories['homework']->id,
            'title' => 'حل تمرین ساختمان داده',
            'description' => 'حل کامل تمرین‌های ساختمان داده همراه با توضیح مراحل حل.',
            'budget' => 3500000,
            'deadline' => now()->addDays(5),
            'status' => 'open',
        ]);

        Application::create([
            'user_id' => $users[1]->id,
            'task_id' => $task1->id,
            'description' => 'این درس را قبلاً گذرانده‌ام و می‌توانم تمرین‌ها را کامل انجام دهم.',
            'status' => 'pending',
        ]);

        Application::create([
            'user_id' => $users[2]->id,
            'task_id' => $task1->id,
            'description' => 'در زمینه ساختمان داده تجربه دارم و آماده همکاری هستم.',
            'status' => 'pending',
        ]);

        $task2 = Task::create([
            'user_id' => $users[0]->id,
            'category_id' => $categories['course-project']->id,
            'title' => 'پروژه درسی Laravel',
            'description' => 'ساخت یک API ساده با Laravel برای پروژه درس برنامه‌سازی وب.',
            'budget' => 5000000,
            'deadline' => now()->addDays(7),
            'status' => 'open',
        ]);

        Application::create([
            'user_id' => $users[3]->id,
            'task_id' => $task2->id,
            'description' => 'با Laravel و REST API کار کرده‌ام و می‌توانم پروژه را انجام دهم.',
            'status' => 'pending',
        ]);

        $task3 = Task::create([
            'user_id' => $users[1]->id,
            'category_id' => $categories['powerpoint-design']->id,
            'title' => 'طراحی پاورپوینت ارائه دانشگاهی',
            'description' => 'طراحی یک پاورپوینت حرفه‌ای برای ارائه دانشگاهی.',
            'budget' => 1800000,
            'deadline' => now()->addDays(4),
            'status' => 'open',
        ]);

        Application::create([
            'user_id' => $users[2]->id,
            'task_id' => $task3->id,
            'description' => 'در طراحی پاورپوینت و ارائه دانشگاهی تجربه دارم.',
            'status' => 'pending',
        ]);

        Application::create([
            'user_id' => $users[4]->id,
            'task_id' => $task3->id,
            'description' => 'می‌توانم پاورپوینت را با طراحی مناسب و ساختار حرفه‌ای آماده کنم.',
            'status' => 'pending',
        ]);

        $task4 = Task::create([
            'user_id' => $users[2]->id,
            'category_id' => $categories['research-article']->id,
            'title' => 'تحقیق درباره هوش مصنوعی',
            'description' => 'تهیه یک تحقیق دانشگاهی درباره کاربردهای هوش مصنوعی.',
            'budget' => 2500000,
            'deadline' => now()->addDays(6),
            'status' => 'open',
        ]);

        Application::create([
            'user_id' => $users[0]->id,
            'task_id' => $task4->id,
            'description' => 'توانایی انجام تحقیق و جمع‌آوری منابع دانشگاهی را دارم.',
            'status' => 'pending',
        ]);

        $task5 = Task::create([
            'user_id' => $users[1]->id,
            'category_id' => $categories['typing-translation']->id,
            'title' => 'تایپ و آماده‌سازی گزارش',
            'description' => 'تایپ و مرتب‌سازی یک گزارش دانشگاهی.',
            'budget' => 1200000,
            'deadline' => now()->addDays(3),
            'status' => 'open',
        ]);

        Application::create([
            'user_id' => $users[3]->id,
            'task_id' => $task5->id,
            'description' => 'برای تایپ و آماده‌سازی فایل‌های دانشگاهی آماده همکاری هستم.',
            'status' => 'pending',
        ]);
    }
}
