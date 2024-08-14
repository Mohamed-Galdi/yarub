<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RealRashid\SweetAlert\Facades\Alert;

class CourseController extends Controller
{
    private $tempFolder = 'temp_videos';
    private $finalFolder = 'course_videos';
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // order by published, then created_at
        $courses = Course::orderBy('is_published', 'desc')->orderBy('created_at', 'desc')->get();
        return view('admin.courses.courses', compact('courses'));
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
            'type' => 'required|string|max:255',
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
                'type' => $request->type,
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
            Alert::success('تم إنشاء الدورة بنجاح !');
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
        $course = Course::with('content')->findOrFail($id);
        return view('admin.courses.show', compact('course'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $course = Course::with('content')->findOrFail($id);
        return view('admin.courses.edit', compact('course'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'type' => 'required|string|max:255',
            'content_titles' => 'array|min:1',
            'content_titles.*' => 'string|max:255',
            'content_videos' => 'array|min:1',
            'content_videos.*' => 'nullable|string',
            'content_ids' => 'array|min:1',
            'content_ids.*' => 'nullable|integer|exists:contents,id',
        ]);
        // dd($request->all());
        DB::beginTransaction();

        try {
            $course = Course::findOrFail($id);
            $course->update([
                'title' => $request->title,
                'description' => $request->description,
                'price' => $request->price,
                'type' => $request->type,
                'is_published' => $request->has('published'),

            ]);

            // check if content_titles array exist and not empty
            if ($request->has('content_titles') && count($request->content_titles) > 0) {
                foreach ($request->content_titles as $index => $title) {
                    $contentId = $request->content_ids[$index];
                    $videoPath = $request->content_videos[$index];

                    if ($contentId) {
                        // Update existing content
                        $content = Content::findOrFail($contentId);
                        $content->title = $title;
                        if ($videoPath && $videoPath !== $content->url) {
                            // New video uploaded, move it to final location
                            $finalPath = $this->moveVideoToFinalLocation($videoPath, $course->id);
                            // Delete old video
                            Storage::disk('public')->delete($content->url);
                            $content->url = $finalPath;
                        }
                        $content->save();
                    } else {
                        // Create new content
                        $finalPath = $this->moveVideoToFinalLocation($videoPath, $course->id);
                        Content::create([
                            'course_id' => $course->id,
                            'title' => $title,
                            'url' => $finalPath,
                        ]);
                    }
                }
            }

            $this->cleanupTempFolder();

            DB::commit();
            Alert::success('تم تحديث الدورة بنجاح !');
            return redirect()->route('admin.courses')->with('success', 'Course updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->cleanupTempFolder();
            return back()->with('error', 'An error occurred while updating the course. Please try again.');
        }
    }

    public function deleteContent(Request $request, $id)
    {
        $content = Content::findOrFail($id);
        $courseId = $content->course_id;

        Storage::disk('public')->delete($content->url);
        $content->forceDelete();
        // $content->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function detach($course_id, $student_id, Request $request)
    {
        $course = Course::findOrFail($course_id);
        $student = User::findOrFail($student_id);

        $course->students()->detach($student);
        Alert::success('تم إزالة الطالب بنجاح !');

        return redirect()->route('admin.courses.view', ['id' => $course_id]);
    }
}
