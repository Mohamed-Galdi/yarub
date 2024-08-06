<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GuestMessage;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class GuestMessagesController extends Controller
{
    public function messages_from_about()
    {
        $messages_from_about = GuestMessage::where('source', 'about_page')->paginate(5);
        return view('admin.guest-messages.messages_from_about', compact('messages_from_about'));
    }

    public function messages_from_contact()
    {
        $messages_from_contact = GuestMessage::where('source', 'contact_page')->paginate(5);
        return view('admin.guest-messages.messages_from_contact', compact('messages_from_contact'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string| max:500',
            'source' => 'required',
            'name' => 'string|nullable|max:100',
            'email' => 'email|nullable',
        ]);

        $message = new GuestMessage();
        $message->message = $request->message;
        $message->source = $request->source;
        $message->name = $request->name ?? null;
        $message->email = $request->email ?? null;
        $message->save();

        Alert::success('تم إستقبال رسالتك بنجاح');
        return back();
    }
}
