<?php

namespace Database\Seeders;

use App\Models\HomePageReview;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HomePageReviewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reviews = [
            [
                'review' => 'دورة ممتازة! المحتوى غني ومفيد للغاية. أنصح الجميع بالالتحاق بها.',
                'reviewer_name' => 'محمد المهدي',
                'reviewer_image' => 'storage/reviewers/man-1.webp',
                'stars' => 5
            ],
            [
                'review' => 'محتوى جميل ومفيد للغاية. أنصح الجميع بالالتحاق بها.',
                'reviewer_name' => 'فاطمة الزهراء',
                'reviewer_image' => 'storage/reviewers/woman-1.webp',
                'stars' => 5
            ],
            [
                'review' => 'استفدت كثيرًا من هذه الدورة، فقد حسّنت مستواي في اللغة العربية بشكل ملحوظ.',
                'reviewer_name' => 'أحمد السعدي',
                'reviewer_image' => 'storage/reviewers/man-2.webp',
                'stars' => 5
            ],
            [
                'review' => 'الدروس مكثفة ولكنها مفيدة جدًا. ساعدتني كثيرًا في تحسين كتابتي وقراءتي.',
                'reviewer_name' => 'ريم الحربي',
                'reviewer_image' => 'storage/reviewers/woman-2.webp',
                'stars' => 5
            ],
            [
                'review' => 'الدورة تحتوي على مواد تعليمية ممتازة وتطبيقات عملية تعزز الفهم.',
                'reviewer_name' => 'خالد البدر',
                'reviewer_image' => 'storage/reviewers/man-3.webp',
                'stars' => 5
            ],
            [
                'review' => 'الدورة تحتوي على مواد تعليمية ممتازة وتطبيقات عملية تعزز الفهم.',
                'reviewer_name' => 'ليلى الطيب',
                'reviewer_image' => 'storage/reviewers/woman-3.webp',
                'stars' => 5
            ],
            [
                'review' => 'أنصح بشدة بهذه الدورة لمن يرغب في تعزيز مهاراته اللغوية.',
                'reviewer_name' => 'سعيد الهاشمي',
                'reviewer_image' => 'storage/reviewers/man-4.webp',
                'stars' => 5
            ],
            [
                'review' => 'المنصة سهلة الاستخدام والمحتوى التعليمي رائع. شكرًا لكم!',
                'reviewer_name' => 'منى الحارثي',
                'reviewer_image' => 'storage/reviewers/woman-4.webp',
                'stars' => 5
            ],
            [
                'review' => 'أحببت الطريقة التي يتم بها تقديم الدروس، فهي تتسم بالوضوح والتفصيل.',
                'reviewer_name' => 'يوسف الأنصاري',
                'reviewer_image' => 'storage/reviewers/man-5.webp',
                'stars' => 5
            ],
        ];

        HomePageReview::insert($reviews);
    }
}
