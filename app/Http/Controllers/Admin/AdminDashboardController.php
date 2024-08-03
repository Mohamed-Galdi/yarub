<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use RealRashid\SweetAlert\Facades\Alert;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $students_count = User::where('role', 'student')->count();
        $courses_count = Course::count();
        $lessons_count = Lesson::count();
        $courses_and_lessons_count = Course::count() + Lesson::count();
        $visitors_count = 0;
        $sales_count = 0;
        return view('admin.dashboard', compact('students_count', 'courses_count', 'lessons_count', 'courses_and_lessons_count', 'visitors_count', 'sales_count'));
    }

    public function show()
    {
        $superAdmin = User::where('role', 'admin')->first();
        $admins = User::where('role', 'admin')->where('id', '!=', $superAdmin->id)->get();
        return view('admin.account.index', compact('superAdmin', 'admins'));
    }

    public function updateMain(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . auth()->id(),
        ]);

        $user = User::where('role', 'admin')->first();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();
        toast('تم تحديث الحساب بنجاح');
        return back();
    }

    public function updateMainPassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::where('role', 'admin')->first() ;

        if (!Hash::check($request->old_password, $user->password)) {
            Alert::error('كلمة المرور القديمة غير صحيحة');
            return back();
        }

        $user->password = Hash::make($request->new_password);
        $user->save();
        toast('تم تغيير كلمة المرور بنجاح');
        return back();
    }

    public function deleteAdmin($admin_id)
    {
        $admin = User::findOrFail($admin_id);

        if ($admin->role !== 'admin') {
            return back()->withErrors(['error' => 'لا يمكن حذف هذا الحساب']);
        }

        $admin->delete();
        toast('تم حذف المشرف بنجاح');
        return back();
    }

    public function createAdmin(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'admin',
        ]);
        toast('تم إضافة المشرف بنجاح');
        return back();
    }
}
