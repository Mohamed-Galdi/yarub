<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory;

    public function student()
    {
        return $this->belongsToMany(User::class, 'student_certificate_sub')->withPivot('created_at')
        ->withTimestamps();
    }
}
