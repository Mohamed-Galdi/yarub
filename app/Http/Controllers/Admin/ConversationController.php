<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function index()
    {
        $conversations = Conversation::with('student')
        ->orderBy('last_message_at', 'desc')
        ->paginate(20);
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
}
