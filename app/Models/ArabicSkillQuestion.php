<?php

namespace App\Models;

use App\Models\Concerns\TracksArabicSkillRevision;
use App\Services\ArabicSkills\ContentValidator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ArabicSkillQuestion extends Model
{
    use TracksArabicSkillRevision;

    protected $fillable = ['source_id', 'bank_id', 'model_id', 'number', 'context_group_number', 'skill', 'type', 'prompt', 'options', 'matching_items', 'answer', 'explanation', 'hint', 'scoring', 'max_score', 'is_active', 'import_checksum', 'import_version'];

    protected $attributes = ['is_active' => true, 'content_revision' => 1, 'max_score' => 2];

    protected $casts = ['options' => 'array', 'matching_items' => 'array', 'answer' => 'array', 'scoring' => 'array', 'is_active' => 'boolean'];

    protected $hidden = ['answer', 'explanation', 'hint', 'scoring', 'import_checksum', 'import_version'];

    protected static function booted(): void
    {
        static::saving(function ($question) {
            if (! $question->exists) {
                $question->source_id ??= 'admin_'.str_replace('-', '', (string) Str::uuid());
                $question->is_active = true;
            }
            app(ContentValidator::class)->validateQuestion($question->only(['skill', 'type', 'prompt', 'options', 'matching_items', 'answer', 'explanation', 'hint', 'max_score', 'scoring']), $question->source_id ?? 'question');
            if ($question->exists && $question->isDirty(['source_id', 'bank_id', 'model_id', 'number', 'context_group_number'])) {
                throw new \LogicException('Question source identity and original membership are immutable.');
            }
            if (str_starts_with($question->source_id ?? '', 'admin_') && ($question->model_id !== null || $question->number !== null || $question->context_group_number !== null)) {
                throw new \InvalidArgumentException('Admin additions belong to the bank, outside the fixed source models.');
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->whereHas('bank', fn ($bank) => $bank->where('is_active', true));
    }

    public function bank()
    {
        return $this->belongsTo(ArabicSkillBank::class, 'bank_id');
    }

    public function sourceModel()
    {
        return $this->belongsTo(ArabicSkillModel::class, 'model_id');
    }

    public function sessionQuestions()
    {
        return $this->hasMany(ArabicSkillSessionQuestion::class, 'question_id');
    }
}
