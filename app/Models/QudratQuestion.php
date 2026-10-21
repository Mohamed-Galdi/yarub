<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QudratQuestion extends Model
{
    protected $fillable = [
        'external_key', 'category_id', 'passage_id', 'prompt', 'hint', 'explanation', 'sort_order', 'correct_option_key', 'is_published', 'archived_at', 'import_checksum', 'import_version',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'archived_at' => 'datetime',
    ];

    protected $hidden = ['correct_option_key', 'hint', 'explanation', 'import_checksum', 'import_version'];

    public function scopePublished($query)
    {
        return $query->where('is_published', true)->whereNull('archived_at')
            ->whereHas('category', fn ($category) => $category->where('is_published', true));
    }

    public function category()
    {
        return $this->belongsTo(QudratCategory::class, 'category_id');
    }

    public function passage()
    {
        return $this->belongsTo(QudratPassage::class, 'passage_id');
    }

    public function options()
    {
        return $this->hasMany(QudratOption::class, 'question_id')->orderBy('sort_order');
    }

    public function sessionQuestions()
    {
        return $this->hasMany(QudratSessionQuestion::class, 'question_id');
    }
}
