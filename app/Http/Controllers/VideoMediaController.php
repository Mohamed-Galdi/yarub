<?php

namespace App\Http\Controllers;

use App\Models\LibraryVideo;
use App\Models\VideoLibrarySetting;
use App\Services\VideoLibrary\MediaFiles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class VideoMediaController extends Controller
{
    public function student(Request $request, LibraryVideo $video, string $asset, MediaFiles $files)
    {
        // Same response for hidden, failed and missing IDs; no protected metadata leakage.
        $video = $video->fresh();
        abort_unless($video && Gate::allows('view', $video), 404);

        return $this->deliver($request, $video, $asset, $files);
    }

    public function admin(Request $request, LibraryVideo $video, string $asset, MediaFiles $files)
    {
        $this->authorize('preview', $video);
        $video = $video->fresh();
        abort_unless($video, 404);

        return $this->deliver($request, $video, $asset, $files);
    }

    public function example(Request $request, string $asset, MediaFiles $files)
    {
        $video = VideoLibrarySetting::eligiblePublicExample();
        abort_unless($video && Gate::allows('viewPublicExample', $video), 404);

        return $this->deliver($request, $video, $asset, $files);
    }

    private function deliver(Request $request, LibraryVideo $video, string $asset, MediaFiles $files)
    {
        abort_unless($video->disk === 'video_library', 404);
        try {
            $path = $files->path(($asset === 'media' ? $video->video_path : $video->cover_path) ?? '');
        } catch (\RuntimeException $e) {
            abort(404);
        }
        $size = filesize($path);
        abort_unless($size > 0, 404);
        // The pinned Symfony parser coerces malformed ranges to integers. Validate first.
        // Multipart ranges may legally be ignored, serving a normal 200 instead.
        $range = $request->headers->get('Range');
        if ($request->isMethod('GET') && $range !== null && ! $request->headers->has('If-Range')) {
            if (! preg_match('/^bytes=(\d*)-(\d*)$/D', $range, $match) || ($match[1] === '' && $match[2] === '')) {
                $request->headers->remove('Range');
            } else {
                $start = $match[1] === '' ? null : (float) $match[1];
                $end = $match[2] === '' ? null : (float) $match[2];
                if (($start !== null && ($start >= $size || ($end !== null && $start > $end)))
                    || ($start === null && $end === 0.0)) {
                    return response('', 416, ['Content-Range' => 'bytes */'.$size, 'Accept-Ranges' => 'bytes', 'Content-Length' => '0', 'Cache-Control' => 'private, no-store']);
                }
                if ($start === null && $end > $size) {
                    $request->headers->set('Range', 'bytes=0-'.($size - 1));
                }
            }
        }
        // No X-Sendfile trust: client-controlled forwarding headers cannot select a path.
        return new BinaryFileResponse($path, 200, [
            'Content-Type' => $asset === 'media' ? 'video/mp4' : 'image/jpeg',
            'Content-Disposition' => 'inline; filename="'.($asset === 'media' ? 'video.mp4' : 'cover.jpg').'"',
            'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff',
        ], false, null, false, false);
    }
}
