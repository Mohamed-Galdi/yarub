<?php

namespace App\Services\VideoLibrary;

use App\Models\ArabicSkillBank;
use App\Models\LibraryVideo;
use App\Models\QudratCategory;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class VideoManager
{
    public function __construct(private MediaFiles $files, private MediaInspector $inspector) {}

    public function upload(User $admin, array $metadata, UploadedFile $video, ?UploadedFile $cover, string $key): LibraryVideo
    {
        Gate::forUser($admin)->authorize('create', LibraryVideo::class);
        $data = $this->metadata($metadata);
        $this->inspector->validateUpload($video);
        [$record, $token] = $this->begin($admin, null, $key, 'upload', $data);

        return $token === null ? $record : $this->process($record, $token, $key, $video, $cover, false);
    }

    public function replace(User $admin, LibraryVideo $video, ?UploadedFile $source, ?UploadedFile $cover, bool $resetCover, string $key): LibraryVideo
    {
        Gate::forUser($admin)->authorize('update', $video);
        if ($source) {
            $this->inspector->validateUpload($source);
        }
        if (! $source && ! $cover && ! $resetCover) {
            throw ValidationException::withMessages(['video' => 'اختر فيديو أو غلافًا، أو اطلب إعادة إنشاء الغلاف.']);
        }
        if ($cover && $resetCover) {
            throw ValidationException::withMessages(['cover' => 'اختر صورة أو إعادة إنشاء الغلاف، وليس كليهما.']);
        }
        [$record, $token] = $this->begin($admin, $video->id, $key, 'replace');

        return $token === null ? $record : $this->process($record, $token, $key, $source, $cover, $resetCover);
    }

    private function metadata(array $input, ?LibraryVideo $existing = null): array
    {
        $data = Validator::make($input, LibraryVideo::metadataRules())->validate();
        foreach (['qudrat_category_id' => [QudratCategory::class, 'is_published'], 'arabic_skill_bank_id' => [ArabicSkillBank::class, 'is_active']] as $field => [$class, $active]) {
            if (! empty($data[$field]) && (! $existing || (int) $existing->$field !== (int) $data[$field])
                && ! $class::whereKey($data[$field])->where($active, true)->exists()) {
                throw ValidationException::withMessages([$field => 'اختر نوعًا متاحًا.']);
            }
        }

        return array_merge(['description' => null, 'qudrat_category_id' => null, 'arabic_skill_bank_id' => null], $data);
    }

    private function begin(User $admin, ?int $id, string $key, string $kind, array $metadata = []): array
    {
        Validator::make(['request_key' => $key], ['request_key' => 'required|uuid'])->validate();

        return DB::transaction(function () use ($admin, $id, $key, $kind, $metadata) {
            DB::table('video_media_operations')->insertOrIgnore([
                'request_key' => $key, 'admin_id' => $admin->id, 'kind' => $kind,
                'state' => 'pending', 'created_at' => now(), 'updated_at' => now(),
            ]);
            $op = DB::table('video_media_operations')->where('request_key', $key)->lockForUpdate()->first();
            abort_unless($op && (int) $op->admin_id === $admin->id && $op->kind === $kind, 409, 'معرّف الطلب مستخدم.');
            abort_if($op->state === 'canceled', 409, 'أُلغي هذا الطلب. استخدم معرّف طلب جديدًا.');
            if ($op->video_id !== null) {
                abort_if($id !== null && (int) $op->video_id !== $id, 409);
                $record = LibraryVideo::whereKey($op->video_id)->lockForUpdate()->firstOrFail();
                if (in_array($op->state, ['processing', 'completed'], true)) {
                    return [$record, null];
                }
                if ($kind === 'upload' && $op->state === 'failed') {
                    $record->update($metadata);
                }
            } elseif ($kind === 'upload') {
                abort_unless($op->state === 'pending', 409);
                $record = LibraryVideo::create($metadata);
                $record->forceFill(['uploaded_by' => $admin->id])->save();
            } else {
                $record = LibraryVideo::whereKey($id)->lockForUpdate()->firstOrFail();
            }
            abort_if($record->processing_token !== null, 409, 'تجري معالجة هذا الفيديو حاليًا.');
            $token = (string) Str::uuid();
            $record->forceFill([
                'processing_token' => $token, 'processing_started_at' => now(), 'processing_error' => null,
                'processing_state' => $record->processing_state === 'ready' ? 'ready' : 'processing',
            ])->save();
            DB::table('video_media_operations')->where('request_key', $key)->update([
                'video_id' => $record->id, 'state' => 'processing', 'token' => $token, 'updated_at' => now(),
            ]);

            return [$record, $token];
        }, 3);
    }

    private function process(LibraryVideo $record, string $token, string $key, ?UploadedFile $source, ?UploadedFile $cover, bool $reset): LibraryVideo
    {
        $newVideo = $source ? 'objects/'.$token.'/video.mp4' : null;
        $newCover = null;
        $committed = false;
        try {
            $this->files->requireSpace(($source?->getSize() ?? 0) + ($cover?->getSize() ?? 0));
            $metadata = [];
            if ($source) {
                $target = $this->files->reserve($newVideo);
                $stream = fopen($source->getPathname(), 'rb');
                try {
                    if (! $this->files->disk()->put($newVideo, $stream)) {
                        throw new \RuntimeException('Cannot store video.');
                    }
                } finally {
                    fclose($stream);
                }
                $metadata = $this->inspector->inspect($target);
            }
            if ($cover || $reset || $record->cover_origin !== 'custom') {
                $newCover = 'objects/'.$token.'/cover.jpg';
                $target = $this->files->reserve($newCover);
                if ($cover) {
                    $this->inspector->cover($cover, $target);
                } else {
                    $input = $source ? $this->files->path($newVideo) : $this->files->path($record->video_path ?? '');
                    $this->inspector->poster($input, $target, $metadata ?: ['duration_seconds' => $record->duration_seconds]);
                }
            }
            $result = DB::transaction(function () use ($record, $token, $key, $newVideo, $newCover, $metadata, $cover) {
                // Lock order matches begin(): operation then video. Deletion cancels tokens.
                $op = DB::table('video_media_operations')->where('request_key', $key)->lockForUpdate()->first();
                $current = LibraryVideo::whereKey($record->id)->lockForUpdate()->first();
                abort_unless($current && $current->processing_token === $token && $op->state === 'processing' && $op->token === $token, 409, 'أُلغيت المعالجة أو حُذف الفيديو.');
                if ($newVideo) {
                    $this->files->discardLater($current->video_path);
                    $current->forceFill($metadata + ['video_path' => $newVideo]);
                }
                if ($newCover) {
                    $this->files->discardLater($current->cover_path);
                    $current->forceFill(['cover_path' => $newCover, 'cover_origin' => $cover ? 'custom' : 'generated']);
                }
                if (! $current->video_path || ! $current->cover_path) {
                    throw ValidationException::withMessages(['video' => 'ارفع فيديو صالحًا لإكمال المعالجة.']);
                }
                foreach ([$current->video_path, $current->cover_path] as $asset) {
                    if (filesize($this->files->path($asset)) <= 0) {
                        throw new \RuntimeException('A required prepared asset is empty.');
                    }
                }
                $current->forceFill([
                    'disk' => 'video_library', 'processing_state' => 'ready', 'processing_token' => null,
                    'processing_started_at' => null, 'processing_error' => null,
                    'published_at' => $current->published_at ?? now(),
                ])->save();
                DB::table('video_media_operations')->where('request_key', $key)->update(['state' => 'completed', 'token' => null, 'updated_at' => now()]);
                DB::afterCommit(fn () => $this->files->cleanup());

                return $current;
            }, 3);
            $committed = true;

            return $result;
        } catch (\Throwable $e) {
            DB::transaction(function () use ($record, $token, $key) {
                DB::table('video_media_operations')->where('request_key', $key)->where('token', $token)->where('state', 'processing')
                    ->update(['state' => 'failed', 'token' => null, 'updated_at' => now()]);
                $current = LibraryVideo::whereKey($record->id)->lockForUpdate()->first();
                if ($current && $current->processing_token === $token) {
                    $current->forceFill([
                        'processing_state' => $current->processing_state === 'ready' ? 'ready' : 'failed',
                        'processing_token' => null, 'processing_started_at' => null,
                        'processing_error' => 'تعذّرت المعالجة. أعد المحاولة بملف صالح أو تواصل مع المسؤول.',
                    ])->save();
                }
            });
            if ($e instanceof ValidationException || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Video processing failed', ['video_id' => $record->id, 'exception' => $e]);
            throw ValidationException::withMessages(['video' => 'تعذّرت المعالجة. أعد المحاولة أو تواصل مع المسؤول.']);
        } finally {
            if (! $committed) {
                foreach ([$newVideo, $newCover] as $path) {
                    $this->files->discardLater($path);
                }
                DB::afterCommit(fn () => $this->files->cleanup());
            }
        }
    }

    public function updateMetadata(User $admin, LibraryVideo $video, array $metadata): LibraryVideo
    {
        Gate::forUser($admin)->authorize('update', $video);

        return DB::transaction(function () use ($video, $metadata) {
            $current = LibraryVideo::whereKey($video->id)->lockForUpdate()->firstOrFail();
            $current->update($this->metadata($metadata, $current));

            return $current;
        });
    }

    public function visibility(User $admin, LibraryVideo $video, bool $visible): LibraryVideo
    {
        Gate::forUser($admin)->authorize('update', $video);

        return DB::transaction(function () use ($video, $visible) {
            $current = LibraryVideo::whereKey($video->id)->lockForUpdate()->firstOrFail();
            $current->forceFill(['is_visible' => $visible])->save();

            return $current;
        });
    }

    public function delete(User $admin, LibraryVideo $video, string $confirmation): void
    {
        Gate::forUser($admin)->authorize('delete', $video);
        DB::transaction(function () use ($video, $confirmation) {
            // Same operation-before-video lock ordering as the processing lifecycle.
            DB::table('video_media_operations')->where('video_id', $video->id)->update(['state' => 'canceled', 'token' => null, 'updated_at' => now()]);
            $current = LibraryVideo::whereKey($video->id)->lockForUpdate()->firstOrFail();
            if (! hash_equals($current->title, $confirmation)) {
                throw ValidationException::withMessages(['confirmation' => 'اكتب عنوان الفيديو لتأكيد الحذف.']);
            }
            $this->files->discardLater($current->video_path);
            $this->files->discardLater($current->cover_path);
            $current->delete();
            DB::afterCommit(fn () => $this->files->cleanup());
        }, 3);
    }

    public function recoverInterrupted(): int
    {
        $count = 0;
        // One hour exceeds all bounded probe/decode/poster budgets. Old tokens cannot publish.
        $operations = DB::table('video_media_operations')->where('state', 'processing')->where('updated_at', '<', now()->subHour())->limit(100)->pluck('request_key');
        foreach ($operations as $key) {
            DB::transaction(function () use ($key, &$count) {
                $op = DB::table('video_media_operations')->where('request_key', $key)->lockForUpdate()->first();
                if (! $op || $op->state !== 'processing' || $op->updated_at >= now()->subHour()->toDateTimeString()) {
                    return;
                }
                $video = LibraryVideo::whereKey($op->video_id)->lockForUpdate()->first();
                if ($video && $video->processing_token === $op->token) {
                    $video->forceFill(['processing_token' => null, 'processing_started_at' => null,
                        'processing_state' => $video->processing_state === 'ready' ? 'ready' : 'failed',
                        'processing_error' => 'انقطعت المعالجة. أعد المحاولة بملف الفيديو.'])->save();
                }
                DB::table('video_media_operations')->where('request_key', $key)->update(['state' => 'failed', 'token' => null, 'updated_at' => now()]);
                $count++;
            });
        }

        return $count;
    }
}
