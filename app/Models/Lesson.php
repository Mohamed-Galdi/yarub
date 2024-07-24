<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'monthly_price', 'annual_price', 'published'
    ];

    public function content()
    {
        return $this->hasMany(Content::class);
    }

    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    public function progress()
    {
        return $this->morphMany(Progress::class, 'progressable');
    }

    public function tests()
    {
        return $this->hasMany(Test::class);
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'student_lesson_sub')->withPivot('created_at')
            ->withTimestamps();
    }
}
