<?php

namespace Database\Seeders;

use App\Models\Content;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContentVideoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Content::insert([
            [
                'title' => 'بلاغة اللغة العربية وتطبيقاتها الأدبية',
                'url' => 'course_videos/yarub_clip_1.mp4',
                'course_id' => 1, 
                'lesson_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'التحليل اللغوي للنصوص الأدبية والشعرية',
                'url' => 'course_videos/yarub_clip_2.mp4',
                'course_id' => 1,
                'lesson_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'أساليب الكتابة الإبداعية في الأدب العربي',
                'url' => 'course_videos/yarub_clip_3.mp4',
                'course_id' => 1,
                'lesson_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'النحو والصرف في النصوص التراثية العربية',
                'url' => 'lesson_videos/yarub_clip_4.mp4',
                'course_id' => null,
                'lesson_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'الأساليب البلاغية في الخطابة العربية القديمة',
                'url' => 'lesson_videos/yarub_clip_5.mp4',
                'course_id' => null,
                'lesson_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
