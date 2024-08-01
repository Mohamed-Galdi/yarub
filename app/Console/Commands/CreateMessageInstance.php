<?php

namespace App\Console\Commands;

use App\Models\Message;
use Illuminate\Console\Command;

class CreateMessageInstance extends Command
{
    protected $signature = 'message:send-demo';
    protected $description = 'Create a Message instance without saving it to the database';

    public function handle()
    {
        $message = new Message();
        $message->conversation_id = 2;
        $message->sender = 'student';
        $message->content = "نعم، يمكنك الاطلاع على الفصل الخامس من كتاب";
        $message->created_at = now();
        $message->save();

        // Output the message content
        $this->info('Message instance created: ' . $message->content);

    }
}
