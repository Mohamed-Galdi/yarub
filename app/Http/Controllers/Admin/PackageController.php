<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Package;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class PackageController extends Controller
{
    public function index()
    {
        $packages = Package::orderBy('is_active', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('admin.packages.packages', compact('packages'));
    }

    public function create()
    {
        $courses = Course::where('is_published', true)->get();
        $lessons = Lesson::where('is_published', true)->get();
        return view('admin.packages.create', compact('courses', 'lessons'));
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $validatedData = $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'type' => 'required|in:courses,lessons,mixed',
            'price' => 'numeric|min:0',
            'monthly_price' => 'numeric|min:0',
            'annual_price' => 'numeric|min:0',
        ]);

        switch ($validatedData['type']) {
            case 'courses':
                $package = new Package();
                $package->type = 'courses';
                $package->title = $validatedData['title'];
                $package->description = $validatedData['description'];
                $package->price = $validatedData['price'];
                $package->monthly_price = null;
                $package->annual_price = null;
                $package->save();
                $package->courses()->attach($request->input('courses', []));
                break;
            case 'lessons':
                $package = new Package();
                $package->type = 'lessons';
                $package->title = $validatedData['title'];
                $package->description = $validatedData['description'];
                $package->price = null;
                $package->monthly_price = $validatedData['monthly_price'];
                $package->annual_price = $validatedData['annual_price'];
                $package->save();
                $package->lessons()->attach($request->input('lessons', []));
                break;
            case 'mixed':
                $package = new Package();
                $package->type = 'mixed';
                $package->title = $validatedData['title'];
                $package->description = $validatedData['description'];
                $package->price = null;
                $package->monthly_price = $validatedData['monthly_price'];
                $package->annual_price = $validatedData['annual_price'];
                $package->save();
                $package->courses()->attach($request->input('courses', []));
                $package->lessons()->attach($request->input('lessons', []));
                break;
        }
        Alert::success('تم إنشاء الحقيبة بنجاح !');
        return redirect()->route('admin.packages');
    }

    public function edit($id)
    {
        $package = Package::find($id)->with('courses', 'lessons')->first();
        $courses = Course::where('is_published', true)->get();
        $lessons = Lesson::where('is_published', true)->get();
        return view('admin.packages.edit', compact('package', 'courses', 'lessons'));
    }

    public function update(Request $request, $id)
    {
        $package = Package::findOrFail($id);

        $validatedData = $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'type' => 'required|in:courses,lessons,mixed',
            'price' => 'min:0',
            'monthly_price' => 'min:0',
            'annual_price' => 'min:0',
        ]);

        // dd($request->all());

        switch ($validatedData['type']) {
            case 'courses':
                $package->is_active = $request->has('is_active') ? true : false;
                $package->type = 'courses';
                $package->title = $validatedData['title'];
                $package->description = $validatedData['description'];
                $package->price = $validatedData['price'];
                $package->monthly_price = null;
                $package->annual_price = null;
                $package->save();
                $package->courses()->sync($request->input('courses', []));
                $package->lessons()->detach(); // Remove all lessons if type changed
                break;
            case 'lessons':
                $package->is_active = $request->has('is_active') ? true : false;
                $package->type = 'lessons';
                $package->title = $validatedData['title'];
                $package->description = $validatedData['description'];
                $package->price = null;
                $package->monthly_price = $validatedData['monthly_price'];
                $package->annual_price = $validatedData['annual_price'];
                $package->save();
                $package->lessons()->sync($request->input('lessons', []));
                $package->courses()->detach(); // Remove all courses if type changed
                break;
            case 'mixed':
                $package->is_active = $request->has('is_active') ? true : false;
                $package->type = 'mixed';
                $package->title = $validatedData['title'];
                $package->description = $validatedData['description'];
                $package->price = null;
                $package->monthly_price = $validatedData['monthly_price'];
                $package->annual_price = $validatedData['annual_price'];
                $package->save();
                $package->courses()->sync($request->input('courses', []));
                $package->lessons()->sync($request->input('lessons', []));
                break;
        }

        Alert::success('تم تحديث الحقيبة بنجاح !');
        return redirect()->route('admin.packages');
    }
}
