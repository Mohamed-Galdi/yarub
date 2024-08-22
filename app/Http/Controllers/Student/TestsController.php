<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Test;
use App\Models\TestAttempt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class TestsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $availableTests = $user->availableTests();

        $testAttempts = TestAttempt::where('user_id', $user->id)
            ->whereIn('test_id', $availableTests->pluck('id'))
            ->get()
            ->keyBy('test_id');
        return view('student.tests.index', compact('availableTests', 'testAttempts'));
    }

    public function take(Test $test){
        $user = Auth::user();
    }
}
