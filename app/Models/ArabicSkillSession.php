<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArabicSkillSession extends Model
{
    protected $fillable = ['user_id', 'bank_id', 'model_id', 'mode', 'status', 'current_position', 'selected_question_count', 'available_points', 'earned_points', 'started_at', 'completed_at', 'abandoned_at', 'practice_set_key', 'practice_set_title'];

    protected $casts = ['started_at' => 'datetime', 'completed_at' => 'datetime', 'abandoned_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bank()
    {
        return $this->belongsTo(ArabicSkillBank::class, 'bank_id');
    }

    public function sourceModel()
    {
        return $this->belongsTo(ArabicSkillModel::class, 'model_id');
    }

    public function items()
    {
        return $this->hasMany(ArabicSkillSessionQuestion::class, 'session_id')->orderBy('position');
    }
}
