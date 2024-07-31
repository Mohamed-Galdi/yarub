<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'price', 'is_published',
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
        return $this->belongsToMany(User::class, 'student_course_sub')->withPivot(['payment_amount','created_at'])
            ->withTimestamps();
    }

    public function coupons(): BelongsToMany
    {
        return $this->belongsToMany(Coupon::class);
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }
}
