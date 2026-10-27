<?php

namespace App\Services\VideoLibrary;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Process\Process;

class MediaInspector
{
    public function validateUpload(UploadedFile $file): void
    {
        if (! $file->isValid() || strtolower($file->getClientOriginalExtension()) !== 'mp4'
            || $file->getSize() <= 0 || $file->getSize() > config('video_library.max_video_kib') * 1024
            || ! in_array($file->getMimeType(), ['video/mp4', 'application/mp4'], true)) {
            $this->invalid('ارفع ملف MP4 صالحًا ضمن الحجم المسموح.');
        }
    }

    public function inspect(string $path): array
    {
        $this->checkContainer($path);
        $json = $this->run([
            config('video_library.ffprobe'), '-v', 'error', '-protocol_whitelist', 'file', '-format_whitelist', 'mov',
            '-enable_drefs', '0', '-use_absolute_path', '0', '-show_entries',
            'format=duration:stream=codec_type,codec_name,profile,pix_fmt,level,width,height,field_order,avg_frame_rate,sample_aspect_ratio,channels,sample_rate:stream_disposition=attached_pic:stream_side_data=rotation',
            '-of', 'json', '-i', $path,
        ], config('video_library.probe_timeout_seconds'), true);
        $data = json_decode($json, true, 32, JSON_THROW_ON_ERROR);
        $streams = $data['streams'] ?? [];
        $videos = array_values(array_filter($streams, fn ($s) => ($s['codec_type'] ?? '') === 'video'));
        $audio = array_values(array_filter($streams, fn ($s) => ($s['codec_type'] ?? '') === 'audio'));
        if (count($videos) !== 1 || count($audio) > 1 || count($streams) !== count($videos) + count($audio)) {
            $this->invalid('يجب أن يحتوي الملف على مسار فيديو واحد ومسار صوت اختياري فقط.');
        }
        $v = $videos[0];
        [$num, $den] = array_pad(explode('/', $v['avg_frame_rate'] ?? '0/0'), 2, '1');
        $fps = (float) $den > 0 ? (float) $num / (float) $den : 0;
        $duration = (float) ($data['format']['duration'] ?? 0);
        if (($v['codec_name'] ?? '') !== 'h264' || ! in_array($v['profile'] ?? '', ['Baseline', 'Constrained Baseline', 'Main', 'High'], true)
            || ($v['pix_fmt'] ?? '') !== 'yuv420p' || ($v['level'] ?? 0) <= 0 || $v['level'] > 42
            || ! in_array($v['field_order'] ?? 'unknown', ['unknown', 'progressive'], true)
            || ! is_finite($fps) || $fps <= 0 || $fps > 60
            || ! is_finite($duration) || $duration <= 0 || ($v['disposition']['attached_pic'] ?? 0)) {
            $this->invalid('صدّر MP4 بترميز H.264 ‏8-bit yuv420p، مستوى 4.2 أو أقل، وفيديو تدريجي حتى 60 إطارًا/ثانية.');
        }
        if (($v['width'] ?? 0) <= 0 || ($v['height'] ?? 0) <= $v['width']
            || max($v['width'], $v['height']) > config('video_library.max_video_dimension')
            || ! in_array($v['sample_aspect_ratio'] ?? '1:1', ['1:1', 'N/A'], true)
            || array_filter($v['side_data_list'] ?? [], fn ($s) => (float) ($s['rotation'] ?? 0) != 0)) {
            $this->invalid('استخدم فيديو عموديًا ببكسلات مربعة ودون بيانات تدوير، ضمن الأبعاد المسموحة.');
        }
        if ($audio && (($audio[0]['codec_name'] ?? '') !== 'aac' || ($audio[0]['profile'] ?? '') !== 'LC'
            || ($audio[0]['channels'] ?? 0) < 1 || $audio[0]['channels'] > 2
            || (int) ($audio[0]['sample_rate'] ?? 0) > 48000)) {
            $this->invalid('استخدم صوت AAC-LC أحاديًا أو ثنائيًا بتردد لا يتجاوز 48 kHz، أو احذف مسار الصوت.');
        }
        // Decode all tracks; corrupt/truncated data must not pass on headers alone.
        $this->run(array_merge($this->input($path), ['-map', '0:v:0', '-map', '0:a?', '-f', 'null', '-']), config('video_library.decode_timeout_seconds'));

        return ['duration_seconds' => $duration, 'width' => $v['width'], 'height' => $v['height'],
            'byte_size' => filesize($path), 'mime_type' => 'video/mp4', 'codec_metadata' => ['video' => $v, 'audio' => $audio[0] ?? null]];
    }

