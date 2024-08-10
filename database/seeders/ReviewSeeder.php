<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReviewSeeder extends Seeder
{
    public function run()
    {
        $users = User::where('role', 'student')->get();
        $courses = Course::where('is_published', true)->get();
        $lessons = Lesson::where('is_published', true)->get();

        // Add 3 unique reviews to the first course
        $firstCourse = $courses->first();
        $this->addUniqueReviews($firstCourse, 3, $users);

        // Add unique reviews to a few other courses
        $otherCourses = $courses->slice(1, 3);
        foreach ($otherCourses as $course) {
            $this->addUniqueReviews($course, rand(1, 2), $users);
        }

        // Add unique reviews to a few lessons
        $someLessons = $lessons->random(3);
        foreach ($someLessons as $lesson) {
            $this->addUniqueReviews($lesson, 1, $users);
        }
    }

    private function addUniqueReviews($reviewable, $count, $users)
    {
        $availableUsers = $users->shuffle();
        $addedReviews = 0;

        foreach ($availableUsers as $user) {
            if ($addedReviews >= $count) {
                break;
            }

            $existingReview = Review::where('user_id', $user->id)
                ->where('reviewable_id', $reviewable->id)
                ->where('reviewable_type', get_class($reviewable))
                ->first();

            if (!$existingReview) {
                Review::create([
                    'user_id' => $user->id,
                    'reviewable_id' => $reviewable->id,
                    'reviewable_type' => get_class($reviewable),
                    'rating' => rand(4, 5),
                    'comment' => $this->getArabicComment(),
                ]);
                $addedReviews++;
            }
        }
    }

    private function getArabicComment()
    {
        $comments = [
            'هذا المساق متميز جدًا. أسلوب الشرح راقٍ ويناسب المستوى المتقدم.',
            'استفدت كثيرًا من هذا الدرس في فهم دقائق اللغة العربية وبلاغتها.',
            'المحتوى غني ومتعمق. أشعر أنني أرتقي بمستواي اللغوي مع كل درس.',
            'الأستاذة بارع في توضيح النقاط الدقيقة في قواعد النحو المتقدمة.',
            'هذا المساق أضاف لي الكثير في فهم الأدب العربي القديم والحديث.',
            'التمارين التطبيقية ممتازة وتساعد في ترسيخ المفاهيم البلاغية المعقدة.',
            'أسلوب تحليل النصوص الأدبية في هذا الدرس رائع ويفتح آفاقًا جديدة للفهم.',
            'استمتعت كثيرًا بالنقاشات العميقة حول تطور اللغة العربية عبر العصور.',
            'المساق يقدم رؤية شاملة وعميقة لعلوم اللغة العربية. ممتاز للباحثين والأكاديميين.',
            'الشرح الوافي لأوزان الشعر العربي وبحوره كان مفيدًا جدًا لفهمي للعروض.',
            'أعجبني كثيرًا تناول المساق للهجات العربية المختلفة وأصولها التاريخية.',
            'الدرس عن الإعجاز اللغوي في القرآن الكريم كان عميقًا ومؤثرًا.',
            'استفدت كثيرًا من شرح الفروق الدقيقة بين المترادفات في اللغة العربية.',
            'المساق يقدم تحليلًا رائعًا لتطور الأساليب البلاغية في الأدب العربي.',
            'الشرح المفصل لقواعد الإملاء المتقدمة كان مفيدًا جدًا لتحسين كتاباتي.',
        ];

        return $comments[array_rand($comments)];
    }
}
