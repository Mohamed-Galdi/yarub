<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['conversation_id', 'sender', 'content'];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($message) {
            $conversation = $message->conversation;
            $conversation->update(['last_message_at' => $message->created_at]);

            if ($message->sender === 'student') {
                $conversation->incrementUnreadCount();
            }
        });
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender');
    }
}
