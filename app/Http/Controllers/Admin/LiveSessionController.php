<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\LiveSession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jubaer\Zoom\Facades\Zoom;
use RealRashid\SweetAlert\Facades\Alert;


class LiveSessionController extends Controller
{
    public function create()
    {
        return view('admin.live-sessions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'type' => 'required|string|max:255',
            'duration' => 'required|numeric|min:0|max:40',
            'start_date' => 'required|date',
        ]);

        DB::beginTransaction();

        try {
            $course = Course::create([
                'title' => $request->title,
                'description' => $request->description,
                'price' => $request->price,
                'type' => $request->type,
                'content_type' => 'live_session',
            ]);
            $this->scheduleZoomMeeting($course->id, $request->title, $request->start_date, $request->duration);
            DB::commit();
            Alert::success('تم إنشاء الحصة المباشرة بنجاح !');
            return redirect()->route('admin.courses');
        } catch (\Exception $e) {
            DB::rollBack();
            Alert::error('حدث خطأ أثناء إنشاء الحصة المباشرة !');
            throw $e;
            // return redirect()->back();
        }
    }

    public function scheduleZoomMeeting($courseId, $title, $startTime, $duration)
    {
        // Convert start time to the correct format
        $startTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $startTime, 'Asia/Riyadh')->toIso8601String();

        // Schedule the Zoom meeting
        $meeting = Zoom::createMeeting([
            "topic" => $title,
            "type" => 2, // Scheduled meeting
            "duration" => $duration, // Duration in minutes
            "timezone" => 'Asia/Riyadh', // Saudi Arabia timezone
            "start_time" => $startTime, // Converted start time
        ]);

        $meetingData = $meeting['data']; // Get the 'data' array from the meeting response

        // Convert the start time to MySQL format (Y-m-d H:i:s) for storing in the database
        $mysqlStartTime = \Carbon\Carbon::parse($meeting['data']['start_time'])->setTimezone('Asia/Riyadh')->format('Y-m-d H:i:s');

        // Store the Live Session
        LiveSession::create([
            'course_id' => $courseId,
            'zoom_meeting_id' => $meeting['data']['id'], // Zoom meeting ID
            'start_time' => $mysqlStartTime, // MySQL compatible datetime
            'duration' => $meeting['data']['duration'], // Duration
            'join_url' => $meeting['data']['join_url'], // URL for students to join the meeting
        ]);
    }

    public function edit($id)
    {
        $course = Course::findOrFail($id);
        $course->load('liveSession');
        return view('admin.live-sessions.edit', compact('course'));
    }

    public function updateCourse(Request $request, $id){
        $course = Course::findOrFail($id);
        $course->title = $request->title;
        $course->description = $request->description;
        $course->price = $request->price;
        $course->type = $request->type;
        $course->is_published =  $request->has('published');
        $course->save();
        Alert::success('تم تحديث الدورة بنجاح !');
        return redirect()->route('admin.live-session.edit', $id);
    }

    public function updateLiveSession(Request $request, $id){

        DB::beginTransaction();
        try {
            $course = Course::findOrFail($id);
            $course->liveSession->start_time = $request->start_date;
            $course->liveSession->duration = $request->duration;
            $course->liveSession->save();

            $meetingId = $course->liveSession->zoom_meeting_id;

            $startTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $request->start_date, 'Asia/Riyadh')->toIso8601String();

            $meeting = Zoom::updateMeeting($meetingId, [
                'topic' => $course->title,
                'start_time' => $startTime,
                'duration' => $request->duration, // Duration in minutes
            ]);

            DB::commit();
            Alert::success('تم تحديث جدولة البث بنجاح !');
            return redirect()->route('admin.live-session.edit', $id);
        } catch (\Exception $e) {
            DB::rollBack();
            Alert::error('حدث خطأ أثناء تحديث جدولة البث !');
            throw $e;
        }
        
    }
}
