<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Coupon extends Model
{
    use HasFactory;
    protected $fillable = [
        'code', 'type', 'value', 'applicable_to', 'start_date', 'end_date',
        'usage_limit', 'used_count', 'is_active'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class);
    }

    public function lessons(): BelongsToMany
    {
        return $this->belongsToMany(Lesson::class);
    }
}
