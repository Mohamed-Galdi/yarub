<?php

namespace App\Http\Controllers;

use App\Models\AboutPage;
use App\Models\ContactPage;
use App\Models\Course;
use App\Models\FAQ;
use App\Models\HomePage;
use App\Models\HomePageReview;
use App\Models\Lesson;
use App\Models\Partners;
use Illuminate\Http\Request;

class HomePagesController extends Controller
{
    public function homePage()
    {
        $homePage = HomePage::first();
        $reviews = HomePageReview::all();

        // first 3 courses
        $courses = Course::where('is_published', true)->take(3)->get();
        // first 3 lessons
        $lessons = Lesson::where('is_published', true)->take(3)->get();

        return view('home.home', compact('courses', 'lessons', 'homePage', 'reviews'));
    }


    public function coursesPage(Request $request)
    {
        $search = $request->input('search');
        $selectedType = $request->input('type');

        $query = Course::where('is_published', true);

        if ($search) {
            $query->where('title', 'like', '%' . $search . '%');
        }

        if ($selectedType && $selectedType !== 'الكل') {
            $query->where('type', $selectedType);
        }

        $courses = $query->paginate(9);

        // Get all unique types from the filtered courses
        $availableTypes = $query->clone()->pluck('type')->unique()->sort()->values();

        return view('home.courses.courses_list', compact('courses', 'search', 'availableTypes', 'selectedType'));
    }


    public function lessonsPage(Request $request)
    {
        $search = $request->input('search');
        $selectedType = $request->input('type');

        $query = Lesson::where('is_published', true);

        if ($search) {
            $query->where('title', 'like', '%' . $search . '%');
        }

        if ($selectedType && $selectedType !== 'الكل') {
            $query->where('type', $selectedType);
        }

        $lessons = $query->paginate(9);

        // Get all unique types from the filtered lessons
        $availableTypes = $query->clone()->pluck('type')->unique()->sort()->values();

        return view('home.lessons.lessons_list', compact('lessons', 'search', 'availableTypes', 'selectedType'));
    }

    public function aboutPage()
    {
        $aboutPage = AboutPage::first();
        $partners = Partners::all();
        $faqs = FAQ::all();
        return view('home.about', compact('faqs', 'aboutPage', 'partners'));
    }

    public function contactPage()
    {
        $contactPage = ContactPage::first();
        return view('home.contact', compact('contactPage'));
    }
}
