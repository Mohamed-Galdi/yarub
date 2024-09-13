<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'type',
        'monthly_price',
        'annual_price',
        'is_published'
    ];

    // always cast the price to int
    protected $casts = [
        'monthly_price' => 'integer',
        'annual_price' => 'integer',
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

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'student_lesson_sub')
        ->withPivot(['cost', 'sub_plan', 'created_at'])
        ->withTimestamps();
    }

    public function coupons(): BelongsToMany
    {
        return $this->belongsToMany(Coupon::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }
    public function hasValidAccess(User $user)
    {
        $subscription = $this->students()
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$subscription) {
            return false;
        }

        $createdAt = Carbon::parse($subscription->pivot->created_at);
        $now = Carbon::now();

        // Check if the subscription is still valid based on the plan
        if ($subscription->pivot->sub_plan === 'monthly') {
            return $createdAt->addMonth()->greaterThan($now);
        } elseif ($subscription->pivot->sub_plan === 'annual') {
            return $createdAt->addYear()->greaterThan($now);
        }

        return false;
    }

    public function packages()
    {
        return $this->morphToMany(Package::class, 'packageable');
    }
}
