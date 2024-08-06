<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomePage extends Model
{
    use HasFactory;

    protected $fillable = [
        'main_title',
        'sub_title',
        'our_features_title',
        'first_feature_title',
        'first_feature_content',
        'second_feature_title',
        'second_feature_content',
        'third_feature_title',
        'third_feature_content',
        'last_section_title',
        'last_section_content',
    ];
}
