<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Payment;
use App\Models\CourseSubscription;
use App\Models\LessonSubscription;
use App\Models\Coupon;
use Faker\Factory as Faker;

class SubscriptionSeeder extends Seeder
{
    const COURSE_PRICE = 30;
    const LESSON_MONTHLY_PRICE = 30;
    const LESSON_ANNUAL_PRICE = 300;

    public function run()
    {
        $faker = Faker::create();

        $userIds = User::where('role', '!=', 'admin')->pluck('id')->toArray();
        $courses = Course::where('is_published', true)->get();
        $lessons = Lesson::where('is_published', true)->get();
        $coupons = Coupon::where('is_active', true)->get();

        foreach ($userIds as $userId) {
            $this->createSubscription($faker, $userId, $courses->random(), $coupons, true);
            $this->createSubscription($faker, $userId, $lessons->random(), $coupons, false);
        }
    }

    private function createSubscription($faker, $userId, $item, $coupons, $isCourse)
    {
        DB::transaction(function () use ($faker, $userId, $item, $coupons, $isCourse) {
            $useCoupon = $faker->boolean(30);

            $payment = Payment::create([
                'user_id' => $userId,
                'payment_id' => $faker->uuid,
                'payment_status' => 'paid',
                'payment_message' => 'APPROVED',
                'original_amount' => 0,
                'payment_amount' => 0,
            ]);

            $discount = 0;
            if ($useCoupon && $coupons->isNotEmpty()) {
                $coupon = $coupons->random();
                $discount = $faker->randomElement([5, 10, 15, 20, 25]);
                $payment->coupon_used = $coupon->code;
                $payment->coupon_reduction = $discount;

                $coupon->used_count += 1;
                $coupon->save();
            }

            if ($isCourse) {
                $payment->original_amount = self::COURSE_PRICE;
                $payment->payment_amount = self::COURSE_PRICE - $discount;

                $courseSub = new CourseSubscription();
                $courseSub->user_id = $userId;
                $courseSub->course_id = $item->id;
                $courseSub->is_active = true;
                $courseSub->payment_id = $payment->id;
                $courseSub->cost = self::COURSE_PRICE - $discount;
                $courseSub->save();
            } else {
                $isAnnual = $faker->boolean;
                $originalAmount = $isAnnual ? self::LESSON_ANNUAL_PRICE : self::LESSON_MONTHLY_PRICE;
                $payment->original_amount = $originalAmount;
                $payment->payment_amount = $originalAmount - $discount;

                $lessonSub = new LessonSubscription();
                $lessonSub->user_id = $userId;
                $lessonSub->lesson_id = $item->id;
                $lessonSub->sub_plan = $isAnnual ? 'annual' : 'monthly';
                $lessonSub->is_active = true;
                $lessonSub->payment_id = $payment->id;
                $lessonSub->cost = $originalAmount - $discount;
                $lessonSub->save();
            }

            $payment->save();
        });
    }
}
