<?php

namespace App\Http\Controllers;

use App\Models\Test;
use App\Models\Question;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function index()
    {
        $tests = Test::all();
        return view('admin.tests.tests', compact('tests'));
    }

    public function create()
    {
        $courses = Course::all();
        $lessons = Lesson::all();
        return view('tests.create', compact('courses', 'lessons'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'content_type' => 'required|in:course,lesson',
            'content_id' => 'required|integer',
            'type' => 'required|in:before,after,regular',
            'is_published' => 'boolean',
            'questions' => 'required|array|min:1',
            'questions.*.question' => 'required|string',
            'questions.*.options' => 'required|array|min:4|max:4',
            'questions.*.correct_answers' => 'required|array|min:1',
            'questions.*.is_multiple_choice' => 'required|boolean',
        ]);

        $test = Test::create($validatedData);

        foreach ($validatedData['questions'] as $questionData) {
            $test->questions()->create($questionData);
        }

        return redirect()->route('tests.index')->with('success', 'Test created successfully.');
    }

    public function edit(Test $test)
    {
        $courses = Course::all();
        $lessons = Lesson::all();
        return view('tests.edit', compact('test', 'courses', 'lessons'));
    }

    public function update(Request $request, Test $test)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'content_type' => 'required|in:course,lesson',
            'content_id' => 'required|integer',
            'type' => 'required|in:before,after,regular',
            'is_published' => 'boolean',
            'questions' => 'required|array|min:1',
            'questions.*.id' => 'sometimes|integer|exists:questions,id',
            'questions.*.question' => 'required|string',
            'questions.*.options' => 'required|array|min:4|max:4',
            'questions.*.correct_answers' => 'required|array|min:1',
            'questions.*.is_multiple_choice' => 'required|boolean',
        ]);

        $test->update($validatedData);

        $test->questions()->delete();

        foreach ($validatedData['questions'] as $questionData) {
            $test->questions()->create($questionData);
        }

        return redirect()->route('tests.index')->with('success', 'Test updated successfully.');
    }

    public function destroy(Test $test)
    {
        $test->delete();
        return redirect()->route('tests.index')->with('success', 'Test deleted successfully.');
    }
}
