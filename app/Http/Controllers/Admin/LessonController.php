<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RealRashid\SweetAlert\Facades\Alert;

class LessonController extends Controller
{
    private $tempFolder = 'temp_videos';
    private $finalFolder = 'lesson_videos';
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // order by published, then created_at
        $lessons = Lesson::orderBy('published', 'desc')->orderBy('created_at', 'desc')->get();
        return view('admin.lessons.lessons', compact('lessons'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.lessons.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'monthly_price' => 'required|numeric|min:0',
            'annual_price' => 'required|numeric|min:0',
            'content_titles' => 'required|array|min:1',
            'content_titles.*' => 'required|string|max:255',
            'content_videos' => 'required|array|min:1',
            'content_videos.*' => 'required|string',
        ]);
        // dd($request->all());

        DB::beginTransaction();

        try {
            $lesson = Lesson::create([
                'title' => $request->title,
                'description' => $request->description,
                'monthly_price' => $request->monthly_price,
                'annual_price' => $request->annual_price,
            ]);

            foreach ($request->content_titles as $index => $title) {
                $tempPath = $request->content_videos[$index];
                $finalPath = $this->moveVideoToFinalLocation($tempPath, $lesson->id);
                // dd($title, $finalPath);
                Content::create([
                    'lesson_id' => $lesson->id,
                    'title' => $title,
                    'url' => $finalPath,
                ]);
            }

            $this->cleanupTempFolder();

            DB::commit();
            Alert::success('تم إنشاء الدرس بنجاح !');
            return redirect()->route('admin.lessons')->with('success', 'Lesson created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->cleanupTempFolder();
            throw $e;

            return back()->with('error', 'An error occurred while creating the lesson. Please try again.');
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

    private function moveVideoToFinalLocation($tempPath, $lessonId)
    {
        if (!Storage::disk('public')->exists($tempPath)) {
            throw new \Exception("Temporary file not found: {$tempPath}");
        }

        $fileName = basename($tempPath);
        $finalPath = "{$this->finalFolder}/lesson-{$lessonId}_{$fileName}";

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
        $lesson = Lesson::with('content')->findOrFail($id);
        return view('admin.lessons.show', compact('lesson'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $lesson = Lesson::with('content')->findOrFail($id);
        return view('admin.lessons.edit', compact('lesson'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'monthly_price' => 'required|numeric|min:0',
            'annual_price' => 'required|numeric|min:0',
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
            $lesson = Lesson::findOrFail($id);
            $lesson->update([
                'title' => $request->title,
                'description' => $request->description,
                'monthly_price' => $request->monthly_price,
                'annual_price' => $request->annual_price,
                'published' => $request->has('published'),

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
                            $finalPath = $this->moveVideoToFinalLocation($videoPath, $lesson->id);
                            // Delete old video
                            Storage::disk('public')->delete($content->url);
                            $content->url = $finalPath;
                        }
                        $content->save();
                    } else {
                        // Create new content
                        $finalPath = $this->moveVideoToFinalLocation($videoPath, $lesson->id);
                        Content::create([
                            'lesson_id' => $lesson->id,
                            'title' => $title,
                            'url' => $finalPath,
                        ]);
                    }
                }
            }

            $this->cleanupTempFolder();

            DB::commit();
            Alert::success('تم تحديث الدرس بنجاح !');
            return redirect()->route('admin.lessons')->with('success', 'Lesson updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->cleanupTempFolder();
            return back()->with('error', 'An error occurred while updating the lesson. Please try again.');
        }
    }

    public function deleteContent(Request $request, $id)
    {
        $content = Content::findOrFail($id);
        $lessonId = $content->lesson_id;

        Storage::disk('public')->delete($content->url);
        $content->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function detach($lesson_id, $student_id, Request $request)
    {
        $lesson = Lesson::findOrFail($lesson_id);
        $student = User::findOrFail($student_id);

        $lesson->students()->detach($student);
        Alert::success('تم إزالة الطالب بنجاح !');

        return redirect()->route('admin.lessons.view', ['id' => $lesson_id]);
    }
}
