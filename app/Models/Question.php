<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;
    protected $fillable = ['test_id', 'question', 'options', 'correct_answers', 'is_multiple_choice'];

    protected $casts = [
        'options' => 'array',
        'correct_answers' => 'array',
        'is_multiple_choice' => 'boolean',
    ];

    public function test()
    {
        return $this->belongsTo(Test::class);
    }
}
