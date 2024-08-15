<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseSubscription extends Model
{
    use HasFactory;
    protected $table = 'student_course_sub';
    protected $fillable = ['user_id','payment_id', 'course_id', 'is_active', 'cost', 'created_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
