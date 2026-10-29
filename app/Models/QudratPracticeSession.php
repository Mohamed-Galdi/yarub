<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QudratPracticeSession extends Model
{
    protected $fillable = [
        'user_id', 'category_id', 'mode', 'status', 'current_position', 'started_at', 'completed_at', 'closed_at', 'closure_reason', 'practice_set_key', 'practice_set_title',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(QudratCategory::class, 'category_id');
    }

    public function items()
    {
        return $this->hasMany(QudratSessionQuestion::class, 'session_id')->orderBy('position');
    }

    public function answers()
    {
        return $this->hasMany(QudratSessionAnswer::class, 'session_id');
    }
}
