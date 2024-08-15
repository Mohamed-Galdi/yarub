<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonSubscription extends Model
{
    use HasFactory;
    protected $table = 'student_lesson_sub';
    protected $fillable = ['user_id', 'lesson_id', 'payment_id',  'sub_plan', 'is_active', 'cost', 'created_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}
