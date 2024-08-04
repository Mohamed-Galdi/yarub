<?php

namespace App\Http\Controllers\Admin;

use App\Models\TestAttempt;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TestAttemptController extends Controller
{
    public function adminShow($id)
    {
        $testAttempt = TestAttempt::with(['test.questions', 'user'])->findOrFail($id);

        $questions = $testAttempt->test->questions;
        $studentAnswers = $testAttempt->answers;

        $result = [];
        $score = 0;

        foreach ($questions as $question) {
            $studentAnswer = $studentAnswers[$question->id] ?? null;
            $isCorrect = $studentAnswer == $question->correct_answer;

            if ($isCorrect) {
                $score++;
            }

            $result[] = [
                'question' => $question,
                'student_answer' => $studentAnswer,
                'student_answer_text' => $studentAnswer ? $question->{"option_" . $studentAnswer} : null,
                'correct_answer_text' => $question->{"option_" . $question->correct_answer},
                'is_correct' => $isCorrect,
            ];
        }

        $totalQuestions = count($questions);
        $percentage = ($totalQuestions > 0) ? ($score / $totalQuestions) * 100 : 0;

        return view('admin.tests.test_attempt', compact('testAttempt', 'result', 'score', 'totalQuestions', 'percentage'));
    }
}
