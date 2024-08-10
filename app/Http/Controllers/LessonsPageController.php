<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;

class LessonsPageController extends Controller
{

    public function lessonsPage(Request $request)
    {
        $search = $request->input('search');
        $selectedType = $request->input('type');

        $query = Lesson::where('is_published', true)
            ->withCount('reviews')
            ->withAvg('reviews', 'rating');

        if ($search) {
            $query->where('title', 'like', '%' . $search . '%');
        }

        if ($selectedType && $selectedType !== 'الكل') {
            $query->where('type', $selectedType);
        }

        $lessons = $query->paginate(9);

        // Round the average rating for each lesson
        $lessons->getCollection()->transform(function ($lesson) {
            $lesson->reviews_avg_rating = $lesson->reviews_avg_rating ? round($lesson->reviews_avg_rating, 1) : null;
            return $lesson;
        });

        // Get all unique types from the filtered lessons
        $availableTypes = Lesson::where('is_published', true)
            ->when($search, function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%');
            })
            ->pluck('type')
            ->unique()
            ->sort()
            ->values();

        return view('home.lessons.lessons_list', compact('lessons', 'search', 'availableTypes', 'selectedType'));
    }

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
