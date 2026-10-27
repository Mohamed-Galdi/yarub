<?php

// Limits are operational settings, not a hard three-minute product duration limit.
return [
    'max_video_kib' => (int) env('VIDEO_LIBRARY_MAX_VIDEO_KIB', 256000),
    'max_cover_kib' => (int) env('VIDEO_LIBRARY_MAX_COVER_KIB', 5120),
    'max_video_dimension' => (int) env('VIDEO_LIBRARY_MAX_VIDEO_DIMENSION', 3840),
    'max_cover_dimension' => (int) env('VIDEO_LIBRARY_MAX_COVER_DIMENSION', 4096),
    'max_cover_pixels' => (int) env('VIDEO_LIBRARY_MAX_COVER_PIXELS', 8000000),
    'poster_max_width' => 720,
    'probe_timeout_seconds' => (int) env('VIDEO_LIBRARY_PROBE_TIMEOUT', 15),
    'poster_timeout_seconds' => (int) env('VIDEO_LIBRARY_POSTER_TIMEOUT', 30),
    'decode_timeout_seconds' => (int) env('VIDEO_LIBRARY_DECODE_TIMEOUT', 45),
    // Whole browser request: allow the body transfer plus bounded synchronous processing.
    'request_timeout_seconds' => (int) env('VIDEO_LIBRARY_REQUEST_TIMEOUT', 450),
    'minimum_free_bytes' => (int) env('VIDEO_LIBRARY_MINIMUM_FREE_BYTES', 1073741824),
    'free_space_reserve_ratio' => (float) env('VIDEO_LIBRARY_FREE_SPACE_RESERVE_RATIO', 0.2),
    'ffmpeg' => env('VIDEO_LIBRARY_FFMPEG', 'ffmpeg'),
    'ffprobe' => env('VIDEO_LIBRARY_FFPROBE', 'ffprobe'),
];
