<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = ['student_id', 'subject', 'status', 'admin_unread_count', 'last_message_at'];

    protected $dates = ['last_message_at'];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function incrementUnreadCount()
    {
        $this->increment('admin_unread_count');
    }

    public function resetUnreadCount()
    {
        $this->update(['admin_unread_count' => 0]);
    }
    
    public function lastMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }
}