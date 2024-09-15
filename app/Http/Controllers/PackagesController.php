<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\Request;

class PackagesController extends Controller
{
    public function packagesPage(Request $request)
    {
        $search = $request->input('search');
        $selectedType = $request->input('type');

        $query = app('App\Models\Package')->where('is_active', true)
            ->withCount('courses')
            ->withCount('lessons');

        if ($search) {
            $query->where('title', 'like', '%' . $search . '%');
        }

        if ($selectedType && $selectedType !== 'الكل') {
            $query->where('type', $selectedType);
        }

        $packages = $query->paginate(9);

        // Get all unique types from the filtered packages
        $availableTypes = app('App\Models\Package')->where('is_active', true)
            ->when($search, function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%');
            })
            ->pluck('type')
            ->unique()
            ->sort()
            ->values();
        // dd($availableTypes, $selectedType, $search, $packages);

        return view('home.packages.packages_list', compact('packages', 'search', 'availableTypes', 'selectedType'));
    }

    public function packagePage($package)
    {
        $package = Package::with([
            'courses' => function ($query) {
                $query->withCount('reviews')
                ->withAvg('reviews', 'rating');
            },
            'lessons' => function ($query) {
                $query->withCount('reviews')
                ->withAvg('reviews', 'rating');
            }
        ])->find($package);

        $courses = $package->courses->map(function ($course) {
            $course->reviews_avg_rating = $course->reviews_avg_rating ? round($course->reviews_avg_rating, 1) : null;
            return $course;
        });

        $lessons = $package->lessons->map(function ($lesson) {
            $lesson->reviews_avg_rating = $lesson->reviews_avg_rating ? round($lesson->reviews_avg_rating, 1) : null;
            return $lesson;
        });

        return view('home.packages.package_page', compact('package', 'courses', 'lessons'));
    }
}
