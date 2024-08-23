<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CertificatesController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $certificates = $user->certificates()->with('course', 'lesson')->get();
        return view('student.certificates.index', compact('certificates'));
    }
}
