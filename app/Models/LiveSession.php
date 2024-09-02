<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveSession extends Model
{
    use HasFactory;

    protected $fillable = ['course_id', 'zoom_meeting_id', 'zoom_meeting_password', 'start_time', 'duration', 'join_url', 'status'];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
    
}
