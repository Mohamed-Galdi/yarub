<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    public function courses()
    {
        return $this->morphedByMany(Course::class, 'packageable');
    }

    public function lessons()
    {
        return $this->morphedByMany(Lesson::class, 'packageable');
    }
}
