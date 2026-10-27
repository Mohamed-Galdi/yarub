<?php

namespace App\Services\VideoLibrary;

use App\Models\LibraryVideo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class MediaFiles
{
    public function disk()
    {
        if (config('filesystems.disks.video_library.driver') !== 'local') {
            throw new RuntimeException('Video delivery requires the dedicated local private disk.');
        }

        return Storage::disk('video_library');
    }

    public function path(string $relative, bool $mustExist = true): string
    {
        if (! preg_match('~^objects/[a-f0-9-]{36}/(video\.mp4|cover\.jpg)$~D', $relative)) {
            throw new RuntimeException('Invalid managed video path.');
        }
        $disk = $this->disk();
        $disk->makeDirectory('objects');
        $root = realpath($disk->path(''));
        $normalized = str_replace('\\', '/', (string) $root).'/';
        $publicStorage = realpath(storage_path('app/public')) ?: storage_path('app/public');
        if ($root === false || str_starts_with($normalized, str_replace('\\', '/', public_path()).'/')
            || str_starts_with($normalized, str_replace('\\', '/', $publicStorage).'/')) {
            throw new RuntimeException('Video storage must be outside the public document root.');
        }
        $candidate = $disk->path($relative);
        // Check existing ancestors too: a symlink must never redirect a future write/delete.
        $ancestor = $candidate;
        while (! file_exists($ancestor) && ! is_link($ancestor)) {
            $ancestor = dirname($ancestor);
        }
        $resolved = realpath($ancestor);
        if ($resolved === false || ! str_starts_with(str_replace('\\', '/', $resolved).'/', str_replace('\\', '/', $root).'/')) {
            throw new RuntimeException('Managed media escapes private storage.');
        }
        if ($mustExist && (! is_file($candidate) || is_link($candidate))) {
            throw new RuntimeException('Managed media is unavailable.');
        }

        return $candidate;
    }

    public function reserve(string $relative): string
    {
        $path = $this->path($relative, false);
        // Write-ahead ledger: a killed request leaves a known, reclaimable candidate.
        DB::table('video_asset_cleanups')->insertOrIgnore([
            'path' => $relative, 'available_after' => now()->addHour(), 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->disk()->makeDirectory(dirname($relative));

        return $path;
    }

    public function requireSpace(int $incomingBytes): void
    {
        $disk = $this->disk();
        $disk->makeDirectory('objects');
        $free = disk_free_space($disk->path(''));
        $total = disk_total_space($disk->path(''));
        $ratio = max(0, min(1, (float) config('video_library.free_space_reserve_ratio')));
        $reserve = max((int) config('video_library.minimum_free_bytes'), (int) ($total * $ratio));
        if ($free === false || $free - $incomingBytes - 16777216 < $reserve) {
            throw ValidationException::withMessages(['video' => 'المساحة المتاحة غير كافية. تواصل مع مسؤول الخادم.']);
        }
    }

    public function discardLater(?string $relative): void
    {
        if ($relative === null) {
            return;
        }
        $this->path($relative, false);
        DB::table('video_asset_cleanups')->updateOrInsert(['path' => $relative], [
            'available_after' => now(), 'updated_at' => now(),
        ]);
    }

    public function cleanup(int $limit = 100): int
    {
        $failed = 0;
        foreach (DB::table('video_asset_cleanups')->where('available_after', '<=', now())->orderBy('id')->limit($limit)->get() as $item) {
            try {
                // Never delete an asset referenced by a committed record.
                if (LibraryVideo::where('video_path', $item->path)->orWhere('cover_path', $item->path)->exists()) {
                    DB::table('video_asset_cleanups')->where('id', $item->id)->delete();
                    continue;
                }
                $this->path($item->path, false);
                if (! $this->disk()->delete($item->path)) {
                    throw new RuntimeException('Filesystem refused managed file removal.');
                }
                // The UUID directory contains only our two files; nonrecursive removal is safe.
                @rmdir(dirname($this->disk()->path($item->path)));
                DB::table('video_asset_cleanups')->where('id', $item->id)->delete();
            } catch (\Throwable $e) {
                $failed++;
                DB::table('video_asset_cleanups')->where('id', $item->id)->update([
                    'attempts' => DB::raw('attempts + 1'), 'last_error' => 'Managed file cleanup failed; inspect private server logs.',
                    'available_after' => now()->addMinutes(10), 'updated_at' => now(),
                ]);
                Log::warning('Video asset cleanup failed', ['cleanup_id' => $item->id, 'exception' => $e]);
            }
        }

        return $failed;
    }
}
