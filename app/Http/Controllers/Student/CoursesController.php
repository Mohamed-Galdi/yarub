<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Review;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;
use Jubaer\Zoom\Facades\Zoom;

class CoursesController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $courses = $user->courses()->with('content', 'liveSession')->withPivot('created_at')->get();
        // dd($courses);
        return view('student.courses.index', compact('courses'));
    }

    public function show(Course $course)
    {
        $user = Auth::user();
        // check if the course is in user courses
        if (!$user->courses()->pluck('course_id')->contains($course->id)) {
            Alert::warning(' انت لست مشتركا بعد في هذه الدورة');
            return redirect()->route('student.courses.index');
        }

        $cloudFrontDomain = env('AWS_CLOUDFRONT_DOMAIN');
        $course->load('content');
        $review = Review::where('reviewable_id', $course->id)->where('user_id', Auth::user()->id)->first();
        // dd($review);
        return view('student.courses.show', compact('course', 'cloudFrontDomain', 'review'));
    }

    public function showLiveSession(Course $course)
    {
        $user = Auth::user();
        if (!$user->courses()->pluck('course_id')->contains($course->id)) {
            Alert::warning(' انت لست مشتركا بعد في هذه الدورة');
            return redirect()->route('student.courses.index');
        }
        $course->load('liveSession');
        $meeting = Zoom::getMeeting($course->liveSession->zoom_meeting_id);

        // // dd($meeting);

        $meetingData = [
            'meetingNumber' => $meeting['data']['id'],
            'status' => $meeting['data']['status'],
            'password' => $meeting['data']['password'],
            'userName' => $user->name,
            'userEmail' => $user->email,
            'joinUrl' => $meeting['data']['join_url'],
        ];
        return view('student.courses.showLiveSession', compact('course' , 'meetingData'));
    }

    public function livePAge(Course $course)
    {
        $user = Auth::user();
        if (!$user->courses()->pluck('course_id')->contains($course->id)) {
            Alert::warning(' انت لست مشتركا بعد في هذه الدورة');
            return redirect()->route('student.courses.index');
        }
        $course->load('liveSession');
        $meeting = Zoom::getMeeting($course->liveSession->zoom_meeting_id);

        // dd($meeting);

        $meetingData = [
            'meetingNumber' => $meeting['data']['id'],
            'status' => $meeting['data']['status'],
            'password' => $meeting['data']['password'],
            'userName' => $user->name,
            'userEmail' => $user->email,
            'joinUrl' => $meeting['data']['join_url'],
        ];

        $signature = $this->generateSignature($meetingData['meetingNumber'], 0); // 0 for participant role

        return view('student.courses.livePage', compact('course', 'meetingData', 'signature'));


    }

    private function generateSignature($meetingNumber, $role = 0)
    {
        $sdkKey = config('services.zoom.client_id');
        $sdkSecret = config('services.zoom.client_secret');

        $iat = time();
        $exp = $iat + 60 * 60 * 2;

        $payload = array(
            "sdkKey" => $sdkKey,
            "mn" => $meetingNumber,
            "role" => $role,
            "iat" => $iat,
            "exp" => $exp
        );

        return JWT::encode($payload, $sdkSecret, 'HS256');
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
