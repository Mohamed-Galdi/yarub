<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CoursesController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $courses = $user->courses()->withPivot('created_at')->get();
        // dd($courses);
        return view('student.courses.index', compact('courses'));
    }
}
