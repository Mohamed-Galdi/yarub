<?php

namespace App\Http\Controllers;


use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;

class CoursesPageController extends Controller
{
    public function coursesPage(Request $request)
    {
        $search = $request->input('search');
        $selectedType = $request->input('type');

        $query = Course::where('is_published', true)
            ->withCount('reviews')
            ->withAvg('reviews', 'rating');

        if ($search) {
            $query->where('title', 'like', '%' . $search . '%');
        }

        if ($selectedType && $selectedType !== 'الكل') {
            $query->where('type', $selectedType);
        }

        $courses = $query->paginate(9);

        // Round the average rating for each course
        $courses->getCollection()->transform(function ($course) {
            $course->reviews_avg_rating = $course->reviews_avg_rating ? round($course->reviews_avg_rating, 1) : null;
            return $course;
        });

        // Get all unique types from the filtered courses
        $availableTypes = Course::where('is_published', true)
            ->when($search, function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%');
            })
            ->pluck('type')
            ->unique()
            ->sort()
            ->values();

        return view('home.courses.courses_list', compact('courses', 'search', 'availableTypes', 'selectedType'));
    }

    public function coursePage($course)
    {
        $course = Course::with('reviews', 'reviews.user')->find($course);

        $suggestions = Course::where('is_published', true)
            ->where('id', '!=', $course->id)
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->inRandomOrder()
            ->take(3)
            ->get()
            ->map(function ($course) {
                $course->reviews_avg_rating = $course->reviews_avg_rating ? round($course->reviews_avg_rating, 1) : null;
                return $course;
            });



        return view('home.courses.course_page', compact('course', 'suggestions'));
    }
}
