<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class LessonsController extends Controller
{
    
    public function index()
    {
        $user = Auth::user();
        $lessons = $user->activeLessonsSub()->with('content')->get();
        return view('student.lessons.index', compact('lessons'));
    }

    public function show(Lesson $lesson)
    {
        $user = Auth::user();
        // check if the lesson is in user lessons
        if (!$user->lessons()->pluck('lesson_id')->contains($lesson->id)) {
            Alert::warning(' انت لست مشتركا بعد في هذه الشرح');
            return redirect()->route('student.lessons.index');
        }
        $cloudFrontDomain = env('AWS_CLOUDFRONT_DOMAIN');
        $lesson->load('content');
        $review = Review::where('reviewable_id', $lesson->id)->where('user_id', Auth::user()->id)->first();
        // dd($review);
        return view('student.lessons.show', compact('lesson', 'cloudFrontDomain', 'review'));
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
