<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class CoursesController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $courses = $user->courses()->with('content')->withPivot('created_at')->get();
        // dd($courses);
        return view('student.courses.index', compact('courses'));
    }

    public function show(Course $course)
    {
        $cloudFrontDomain = env('AWS_CLOUDFRONT_DOMAIN');
        $course->load('content');
        $review = Review::where('reviewable_id', $course->id)->where('user_id', Auth::user()->id)->first();
        // dd($review);
        return view('student.courses.show', compact('course', 'cloudFrontDomain', 'review'));
    }

    public function rating(Request $request, $id)
    {
        $student = Auth::user();

        // Determine the model class based on the type input
        $reviewableType = $request->input('type') === 'course' ? \App\Models\Course::class : \App\Models\Lesson::class;

        // Find the reviewable entity (either Course or Lesson)
        $reviewable = $reviewableType::findOrFail($id);

        // Create or update the review
        $rating = Review::updateOrCreate(
            [
                'user_id' => $student->id,
                'reviewable_id' => $reviewable->id,
                'reviewable_type' => $reviewableType,
            ],
            [
                'rating' => $request->input('rating'),
                'comment' => $request->input('comment'),
            ]
        );

        Alert::success('تم تقييم الدورة بنجاح');
        return redirect()->back();
    }

}
