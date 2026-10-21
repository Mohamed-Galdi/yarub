<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QudratOption extends Model
{
    protected $fillable = [
        'question_id', 'local_key', 'text', 'sort_order',
    ];

    public function question()
    {
        return $this->belongsTo(QudratQuestion::class, 'question_id');
    }
}
