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

        // first 3 courses with reviews count and average rating
        $courses = Course::where('is_published', true)
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->take(3)
            ->get();
        // first 3 lessons with reviews count and average rating
        $lessons = Lesson::where('is_published', true)
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->take(3)
            ->get();
        return view('home.home', compact('courses', 'lessons', 'homePage', 'reviews'));
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
