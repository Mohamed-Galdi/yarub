<?php

namespace App\Http\Controllers;

use App\Models\VideoLibrarySetting;

class VideoShowcaseController extends Controller
{
    public function index()
    {
        // The request cannot choose a library ID. Never fall back to the newest upload.
        $exampleTitle = VideoLibrarySetting::eligiblePublicExample()?->title;

        return response()->view('home.videos', compact('exampleTitle'))->header('Cache-Control', 'private, no-store');
    }
}
