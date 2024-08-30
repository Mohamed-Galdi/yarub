<?php

namespace App\Http\Controllers\Admin;

use App\Exports\CoursesSubsExport;
use App\Exports\LessonsSubsExport;
use App\Exports\PaymentsExport;
use App\Exports\UsersExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use RealRashid\SweetAlert\Facades\Alert;

class DataExportController extends Controller
{
    public function index() {
        return view('admin.data-export');
    }
    
    public function exportData(Request $request) {

        // validate request
        $validated = $request->validate([
            'data_type' => 'required|in:users,courses_subs,lessons_subs,payments',
            'start_date' => 'nullable|date|required',
            'end_date' => 'nullable|date|after:start_date|required',
            'file_name' => 'required|string|max:255',
        ]);


        switch ($validated['data_type']) {
            case 'users':
                return Excel::download(new UsersExport($validated['start_date'], $validated['end_date']), $validated['file_name'] . '.xlsx');
                break;
            case 'courses_subs':
                return Excel::download(new CoursesSubsExport($validated['start_date'], $validated['end_date']), $validated['file_name'] . '.xlsx');
                break;
            case 'lessons_subs':
                return Excel::download(new LessonsSubsExport($validated['start_date'], $validated['end_date']), $validated['file_name'] . '.xlsx');
                break;
            case 'payments':
                return Excel::download(new PaymentsExport($validated['start_date'], $validated['end_date']), $validated['file_name'] . '.xlsx');
                break;
        }

    }
}
