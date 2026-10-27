<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LibraryVideo extends Model
{
    protected $fillable = ['title', 'description', 'qudrat_category_id', 'arabic_skill_bank_id'];

    protected $attributes = ['disk' => 'video_library', 'processing_state' => 'pending', 'is_visible' => true];

    // Never expose storage details or processing diagnostics through generic serialization.
    protected $hidden = ['disk', 'video_path', 'cover_path', 'codec_metadata', 'processing_error', 'processing_token'];

    protected $casts = [
        'is_visible' => 'boolean', 'published_at' => 'datetime', 'codec_metadata' => 'array',
        'duration_seconds' => 'float', 'width' => 'integer', 'height' => 'integer', 'byte_size' => 'integer',
        'processing_started_at' => 'datetime',
    ];

    public static function metadataRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'qudrat_category_id' => ['nullable', 'required_without:arabic_skill_bank_id', 'prohibits:arabic_skill_bank_id', 'integer', Rule::exists('qudrat_categories', 'id')],
            'arabic_skill_bank_id' => ['nullable', 'required_without:qudrat_category_id', 'prohibits:qudrat_category_id', 'integer', Rule::exists('arabic_skill_banks', 'id')],
            'program' => ['prohibited'],
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $video) {
            Validator::make($video->only(['title', 'description', 'qudrat_category_id', 'arabic_skill_bank_id']), static::metadataRules())->validate();
        });
    }

    public function qudratCategory()
    {
        return $this->belongsTo(QudratCategory::class);
    }

    public function arabicSkillBank()
    {
        return $this->belongsTo(ArabicSkillBank::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by')->withTrashed();
    }

    public function getProgramAttribute(): string
    {
        return $this->qudrat_category_id !== null ? 'qudrat' : 'arabic_skills';
    }

    public function scopeEligible(Builder $query): Builder
    {
        return $query->where('processing_state', 'ready')->where('is_visible', true)
            ->where(function (Builder $types) {
                $types->whereHas('qudratCategory', fn (Builder $q) => $q->where('is_published', true))
                    ->orWhereHas('arabicSkillBank', fn (Builder $q) => $q->where('is_active', true));
            });
    }

    public function isEligible(): bool
    {
        // Re-query to honor category/visibility changes even when this instance is stale.
        return $this->exists && static::eligible()->whereKey($this->getKey())->exists();
    }

    public function scopeNewestPublished(Builder $query): Builder
    {
        return $query->orderByDesc('published_at')->orderByDesc('id');
    }

    public function scopeNewestUploaded(Builder $query): Builder
    {
        return $query->orderByDesc('created_at')->orderByDesc('id');
    }
}
