<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Test;
use App\Models\Question;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;

class TestController extends Controller
{
    public function index()
    {
        $courses_tests = Test::whereNotNull('course_id')->with('course')->with('attempts')->orderBy('is_published', 'desc')->get();
        $lessons_tests = Test::whereNotNull('lesson_id')->with('lesson')->with('attempts')->orderBy('is_published', 'desc')->get();
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
                    'question_text' => $questionData['question_text'],
                    'question' => $questionData['question'],
                    'option_1' => $questionData['option_1'],
                    'option_2' => $questionData['option_2'],
                    'option_3' => $questionData['option_3'],
                    'option_4' => $questionData['option_4'],
                    'correct_answer' => $questionData['correct_answer'],
                ]);
            }

            DB::commit();
            Alert::success('تم إنشاء الإختبار بنجاح');
            return redirect()->route('admin.tests');
        } catch (\Exception $e) {
            DB::rollBack();
            Alert::error('حدث خطأ أثناء إنشاء الإختبار');
            return back();
        }
    }

    public function edit($id)
    {
        $courses = Course::all();
        $lessons = Lesson::all();
        $test = Test::findOrFail($id);
        return view('admin.tests.edit', compact('test', 'courses', 'lessons'));
    }

    public function update(Request $request, $id)
    {
        $test = Test::findOrFail($id);
        $request->validate([
            'title' => 'required|string|max:255',
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

            $test->update([
                'title' => $request->title,
                'description' => $request->description,
                'is_published' => $request->has('is_published'),
                'type' => $request->type,
            ]);

            // Update or create questions
            foreach ($request->questions as $questionData) {
                if (isset($questionData['id'])) {
                    $question = Question::find($questionData['id']);
                    $question->update([
                        'question' => $questionData['question'],
                        'option_1' => $questionData['option_1'],
                        'option_2' => $questionData['option_2'],
                        'option_3' => $questionData['option_3'],
                        'option_4' => $questionData['option_4'],
                        'correct_answer' => $questionData['correct_answer'],
                    ]);
                } else {
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
            }

            // Delete questions that were removed
            $existingQuestionIds = $test->questions->pluck('id')->toArray();
            $updatedQuestionIds = collect($request->questions)->pluck('id')->filter()->toArray();
            $questionIdsToDelete = array_diff($existingQuestionIds, $updatedQuestionIds);
            Question::destroy($questionIdsToDelete);

            DB::commit();
            Alert::success('تم تحديث الإختبار بنجاح');
            return redirect()->route('admin.tests')->with('success', 'Test updated successfully.');
        } catch (\Exception $e) {
            Alert::error('حدث خطأ أثناء تحديث الإختبار');
            dd($e);
            DB::rollBack();
            return back()->with('error', 'An error occurred while updating the test. Please try again.')->withInput();
        }
    }

    public function show($id)
    {
        $test = Test::findOrFail($id);
        return view('admin.tests.show', compact('test'));
    }

    public function destroy(Test $test)
    {
        $test->delete();
        return redirect()->route('tests.index')->with('success', 'Test deleted successfully.');
    }
}
