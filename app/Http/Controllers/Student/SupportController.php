<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class SupportController extends Controller
{
    public function index()
    {
        $studentConversations = Conversation::where('student_id', auth()->user()->id)->with(['student', 'lastMessage'])
            ->orderBy('last_message_at', 'desc')
            ->paginate(10);
        return view('student.support.index', compact('studentConversations'));
    }

    public function showReplyForm(Conversation $conversation)
    {
        // check if the conversation belongs to the current user
        if ($conversation->student_id !== auth()->user()->id) {
            Alert::error('المحادثة غير متوفرة');
            return redirect()->route('student.support.index');
        }
        $conversation->load('messages.sender');
        $conversation->resetStudentUnreadCount();
        return view('student.support.reply', compact('conversation'));
    }

    public function reply(Request $request, Conversation $conversation)
    {
        $request->validate([
            'content' => 'required|string'
        ]);

        $message = new Message([
            'sender' => 'student',
            'content' => $request->content
        ]);

        $conversation->messages()->save($message);
        $conversation->touch('last_message_at');

        return redirect()->route('student.conversations.reply', $conversation)
            ->with('success', 'تم إرسال الرد بنجاح');
    }

    public function create(Request $request)
    {
        return view('student.support.create');
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $studentId = auth()->user()->id;

        $conversation = Conversation::create([
            'student_id' => $studentId,
            'subject' => $request->subject,
            'status' => 'open',
            'student_unread_count' => 0,
            'last_message_at' => now(),
        ]);

        $conversation->messages()->create([
            'sender' => 'student',
            'content' =>
            $request->message,
        ]);

        

        return redirect()->route('student.support.index');
    }

}
