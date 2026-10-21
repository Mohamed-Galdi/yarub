<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QudratPassage extends Model
{
    protected $fillable = [
        'external_key', 'category_id', 'text', 'sort_order', 'import_checksum', 'import_version',
    ];

    public function category()
    {
        return $this->belongsTo(QudratCategory::class, 'category_id');
    }

    public function questions()
    {
        return $this->hasMany(QudratQuestion::class, 'passage_id');
    }
}
