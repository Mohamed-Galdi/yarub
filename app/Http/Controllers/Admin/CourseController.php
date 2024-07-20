<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    private $tempFolder = 'temp_videos';
    private $finalFolder = 'course_videos';
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.courses.courses');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.courses.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'content_titles' => 'required|array|min:1',
            'content_titles.*' => 'required|string|max:255',
            'content_videos' => 'required|array|min:1',
            'content_videos.*' => 'required|string',
        ]);
        // dd($request->all());

        DB::beginTransaction();

        try {
            $course = Course::create([
                'title' => $request->title,
                'description' => $request->description,
                'price' => $request->price,
            ]);

            foreach ($request->content_titles as $index => $title) {
                $tempPath = $request->content_videos[$index];
                $finalPath = $this->moveVideoToFinalLocation($tempPath, $course->id);
                // dd($title, $finalPath);
                Content::create([
                    'course_id' => $course->id,
                    'title' => $title,
                    'url' => $finalPath,
                ]);
            }

            $this->cleanupTempFolder();

            DB::commit();
            return redirect()->route('admin.courses')->with('success', 'Course created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->cleanupTempFolder();
            throw $e;
            
            return back()->with('error', 'An error occurred while creating the course. Please try again.');
        }
    }

    public function uploadVideo(Request $request)
    {
        $request->validate([
            'video' => 'required|file|mimetypes:video/*|max:100000'
        ]);

        $file = $request->file('video');
        $tempName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($this->tempFolder, $tempName, 'public');

        return response()->json(['path' => $path]);
    }

    private function moveVideoToFinalLocation($tempPath, $courseId)
    {
        if (!Storage::disk('public')->exists($tempPath)) {
            throw new \Exception("Temporary file not found: {$tempPath}");
        }

        $fileName = basename($tempPath);
        $finalPath = "{$this->finalFolder}/course-{$courseId}_{$fileName}";

        Storage::disk('public')->move($tempPath, $finalPath);

        return $finalPath;
    }

    private function cleanupTempFolder()
    {
        $files = Storage::disk('public')->files($this->tempFolder);
        foreach ($files as $file) {
            Storage::disk('public')->delete($file);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view('admin.courses.edit', compact('id'));
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
        //
    }
}
