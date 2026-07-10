<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

        public function run(): void
    {
        Skill::insert([

            // مهارت های عمومی
            ['name' => 'Microsoft Word'],
            ['name' => 'Microsoft Excel'],
            ['name' => 'Microsoft PowerPoint'],
            ['name' => 'تایپ فارسی'],
            ['name' => 'تایپ انگلیسی'],
            ['name' => 'مقاله نویسی'],
            ['name' => 'نگارش علمی'],
            ['name' => 'پژوهش'],
            ['name' => 'ترجمه انگلیسی'],
            ['name' => 'ترجمه عربی'],

            // مهندسی کامپیوتر
            ['name' => 'C'],
            ['name' => 'C++'],
            ['name' => 'C#'],
            ['name' => 'Java'],
            ['name' => 'Python'],
            ['name' => 'الگوریتم'],
            ['name' => 'ساختمان داده'],
            ['name' => 'شبکه های کامپیوتری'],
            ['name' => 'سیستم عامل'],
            ['name' => 'پایگاه داده'],

            // ارشد کامپیوتر
            ['name' => 'هوش مصنوعی'],
            ['name' => 'Machine Learning'],
            ['name' => 'Deep Learning'],
            ['name' => 'پردازش تصویر'],

            // برق
            ['name' => 'MATLAB'],
            ['name' => 'Proteus'],
            ['name' => 'Arduino'],

            // عمران
            ['name' => 'AutoCAD'],
            ['name' => 'ETABS'],
            ['name' => 'SAFE'],

            // معماری
            ['name' => 'Revit'],
            ['name' => 'SketchUp'],

            // مکانیک
            ['name' => 'SolidWorks'],

            // صنایع و مدیریت
            ['name' => 'Microsoft Project'],
            ['name' => 'Power BI'],
            ['name' => 'کنترل پروژه'],

            // حسابداری
            ['name' => 'سپیدار'],
            ['name' => 'هلو'],

            // روانشناسی و مشاوره
            ['name' => 'SPSS'],
            ['name' => 'تحلیل آماری'],

            // گرافیک
            ['name' => 'Photoshop'],
            ['name' => 'Illustrator'],

            // زبان
            ['name' => 'مکالمه انگلیسی'],

            // حقوق
            ['name' => 'تنظیم قرارداد'],

            // علوم ورزشی
            ['name' => 'برنامه تمرینی'],

        ]);
    }

}
