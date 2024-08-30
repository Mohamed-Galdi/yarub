<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class ConversationController extends Controller
{
    public function index()
    {
        $conversations = Conversation::with(['student', 'lastMessage'])
            ->orderBy('last_message_at', 'desc')
            ->paginate(10);

        return view('admin.conversations.conversations', compact('conversations'));
    }

    public function showReplyForm(Conversation $conversation)
    {
        $conversation->load('messages.sender');
        $conversation->resetUnreadCount();
        return view('admin.conversations.reply', compact('conversation'));
    }

    public function reply(Request $request, Conversation $conversation)
    {
        $request->validate([
            'content' => 'required|string'
        ]);

        $message = new Message([
            'sender' => 'admin',
            'content' => $request->content
        ]);

        $conversation->messages()->save($message);
        $conversation->touch('last_message_at');

        return redirect()->route('admin.conversations.reply', $conversation)
            ->with('success', 'تم إرسال الرد بنجاح');
    }

    public function create(Request $request)
    {
        $students = User::where('role', 'student')->get();
        return view('admin.conversations.create', compact('students'));
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'students' => 'required|array',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $studentIds = $request->students;

        if (in_array('all', $studentIds)) {
            $studentIds = User::where('role', 'student')->pluck('id')->toArray();
        }

        foreach ($studentIds as $studentId) {
            $this->createConversation($studentId, $request->subject, $request->message);
        }

        $message = count($studentIds) > 1
            ? 'تم إنشاء محادثات جديدة مع المشتركين المحددين بنجاح'
            : 'تم إنشاء المحادثة الجديدة بنجاح';

        Alert::success($message);

        return redirect()->route('admin.conversations.index');
    }

    private function createConversation($studentId, $subject, $message)
    {
        $conversation = Conversation::create([
            'student_id' => $studentId,
            'subject' => $subject,
            'status' => 'open',
            'admin_unread_count' => 0,
            'last_message_at' => now(),
        ]);

        $conversation->messages()->create([
            'sender' => 'admin',
            'content' => $message,
        ]);
    }
}
