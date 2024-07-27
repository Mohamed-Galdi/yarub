<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;
    protected $fillable = ['test_id', 'question_text', 'question', 'option_1', 'option_2', 'option_3', 'option_4', 'correct_answer'];

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
