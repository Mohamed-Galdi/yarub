<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'student_course_sub')->withPivot('created_at')
            ->withTimestamps();
    }

    public function lessons()
    {
        return $this->belongsToMany(Lesson::class, 'student_lesson_sub')->withPivot('created_at')
            ->withTimestamps();
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function progress()
    {
        return $this->hasMany(Progress::class);
    }

    public function test_attempts()
    {
        return $this->hasMany(TestAttempt::class);
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class, 'student_id');
    }

    /**
     * Get the user's published courses.
     */
    public function publishedCourses()
    {
        return $this->belongsToMany(Course::class, 'student_course_sub')->published()->withPivot('created_at');
    }

    /**
     * Get the user's published lessons.
     */
    public function publishedLessons()
    {
        return $this->belongsToMany(Lesson::class, 'student_lesson_sub')->published()->withPivot('created_at');
    }

    public function isSuperAdmin()
    {
        return $this->role === 'admin' && $this->id === 1; 
    }
}
