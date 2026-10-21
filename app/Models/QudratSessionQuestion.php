<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QudratSessionQuestion extends Model
{
    protected $fillable = [
        'session_id', 'question_id', 'position', 'draft_option_key', 'hint_requested_at', 'availability', 'content_snapshot',
    ];

    protected $casts = [
        'hint_requested_at' => 'datetime',
        'content_snapshot' => 'array',
    ];

    protected $hidden = ['content_snapshot'];

    protected static function booted(): void
    {
        static::updating(function ($item) {
            if ($item->isDirty(['content_snapshot', 'session_id', 'question_id', 'position'])) {
                throw new \LogicException('Session content and order are immutable.');
            }
        });
    }

    public function session()
    {
        return $this->belongsTo(QudratPracticeSession::class, 'session_id');
    }

    public function question()
    {
        return $this->belongsTo(QudratQuestion::class, 'question_id');
    }

    public function answer()
    {
        return $this->hasOne(QudratSessionAnswer::class, 'session_question_id');
    }
}
