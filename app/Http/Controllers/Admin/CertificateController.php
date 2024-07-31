<?php

namespace App\Http\Controllers\Admin;

use App\Models\Certificate;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use RealRashid\SweetAlert\Facades\Alert;

class CertificateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.certificates.certificates');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $students = User::where('role', 'student')->get();
        return view('admin.certificates.create', compact('students'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'content_type' => 'required|in:course,lesson',
            'content_id' => 'required',
        ]);

        $is_course = $request->content_type === 'course';
        Certificate::create([
            'user_id' => $request->student_id,
            'course_id' => $is_course ? $request->content_id : null,
            'lesson_id' => $is_course ? null : $request->content_id,
        ]);

        Alert::success('Certificate created successfully.');
        return redirect()->route('admin.certificates');
    }

    public function getStudentContent(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'content_type' => 'required|in:course,lesson',
        ]);

        $student = User::findOrFail($request->student_id);

        if ($request->content_type === 'course') {
            $content = $student->courses->pluck('title', 'id');
        } else {
            $content = $student->lessons->pluck('title', 'id');
        }
        // dd($content);

        return response()->json($content);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $certificate = Certificate::find($id);
        $is_for_course = $certificate->course_id !== null;
        return view('admin.certificates.show', compact('certificate', 'is_for_course'));
    }

    public function download($id)
    {
        $certificate = Certificate::find($id);
        $is_for_course = $certificate->course_id !== null;

        return view('admin.certificates.download', compact('certificate', 'is_for_course'));
        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Certificate $certificate)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Certificate $certificate)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Certificate $certificate)
    {
        //
    }
}
