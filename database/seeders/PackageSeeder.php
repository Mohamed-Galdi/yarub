<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Package;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Ensure we have courses and lessons to work with
        $courses = Course::where('is_published', true)->get();
        $lessons = Lesson::where('is_published', true)->get();

        // Arabic words for package titles
        $arabicWords = ['الذهبية', 'الفضية', 'المتميزة', 'الشاملة', 'المتقدمة', 'الاحترافية'];

        // Arabic sentences for descriptions
        $arabicDescriptions = [
            'حزمة متكاملة تشمل مجموعة متنوعة من الدورات والدروس لتطوير مهاراتك',
            'مجموعة مختارة من أفضل المحتويات التعليمية لتحسين قدراتك المهنية',
            'حزمة شاملة تغطي جميع الجوانب الأساسية في مجال تخصصك',
            'مجموعة متميزة من الدورات والدروس المصممة لتلبية احتياجاتك التعليمية',
            'حزمة متقدمة للراغبين في تعميق معرفتهم وتطوير مهاراتهم بشكل احترافي',
            'مجموعة متكاملة من المواد التعليمية لتعزيز فرصك في سوق العمل'
        ];

        // Create 2 packages of each type
        foreach (['courses', 'lessons', 'mixed'] as $type) {
            for ($i = 0; $i < 2; $i++) {
                $package = new Package();
                $package->type = $type;
                $package->title = 'الحزمة ' . $arabicWords[array_rand($arabicWords)] . ' - ' . ($i + 1);
                $package->description = $arabicDescriptions[array_rand($arabicDescriptions)];
                $package->is_active = true;

                if ($type === 'courses') {
                    $package->price = rand(50, 150);
                    $package->monthly_price = null;
                    $package->annual_price = null;
                } else {
                    $package->price = null;
                    $package->monthly_price = rand(50, 100);
                    $package->annual_price = rand(400, 800);
                }

                $package->save();

                // Attach courses and/or lessons
                $itemsToAttach = rand(2, 7);

                if ($type === 'courses') {
                    $package->courses()->attach($courses->random(min($itemsToAttach, $courses->count()))->pluck('id')->toArray());
                } elseif ($type === 'lessons') {
                    $package->lessons()->attach($lessons->random(min($itemsToAttach, $lessons->count()))->pluck('id')->toArray());
                } else { // mixed
                    $coursesToAttach = rand(1, min(4, $courses->count()));
                    $lessonsToAttach = rand(1, min(4, $lessons->count()));

                    $package->courses()->attach($courses->random($coursesToAttach)->pluck('id')->toArray());
                    $package->lessons()->attach($lessons->random($lessonsToAttach)->pluck('id')->toArray());
                }
            }
        }
    }
}
