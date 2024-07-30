<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Course;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([Demo::class, Students::class, Courses::class, Lessons::class, CourseTests::class, TestAttempt::class, CouponSeeder::class]);
    }
}
