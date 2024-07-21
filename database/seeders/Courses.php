<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;

class Courses extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $courses = [
            [
                'title' => 'مقدمة يَعرُب في التأسيس للقدرات - اللفظي',
                'description' => 'دورة تفصيلية وتعريفية باختبارات القدرات حسب اشتراطات قياس',
                'price' => 30
            ],
            [
                'title' => 'التعريف بأقسام اختبار  القدرات -اللفظي  ( التناظر اللفظي )',
                'description' => 'التعريف بأقسام الاختبار اللفظي وشرح تفصيلي للتناظر اللفظي مع إيراد أمثلة توضيحية',
                'price' => 30
            ],
            [
                'title' => 'المفردة الشاذة ( الارتباط والاختلاف )',
                'description' => 'شرح تفصيلي لقسم المفردة الشاذة ( الارتباط والاختلاف ) مع تدريبات شاملة',
                'price' => 30
            ],
            [
                'title' => 'إكمال الجمل الناقصة',
                'description' => 'شرح تفصيلي لقسم ( إكمال الجمل الناقصة ) مع تدريبات شاملة',
                'price' => 30
            ],
            [
                'title' => 'الخطأ السياقي',
                'description' => 'شرح تفصيلي لقسم ( الخطأ السياقي ) مع تدريبات شاملة',
                'price' => 30
            ],
        ];

        foreach ($courses as $course) {
            Course::create($course);
        }
    }
}
