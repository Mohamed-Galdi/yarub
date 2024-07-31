<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Certificate;
use App\Models\User;
use App\Models\Course;
use App\Models\Lesson;

class CertificateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Make sure we have users, courses, and lessons
        $users = User::where('role', 'student')->get();
        $courses = Course::all();
        $lessons = Lesson::all();

        // Create 4 certificates
        for ($i = 0; $i < 4; $i++) {
            $certificate = new Certificate();

            // Assign a random user
            $certificate->user_id = $users->random()->id;

            // Randomly choose between course and lesson
            if (rand(0, 1) == 0) {
                $certificate->course_id = $courses->random()->id;
            } else {
                $certificate->lesson_id = $lessons->random()->id;
            }
            $certificate->created_at = now()->subDays($i +1);

            $certificate->save();
        }
    }
}
