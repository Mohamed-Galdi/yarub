<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseSubscription;
use App\Models\Lesson;
use App\Models\LessonSubscription;
use App\Models\Payment;
use App\Models\User;
use App\Models\Visitor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use RealRashid\SweetAlert\Facades\Alert;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $students_count = User::where('role', 'student')->count();
        $courses_count = Course::count();
        $lessons_count = Lesson::count();
        $courses_and_lessons_count = Course::count() + Lesson::count();
        $allTimeVisitors = Visitor::count();
        $total_sales = Payment::where('payment_status', 'paid')->sum('payment_amount');

        $visitorsAndRegistrationsChartData = $this->getVisitorsAndRegistrationsChartData();
        $courseAndLessonChartData = $this->getCourseAndLessonChartData();

        $coursesWithSubsCount = $this->getCoursesWithSubsCount();
        $lessonsWithSubsCount = $this->getLessonsWithSubsCount();


        return view('admin.dashboard', compact('students_count', 'courses_and_lessons_count', 'allTimeVisitors', 'total_sales', 'visitorsAndRegistrationsChartData', 'courseAndLessonChartData', 'coursesWithSubsCount', 'lessonsWithSubsCount'));
    }

    public function getVisitorsAndRegistrationsChartData()
    {
        $end = Carbon::now()->endOfMonth();
        $start = $end->copy()->subMonths(6)->startOfMonth();

        $visitors = Visitor::select(DB::raw('DATE_FORMAT(visited_at, "%Y-%m") as month'), DB::raw('COUNT(DISTINCT ip_address) as count'))
            ->whereBetween('visited_at', [$start, $end])
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month')
            ->all();

        $registrations = User::select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$start, $end])
            ->where('role', 'student')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month')
            ->all();

        $months = [];
        $visitorData = [];
        $registrationData = [];

        for ($date = $start->copy(); $date <= $end; $date->addMonth()) {
            $monthKey = $date->format('Y-m');
            $months[] = $date->translatedFormat('F'); // This will give month names in Arabic
            $visitorData[] = $visitors[$monthKey] ?? 0;
            $registrationData[] = $registrations[$monthKey] ?? 0;
        }

        return [
            'months' => $months,
            'visitors' => $visitorData,
            'registrations' => $registrationData,
        ];
    }

    public function getCourseAndLessonChartData()
    {
        $end = Carbon::now()->endOfMonth();
        $start = $end->copy()->subMonths(6)->startOfMonth();

        $courseSubscriptions = CourseSubscription::select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('COUNT(*) as count'))
        ->whereBetween('created_at', [$start, $end])
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month')
            ->all();

        $lessonSubscriptions = LessonSubscription::select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('COUNT(*) as count'))
        ->whereBetween('created_at', [$start, $end])
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month')
            ->all();

        $months = [];
        $courseData = [];
        $lessonData = [];

        for ($date = $start->copy(); $date <= $end; $date->addMonth()) {
            $monthKey = $date->format('Y-m');
            $months[] = $date->translatedFormat('F'); // This will give month names in Arabic
            $courseData[] = $courseSubscriptions[$monthKey] ?? 0;
            $lessonData[] = $lessonSubscriptions[$monthKey] ?? 0;
        }

        return [
            'months' => $months,
            'courses' => $courseData,
            'lessons' => $lessonData,
        ];
    }

    public function getCoursesWithSubsCount()
    {
        // Query to get the subscription count for each course
        $courses = DB::table('student_course_sub')
        ->select('course_id', DB::raw('count(*) as subs_count'))
        ->groupBy('course_id')
        ->orderByDesc('subs_count')
        ->get();

        // Create an array of counts and titles
        $subsCount = [];
        $courseTitles = [];

        foreach ($courses as $course) {
            // Fetch the course title using the Course model
            $courseModel = Course::find($course->course_id);
            if ($courseModel) {
                $subsCount[] = $course->subs_count;
                $courseTitles[] = $courseModel->title;
            }
        }

        // Return the array in the required format
        return [
            'subs_count' => $subsCount,
            'course_title' => $courseTitles,
        ];
    }

    public function getLessonsWithSubsCount()
    {
        // Query to get the subscription count for each lesson
        $lessons = DB::table('student_lesson_sub')
        ->select('lesson_id', DB::raw('count(*) as subs_count'))
        ->groupBy('lesson_id')
        ->orderByDesc('subs_count')
        ->get();

        // Create an array of counts and titles
        $subsCount = [];
        $lessonTitles = [];

        foreach ($lessons as $lesson) {
            // Fetch the lesson title using the Lesson model
            $lessonModel = Lesson::find($lesson->lesson_id);
            if ($lessonModel) {
                $subsCount[] = $lesson->subs_count;
                $lessonTitles[] = $lessonModel->title;
            }
        }

        // Return the array in the required format
        return [
            'subs_count' => $subsCount,
            'lesson_title' => $lessonTitles,
        ];
    }

    public function show()
    {
        $superAdmin = User::find(1);
        $admins = User::where('role', 'admin')->where('id', '!=', $superAdmin->id)->get();
        return view('admin.account.index', compact('superAdmin', 'admins'));
    }

    public function updateMain(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['string', 'email', 'max:255', Rule::unique('users', 'email')->ignore(User::find(1)->id)]
        ]);
        $authUser = User::find(auth()->id());
        if ($authUser->isSuperAdmin()) {
            $user = User::find(1);
            $user->name = $request->name;
            $user->email = $request->email;
            $user->save();
            toast('تم تحديث الحساب بنجاح');
            return back();
        } else {
            Alert::error('لا يمكن تحديث معلومات الحساب الرئيسي الا بواسطة المشرف الرئيسي');
            return back();
        }
    }

    public function updateMainPassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $authUser = User::find(auth()->id());
        if ($authUser->isSuperAdmin()) {
            if (!Hash::check($request->old_password, $authUser->password)) {
                Alert::error('كلمة المرور القديمة غير صحيحة');
                return back();
            }

            $authUser->password = Hash::make($request->password);
            $authUser->save();
            toast('تم تغيير كلمة المرور بنجاح');

            // log out the user
            Auth::guard('web')->logout();
            return redirect()->route('admin.login');
        } else {
            Alert::error('لا يمكن تغيير كلمة المرور الرئيسية الا بواسطة المشرف الرئيسي');
            return back();
        }
    }

    public function createAdmin(Request $request)
    {

        if (!auth()->user()->isSuperAdmin()) {
            Alert::error('لا يمكن إضافة مشرف جديد الا بواسطة المشرف الرئيسي');
            return back();
        }

        $request->validate([
            'new_admin_name' => 'required|string|max:255',
            'new_admin_email' => 'required|string|email|max:255|unique:users,email',
            'new_admin_password' => 'required|string|min:8',
        ]);

        User::create([
            'name' => $request->new_admin_name,
            'email' => $request->new_admin_email,
            'password' => Hash::make($request->new_admin_password),
            'role' => 'admin',
        ]);
        toast('تم إضافة المشرف بنجاح');
        return back();
    }

    public function updateAdmin(Request $request)
    {
        if (!auth()->user()->isSuperAdmin()) {
            Alert::error('لا يمكن تحديث معلومات الحساب  الا بواسطة المشرف الرئيسي');
            return back();
        }
        $request->validate([
            'admin_id' => 'required|integer',
            'admin_name' => 'string|max:255',
            'admin_email' => ['string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($request->admin_id)]
        ]);
        // dd($request->all());

        $authUser = User::find(auth()->id());
        if ($authUser->isSuperAdmin()) {
            $admin = User::findOrFail($request->admin_id);
            $admin->name = $request->admin_name;
            $admin->email = $request->admin_email;
            $admin->save();
            toast('تم تحديث الحساب بنجاح');
            return back();
        } else {
            Alert::error('لا يمكن تحديث معلومات الحساب  الا بواسطة المشرف الرئيسي');
            return back();
        }
    }

    public function deleteAdmin($admin_id)
    {
        if (!auth()->user()->isSuperAdmin()) {
            Alert::error('لا يمكن حذف مشرف الا بواسطة المشرف الرئيسي');
            return back();
        }
        $admin = User::findOrFail($admin_id);

        if ($admin->role !== 'admin') {
            return back()->withErrors(['error' => 'لا يمكن حذف هذا الحساب']);
        }

        $admin->forceDelete();
        toast('تم حذف المشرف بنجاح');
        return back();
    }
}
