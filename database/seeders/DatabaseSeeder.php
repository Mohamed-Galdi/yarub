<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Course;
use App\Models\HomePage;
use App\Models\Message;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            Demo::class, Students::class, Courses::class, Lessons::class, CourseTests::class, TestAttempt::class, CouponSeeder::class, CertificateSeeder::class,
            MessageSeeder::class, GuestMessagesSeeder::class, HomePageSeeder::class, HomePageReviewsSeeder::class, FAQseeder::class, AboutPageSeeder::class, PartnersPageSeeder::class, ContactPageSeeder::class, ReviewSeeder::class,
            SubscriptionSeeder::class, ContentVideoSeeder::class, PackageSeeder::class
        ]);

    }
}
