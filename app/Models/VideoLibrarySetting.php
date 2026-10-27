<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoLibrarySetting extends Model
{
    public $incrementing = false;

    protected $guarded = ['*'];

    public function publicExample()
    {
        return $this->belongsTo(LibraryVideo::class, 'public_example_video_id');
    }

    public static function eligiblePublicExample(): ?LibraryVideo
    {
        return LibraryVideo::eligible()->whereIn('id', static::whereKey(1)->select('public_example_video_id'))->first();
    }
}
