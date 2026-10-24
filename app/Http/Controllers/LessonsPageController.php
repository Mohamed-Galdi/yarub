<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;

class LessonsPageController extends Controller
{

    public function coursePage($lesson)
    {
        $lesson = Lesson::with('reviews', 'reviews.user')->find($lesson);

        $suggestions =
            Lesson::where('is_published', true)
            ->where('id', '!=', $lesson->id)
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->inRandomOrder()
            ->take(3)
            ->get()
            ->map(function ($lesson) {
                $lesson->reviews_avg_rating = $lesson->reviews_avg_rating ? round($lesson->reviews_avg_rating, 1) : null;
                return $lesson;
            });

        return view('home.lessons.lesson_page', compact('lesson', 'suggestions'));
    }
}
