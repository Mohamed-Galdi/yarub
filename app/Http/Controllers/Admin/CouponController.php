<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class CouponController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $coupons = Coupon::all();
        return view('admin.coupons.coupons', compact('coupons'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $courses = Course::all();
        $lessons = Lesson::all();
        return view('admin.coupons.create', compact('courses', 'lessons'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'code' => 'required|unique:coupons,code',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'applicable_to' => 'required|in:all,courses,lessons,specific',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'courses' => 'array',
            'lessons' => 'array',
        ]);

        $coupon = Coupon::create($validatedData);

        if ($validatedData['applicable_to'] === 'specific') {
            $coupon->courses()->sync($request->input('courses', []));
            $coupon->lessons()->sync($request->input('lessons', []));
        }
        Alert::success('تم إنشاء قسيمة بنجاح !');
        return redirect()->route('admin.coupons');
    }

    /**
     * Display the specified resource.
     */
    public function show(Coupon $coupon)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Coupon $coupon)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Coupon $coupon)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Coupon $coupon)
    {
        //
    }
}