    public function poster(string $source, string $target, array $metadata): void
    {
        $seek = $metadata['duration_seconds'] < .2 ? 0 : min(1.0, $metadata['duration_seconds'] / 3);
        // thumbnail picks a representative frame from a bounded candidate group.
        $filter = 'thumbnail=20,scale=min('.(int) config('video_library.poster_max_width').'\,iw):-2:out_range=full,setsar=1,format=yuvj420p';
        $this->run(array_merge($this->input($source, $seek), ['-map', '0:v:0', '-an', '-vf', $filter,
            '-frames:v', '1', '-c:v', 'mjpeg', '-threads', '1', '-pix_fmt', 'yuvj420p', '-color_range', 'pc', '-q:v', '3', '-y', $target]), config('video_library.poster_timeout_seconds'));
        if (! is_file($target) || ! @getimagesize($target)) {
            $this->invalid('تعذّر إنشاء الغلاف. حاول رفع الفيديو مرة أخرى.');
        }
    }

    public function cover(UploadedFile $file, string $target): void
    {
        $info = @getimagesize($file->getPathname());
        if (! $file->isValid() || $file->getSize() <= 0 || $file->getSize() > config('video_library.max_cover_kib') * 1024
            || ! in_array(strtolower($file->getClientOriginalExtension()), ['jpg', 'jpeg', 'png', 'webp'], true)
            || ! $info || ! in_array($info['mime'], ['image/jpeg', 'image/png', 'image/webp'], true)
            || max($info[0], $info[1]) > config('video_library.max_cover_dimension')
            || $info[0] * $info[1] > config('video_library.max_cover_pixels')) {
            throw ValidationException::withMessages(['cover' => 'استخدم صورة JPEG أو PNG أو WebP ضمن الحجم والأبعاد المسموحة.']);
        }
        $bytes = file_get_contents($file->getPathname());
        $memoryLimit = $this->memoryLimit();
        if ($memoryLimit > 0 && memory_get_usage(true) + $info[0] * $info[1] * 8 + 16777216 > $memoryLimit) {
            throw ValidationException::withMessages(['cover' => 'صورة الغلاف كبيرة للمعالجة. استخدم صورة أصغر.']);
        }
        // Reject animation chunks; all accepted input is decoded and re-encoded as JPEG.
        if (($info['mime'] === 'image/png' && str_contains($bytes, 'acTL'))
            || ($info['mime'] === 'image/webp' && str_contains($bytes, 'ANIM'))) {
            throw ValidationException::withMessages(['cover' => 'استخدم صورة ثابتة للغلاف.']);
        }
        $image = @imagecreatefromstring($bytes);
        if (! $image) {
            throw ValidationException::withMessages(['cover' => 'تعذّر قراءة صورة الغلاف.']);
        }
        try {
            $width = min($info[0], (int) config('video_library.poster_max_width'));
            $height = max(1, (int) round($info[1] * $width / $info[0]));
            $canvas = imagecreatetruecolor($width, $height);
            imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
            imagecopyresampled($canvas, $image, 0, 0, 0, 0, $width, $height, $info[0], $info[1]);
            try {
                if (! imagejpeg($canvas, $target, 85)) {
                    throw new \RuntimeException('Unable to write sanitized cover.');
                }
            } finally {
                imagedestroy($canvas);
            }
        } finally {
            imagedestroy($image);
        }
    }

