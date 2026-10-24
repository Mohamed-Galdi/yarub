<?php

namespace App\Models;

use App\Models\Concerns\TracksArabicSkillRevision;
use Illuminate\Database\Eloquent\Model;

class ArabicSkillBank extends Model
{
    use TracksArabicSkillRevision;

    protected $fillable = ['slug', 'title', 'subtitle', 'sort_order', 'source_metadata', 'is_active', 'import_checksum', 'import_version'];

    protected $attributes = ['is_active' => true, 'content_revision' => 1];

    protected $casts = ['is_active' => 'boolean', 'source_metadata' => 'array'];

    protected $hidden = ['source_metadata', 'import_checksum', 'import_version'];

    public function models()
    {
        return $this->hasMany(ArabicSkillModel::class, 'bank_id')->orderBy('sort_order');
    }

    public function questions()
    {
        return $this->hasMany(ArabicSkillQuestion::class, 'bank_id');
    }

    public function sessions()
    {
        return $this->hasMany(ArabicSkillSession::class, 'bank_id');
    }
}
