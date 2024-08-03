<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $students_count = User::where('role', 'student')->count();
        $courses_count = Course::count();
        $lessons_count = Lesson::count();
        $courses_and_lessons_count = Course::count() + Lesson::count();
        $visitors_count = 0;
        $sales_count = 0;
        return view('admin.dashboard', compact('students_count', 'courses_count', 'lessons_count', 'courses_and_lessons_count', 'visitors_count', 'sales_count'));
    }
}
