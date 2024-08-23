<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Test;
use App\Models\TestAttempt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

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

        // check if the test is in user tests
        $user = Auth::user();
        $availableTests = $user->availableTests();
        if (!$availableTests->pluck('id')->contains($test->id)) {
            Alert::warning('لا يمكنك إجتياز هذا الإختبار حاليا');
            return redirect()->route('student.tests.index');
        }

        // check if the user has already taken the test
        if(TestAttempt::where('user_id', Auth::user()->id)->where('test_id', $test->id)->exists()){
            Alert::warning('سبق لك إجتياز هذا الإختبار بالفعل');
            return redirect()->route('student.tests.index');
        }
        

        $test->load('questions');
        return view('student.tests.take_test', compact('test'));
    }

    public function submit(Request $request, Test $test)
    {
        // check if the user has already taken the test
        if (TestAttempt::where('user_id', Auth::user()->id)->where('test_id', $test->id)->exists()) {
            Alert::warning('سبق لك إجتياز هذا الإختبار بالفعل');
            return redirect()->route('student.tests.index');
        }

        $user = Auth::user();

        // Get the correct answers
        $correctAnswers = $test->questions->pluck('correct_answer', 'id');

        // Calculate the score
        $totalQuestions = $test->questions->count();
        $correctCount = 0;

        foreach ($request->answers as $questionId => $answer) {
            if ($answer == $correctAnswers[$questionId]) {
                $correctCount++;
            }
        }

        $score = ($correctCount / $totalQuestions) * 100;

        // Create the test attempt
        $testAttempt = TestAttempt::create([
            'user_id' => $user->id,
            'test_id' => $test->id,
            'answers' => $request->answers,
            'score' => $score,
        ]);

        // Show the score to the user
        Alert::success('تم تسليم الاختبار بنجاح', "لقد حصلت على $score% من الدرجات");

        // Redirect to the result page instead of showing an alert
        return redirect()->route('student.tests.result', $test);
    }

    public function result(Test $test)
    {
        $user = Auth::user();
        $testAttempt = TestAttempt::where('user_id', $user->id)
            ->where('test_id', $test->id)
            ->latest()
            ->firstOrFail();

        $test->load('questions');

        $answersWithCorrectness = [];
        foreach ($test->questions as $question) {
            $userAnswer = $testAttempt->answers[$question->id] ?? null;
            $answersWithCorrectness[] = [
                'question' => $question,
                'userAnswer' => $userAnswer,
                'isCorrect' => $userAnswer == $question->correct_answer
            ];
        }

        return view('student.tests.result', compact('test', 'testAttempt', 'answersWithCorrectness'));
    }
}
