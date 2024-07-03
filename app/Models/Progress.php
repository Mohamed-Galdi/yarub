<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Progress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'progressable_id', 'progressable_type', 'progress',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function progressable()
    {
        return $this->morphTo();
    }
}
