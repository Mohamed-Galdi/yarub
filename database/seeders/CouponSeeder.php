<?php

namespace Database\Seeders;

use App\Models\Coupon;
use App\Models\Course;
use App\Models\Lesson;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run()
    {
        $courses = Course::all();
        $lessons = Lesson::all();

        $coupons = [
            [
                'code' => 'WELCOME10',
                'type' => 'percentage',
                'value' => 10,
                'applicable_to' => 'all',
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addMonths(3),
                'is_active' => true,
            ],
            [
                'code' => 'SUMMER2023',
                'type' => 'fixed',
                'value' => 50,
                'applicable_to' => 'courses',
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addMonths(2),
                'is_active' => true,
            ],
            [
                'code' => 'LESSONS25',
                'type' => 'percentage',
                'value' => 25,
                'applicable_to' => 'lessons',
                'start_date' => Carbon::now()->addWeek(),
                'end_date' => Carbon::now()->addMonths(1),
                'is_active' => false,
            ],
            [
                'code' => 'NEWYEAR2024',
                'type' => 'percentage',
                'value' => 30,
                'applicable_to' => 'specific',
                'start_date' => Carbon::create(2024, 1, 1),
                'end_date' => Carbon::create(2024, 1, 31),
                'is_active' => true,
            ],
            [
                'code' => 'FLASHSALE',
                'type' => 'fixed',
                'value' => 100,
                'applicable_to' => 'specific',
                'start_date' => Carbon::now()->addDays(7),
                'end_date' => Carbon::now()->addDays(8),
                'is_active' => true,
            ],
        ];

        foreach ($coupons as $couponData) {
            $coupon = Coupon::create($couponData);

            if ($couponData['applicable_to'] === 'specific') {
                // Attach random courses and lessons
                $coupon->courses()->attach($courses->random(2));
                $coupon->lessons()->attach($lessons->random(3));
            }
        }
    }
}