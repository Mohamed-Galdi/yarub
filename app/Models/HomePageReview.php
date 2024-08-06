<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomePageReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'review',
        'reviewer_name',
        'reviewer_image',
        'stars',
    ];
}
