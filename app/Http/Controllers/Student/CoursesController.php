<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $course->load('content');
        return view('student.courses.show', compact('course'));
    }
}
