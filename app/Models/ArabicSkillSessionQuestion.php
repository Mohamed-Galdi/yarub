<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArabicSkillSessionQuestion extends Model
{
    protected $fillable = ['session_id', 'bank_id', 'question_id', 'position', 'content_snapshot', 'response', 'earned_points', 'result', 'submitted_at'];

    protected $casts = ['content_snapshot' => 'array', 'response' => 'array', 'submitted_at' => 'datetime'];

    protected $hidden = ['content_snapshot'];

    protected static function booted(): void
    {
        static::updating(function ($item) {
            if ($item->isDirty(['session_id', 'bank_id', 'question_id', 'position', 'content_snapshot'])) {
                throw new \LogicException('Session content and order are immutable.');
            }
            if ($item->getOriginal('submitted_at') !== null && $item->isDirty(['response', 'earned_points', 'result', 'submitted_at'])) {
                throw new \LogicException('Committed answers are immutable.');
            }
        });
    }

    public function session()
    {
        return $this->belongsTo(ArabicSkillSession::class, 'session_id');
    }

    public function question()
    {
        return $this->belongsTo(ArabicSkillQuestion::class, 'question_id');
    }
}
