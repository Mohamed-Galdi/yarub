<?php

namespace App\Http\Controllers;

use App\Models\Test;
use App\Models\Question;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    public function index()
    {
        $courses_tests = Test::whereNotNull('course_id')->with('course')->get();
        $lessons_tests = Test::whereNotNull('lesson_id')->with('lesson')->get();
        return view('admin.tests.tests', compact('courses_tests', 'lessons_tests'));
    }

    public function create()
    {
        $courses = Course::all();
        $lessons = Lesson::all();
        return view('admin.tests.create', compact('courses', 'lessons'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'related_id' => 'required|integer',
            'type' => 'required|in:قبلي,بعدي,عادي',
            'questions' => 'required|array|min:3',
            'questions.*.question' => 'required|string',
            'questions.*.option_1' => 'required|string',
            'questions.*.option_2' => 'required|string',
            'questions.*.option_3' => 'required|string',
            'questions.*.option_4' => 'required|string',
            'questions.*.correct_answer' => 'required|in:1,2,3,4',
        ]);
        // dd($request->all());

        try {
            DB::beginTransaction();

            $test = Test::create([
                'title' => $request->title,
                'description' => $request->description,
                'course_id' => $request->has('is_for_course') ? $request->related_id : null,
                'lesson_id' => $request->has('is_for_course') ? null : $request->related_id,
                'type' => $request->type,
            ]);

            foreach ($request->questions as $questionData) {
                Question::create([
                    'test_id' => $test->id,
                    'question' => $questionData['question'],
                    'option_1' => $questionData['option_1'],
                    'option_2' => $questionData['option_2'],
                    'option_3' => $questionData['option_3'],
                    'option_4' => $questionData['option_4'],
                    'correct_answer' => $questionData['correct_answer'],
                ]);
            }

            DB::commit();

            return redirect()->route('admin.tests')->with('success', 'Test created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'An error occurred while creating the test. Please try again.')->withInput();
        }
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
