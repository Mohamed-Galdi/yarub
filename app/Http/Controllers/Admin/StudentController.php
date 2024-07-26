<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $trashedCount = User::onlyTrashed()->count();
        return view('admin.students.students', compact('trashedCount'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $student = User::find($id);
        $coursesCount = $student->courses()->count();
        $lessonsCount = $student->lessons()->count();
        $testsCount = $student->test_attempts()->count();
        $certificatesCount = $student->certificates()->count();
        return view('admin.students.view-student', compact('student', 'coursesCount', 'lessonsCount', 'testsCount', 'certificatesCount'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $student = User::find($id);
        $student->delete();
        return redirect()->route('admin.students');
        
    }

    public function deleted()
    {
        $students = User::onlyTrashed()->get();
        return view('admin.students.deleted', compact('students'));
    }

    public function restore(string $id)
    {
        $student = User::onlyTrashed()->find($id);
        $student->restore();
        return redirect()->route('admin.students.deleted');
    }
}
