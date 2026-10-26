<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Admin\Tracking\StudentDetail;
use App\Services\Admin\Tracking\StudentDetailState;
use App\Services\Admin\Tracking\UsersListState;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('view-student-tracking');

        return view('admin.students.students');
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
    public function show(Request $request, string $id, StudentDetail $detail)
    {
        $this->authorize('view-student-tracking');
        $request->validate(['list' => 'sometimes|array', 'detail' => 'sometimes|array']);
        $list = UsersListState::normalize($request->input('list', []));
        $state = StudentDetailState::normalize($request->query());

        return view('admin.students.view-student', $detail->page((int) $id, $state, $list));
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
    public function destroy(Request $request, string $id)
    {
        $this->authorize('view-student-tracking');
        $request->validate(['list' => 'sometimes|array', 'detail' => 'sometimes|array']);
        $state = UsersListState::normalize($request->input('list', []));
        $detailState = $request->has('detail') ? StudentDetailState::normalize($request->input('detail')) : null;
        $student = User::withTrashed()->where('role', 'student')->findOrFail($id);
        if (!$student->trashed()) {
            $student->delete();
        }

        return $request->expectsJson() ? response()->json(['updated' => true]) : ($detailState === null
            ? redirect()->route('admin.students', $state)
            : redirect()->route('admin.view_student', ['id' => $id] + $detailState + ['list' => $state]));
        
    }

    public function deleted(Request $request)
    {
        $this->authorize('view-student-tracking');

        return redirect()->route('admin.students', array_replace(UsersListState::normalize($request->query()), ['status' => 'blocked', 'page' => 1]));
    }

    public function restore(Request $request, string $id)
    {
        $this->authorize('view-student-tracking');
        $request->validate(['list' => 'sometimes|array', 'detail' => 'sometimes|array']);
        $state = UsersListState::normalize($request->input('list', ['status' => 'blocked']));
        $detailState = $request->has('detail') ? StudentDetailState::normalize($request->input('detail')) : null;
        $student = User::withTrashed()->where('role', 'student')->findOrFail($id);
        if ($student->trashed()) {
            $student->restore();
        }

        return $request->expectsJson() ? response()->json(['updated' => true]) : ($detailState === null
            ? redirect()->route('admin.students', $state)
            : redirect()->route('admin.view_student', ['id' => $id] + $detailState + ['list' => $state]));
    }
}
