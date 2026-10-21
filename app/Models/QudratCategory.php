<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QudratCategory extends Model
{
    protected $attributes = ['is_published' => true];

    protected $fillable = [
        'slug', 'title', 'sort_order', 'is_published', 'import_checksum', 'import_version',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function passages()
    {
        return $this->hasMany(QudratPassage::class, 'category_id');
    }

    public function questions()
    {
        return $this->hasMany(QudratQuestion::class, 'category_id');
    }

    public function sessions()
    {
        return $this->hasMany(QudratPracticeSession::class, 'category_id');
    }
}