    private function input(string $path, ?float $seek = null): array
    {
        return array_merge([config('video_library.ffmpeg'), '-nostdin', '-hide_banner', '-v', 'error', '-xerror', '-err_detect', 'explode', '-threads', '2',
            '-protocol_whitelist', 'file', '-format_whitelist', 'mov', '-enable_drefs', '0', '-use_absolute_path', '0', '-noautorotate'],
            $seek === null ? [] : ['-ss', (string) $seek], ['-i', $path]);
    }

    private function run(array $args, int $timeout, bool $capture = false): string
    {
        $process = new Process($args);
        $process->setTimeout(min(300, max(1, $timeout)));
        $output = '';
        $diagnostic = '';
        try {
            $process->run(function ($type, $bytes) use (&$output, &$diagnostic, $capture, $process) {
                if ($capture && $type === Process::OUT) {
                    $output .= $bytes;
                    if (strlen($output) > 65536) {
                        throw new \RuntimeException('Media inspection output exceeded limit.');
                    }
                }
                if ($type === Process::ERR) {
                    $diagnostic = substr($diagnostic.$bytes, -8192);
                }
                $process->clearOutput();
                $process->clearErrorOutput();
            });
            if (! $process->isSuccessful()) {
                Log::debug('Video media tool rejected input', ['exit_code' => $process->getExitCode(), 'diagnostic' => $diagnostic]);
                $this->invalid('تعذّر فحص الفيديو. تأكد من سلامته وإعدادات التصدير المطلوبة.');
            }
        } catch (\Throwable $e) {
            if ($process->isRunning()) {
                $process->stop(0);
            }
            if ($e instanceof ValidationException) {
                throw $e;
            }
            $this->invalid('تعذّرت معالجة الفيديو ضمن الوقت المتاح. تحقق من الملف أو تواصل مع المسؤول.');
        }

        return $output;
    }

    private function checkContainer(string $path): void
    {
        $handle = fopen($path, 'rb');
        try {
            $size = filesize($path);
            $offset = 0;
            $moov = $mdat = $ftyp = false;
            for ($atoms = 0; $offset < $size && $atoms < 2048; $atoms++) {
                fseek($handle, $offset);
                $header = fread($handle, 8);
                if (strlen($header) !== 8) {
                    $this->invalid('ملف MP4 غير مكتمل.');
                }
                $length = unpack('N', substr($header, 0, 4))[1];
                $type = substr($header, 4, 4);
                $headerSize = 8;
                if ($length === 1) {
                    $extended = fread($handle, 8);
                    if (strlen($extended) !== 8) {
                        $this->invalid('ملف MP4 غير مكتمل.');
                    }
                    $parts = unpack('Nhigh/Nlow', $extended);
                    if ($parts['high'] !== 0) {
                        $this->invalid('حجم ملف MP4 غير مدعوم.');
                    }
                    $length = $parts['low'];
                    $headerSize = 16;
                } elseif ($length === 0) {
                    $length = $size - $offset;
                }
                if ($length < $headerSize || $length > $size - $offset) {
                    $this->invalid('ملف MP4 تالف أو غير مكتمل.');
                }
                if ($type === 'ftyp') {
                    $brand = fread($handle, 4);
                    $ftyp = in_array($brand, ['isom', 'iso2', 'mp41', 'mp42', 'avc1', 'M4V '], true);
                }
                if ($type === 'moov') {
                    $moov = true;
                }
                if ($type === 'mdat') {
                    if (! $moov) {
                        $this->invalid('صدّر MP4 مع خيار Fast Start أو Web Optimized لتشغيله مباشرة.');
                    }
                    $mdat = true;
                }
                $offset += $length;
            }
            if ($offset !== $size || ! $ftyp || ! $moov || ! $mdat) {
                $this->invalid('استخدم ملف MP4 قياسيًا صالحًا مع Fast Start.');
            }
        } finally {
            fclose($handle);
        }
    }

    private function invalid(string $message): never
    {
        throw ValidationException::withMessages(['video' => $message]);
    }

    private function memoryLimit(): int
    {
        $limit = trim(ini_get('memory_limit'));
        $number = (int) $limit;

        return match (strtolower(substr($limit, -1))) {
            'g' => $number * 1073741824,
            'm' => $number * 1048576,
            'k' => $number * 1024,
            default => $number,
        };
    }
}
