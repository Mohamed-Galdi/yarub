<?php

namespace App\Models;

use App\Models\Concerns\TracksArabicSkillRevision;
use Illuminate\Database\Eloquent\Model;

class ArabicSkillModel extends Model
{
    use TracksArabicSkillRevision;

    protected $fillable = ['bank_id', 'source_id', 'number', 'title', 'sort_order', 'import_checksum', 'import_version'];

    protected $attributes = ['content_revision' => 1];

    protected $hidden = ['import_checksum', 'import_version'];

    public function bank()
    {
        return $this->belongsTo(ArabicSkillBank::class, 'bank_id');
    }

    public function questions()
    {
        return $this->hasMany(ArabicSkillQuestion::class, 'model_id')->orderBy('number');
    }

    public function sessions()
    {
        return $this->hasMany(ArabicSkillSession::class, 'model_id');
    }
}
