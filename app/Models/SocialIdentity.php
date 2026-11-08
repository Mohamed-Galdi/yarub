<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialIdentity extends Model
{
    protected $fillable = ['user_id', 'provider', 'provider_id'];

    protected $hidden = ['provider_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
