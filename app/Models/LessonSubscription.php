<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonSubscription extends Model
{
    use HasFactory;
    protected $table = 'student_lesson_sub';
    protected $fillable = ['user_id', 'lesson_id', 'sub_plan', 'status'];
}
