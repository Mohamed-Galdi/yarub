<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseSubscription extends Model
{
    use HasFactory;
    protected $table = 'student_course_sub';
    protected $fillable = ['user_id', 'course_id', 'status'];

    
}
