<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QudratSessionAnswer extends Model
{
    protected $fillable = [
        'session_question_id', 'session_id', 'user_id', 'question_id', 'selected_option_key', 'is_correct', 'used_hint', 'submitted_at',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'used_hint' => 'boolean',
        'submitted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(fn () => throw new \LogicException('Submitted Qudrat answers are immutable.'));
        static::deleting(fn () => throw new \LogicException('Submitted Qudrat answers cannot be deleted.'));
    }

    public function item()
    {
        return $this->belongsTo(QudratSessionQuestion::class, 'session_question_id');
    }

    public function session()
    {
        return $this->belongsTo(QudratPracticeSession::class, 'session_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function question()
    {
        return $this->belongsTo(QudratQuestion::class, 'question_id');
    }
}
