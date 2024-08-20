<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Lesson;

class Lessons extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $lessons = [
            [
                'title' => 'الخيل والليل لامروء القيس',
                'description' => 'شرح قصيدة امروء القيس من منهج الدراسات الأدبية',
                'type' => 'الصف الأول الثانوي',
                'monthly_price' => 30,
                'annual_price' => 300
            ],
            [
                'title' => 'التوابع',
                'description' => 'شرح درس التوابع من منهج الكفاية النحوية من الصف الثاني الثانوي',
                'type' => 'الصف الثاني الثانوي',
                'monthly_price' => 30,
                'annual_price' => 300
            ],
            [
                'title' => 'همزة الوصل',
                'description' => 'شرح درس همزة الوصل وكيفية كتابتها من منهج كفايات لغوية ٢ للصف الأول الثانوي',
                'type' => 'الصف الأول الثانوي',
                'monthly_price' => 30,
                'annual_price' => 300
            ],
            [
                'title' => 'المتممات',
                'description' => 'شرح درس المتممات من منهج كفايات لغوية ٢ للصف الأول الثانوي',
                'type' => 'الصف الثالث الثانوي',
                'monthly_price' => 30,
                'annual_price' => 300
            ],
        ];

        foreach ($lessons as $lesson) {
            Lesson::create($lesson);
        }
    }
}
