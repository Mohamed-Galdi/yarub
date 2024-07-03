<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;
    protected $fillable = [
        'test_id', 'question', 'first_option', 'second_option', 'third_option', 'fourth_option', 'answer',
    ];

    public function test()
    {
        return $this->belongsTo(Test::class);
    }
}
