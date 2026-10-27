<?php

namespace App\Services\VideoLibrary;

use App\Models\ArabicSkillBank;
use App\Models\LibraryVideo;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class SeedPackage
{
    public const TYPES = ['برهان' => 'burhan', 'رسم' => 'rasm', 'مداد' => 'midad', 'نسق' => 'nasaq'];

    public function scan(string $directory): array
    {
        $root = realpath($directory);
        if (! $root || ! is_dir($root) || is_link($directory)) throw new RuntimeException('Package directory missing or linked.');
        $items = [];
        $used = [];
        foreach (scandir($root) as $name) {
            if (! str_ends_with($name, '.mp4')) continue;
            $base = substr($name, 0, -4);
            $parts = explode(' - ', $base, 2);
            if (count($parts) !== 2 || ! isset(self::TYPES[$parts[0]]) || trim($parts[1]) === '' || mb_strlen($parts[1]) > 255) {
                throw new RuntimeException('Unrecognized type/title: '.$name);
            }
            $cover = $base.'.jpg';
            $hashes = [];
            foreach ([$name, $cover] as $file) {
                if (! is_file($root.'/'.$file) || is_link($root.'/'.$file) || filesize($root.'/'.$file) < 1) throw new RuntimeException('Missing/unsafe pair: '.$file);
                $hashes[] = hash_file('sha256', $root.'/'.$file);
                $used[$file] = true;
            }
            $items[] = ['filename' => $name, 'cover' => $cover, 'title' => $parts[1], 'type' => $parts[0], 'slug' => self::TYPES[$parts[0]], 'hashes' => $hashes];
        }
        if (! $items) throw new RuntimeException('No MP4/JPG pairs found.');
        foreach (scandir($root) as $name) {
            if (! in_array($name, ['.', '..'], true) && ! isset($used[$name])) throw new RuntimeException('Unexpected package entry: '.$name);
        }
        usort($items, fn ($a, $b) => strcmp($a['filename'], $b['filename']));

        return ['root' => $root, 'items' => $items, 'digest' => hash('sha256', json_encode($items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR))];
    }

    public function import(array $package, User $admin, callable $progress): array
    {
        if ($admin->role !== 'admin') throw new RuntimeException('Select an existing administrator.');
        $banks = ArabicSkillBank::whereIn('slug', array_column($package['items'], 'slug'))->get()->keyBy('slug');
        // Preflight the entire mapping before any write; never create or republish course classifications.
        foreach ($package['items'] as $item) {
            if (! isset($banks[$item['slug']]) || ! $banks[$item['slug']]->is_active) throw new RuntimeException('Missing/inactive Arabic Skills bank: '.$item['slug']);
        }
        $counts = ['imported' => 0, 'skipped' => 0];
        foreach ($package['items'] as $item) {
            $identity = hash('sha256', 'yarub-video-seed-v1:'.$item['filename']);
            $checksum = hash('sha256', json_encode($item, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
            $ledger = DB::table('video_seed_imports')->where('identity', $identity)->first();
            if ($ledger && ! hash_equals($ledger->source_checksum, $checksum)) throw new RuntimeException('Source changed; explicit admin review required: '.$item['filename']);
            if (! $ledger) {
                DB::table('video_seed_imports')->insertOrIgnore(['identity' => $identity, 'source_checksum' => $checksum, 'request_key' => (string) Str::uuid(), 'created_at' => now(), 'updated_at' => now()]);
                $ledger = DB::table('video_seed_imports')->where('identity', $identity)->first();
                if (! hash_equals($ledger->source_checksum, $checksum)) throw new RuntimeException('Concurrent source conflict.');
            }
            $operation = DB::table('video_media_operations')->where('request_key', $ledger->request_key)->first();
            if ($operation && $operation->state === 'canceled') throw new RuntimeException('Previously deleted import will not be recreated: '.$item['filename']);
            if ($operation && $operation->state === 'completed') {
                if (! LibraryVideo::whereKey($operation->video_id)->exists()) throw new RuntimeException('Imported video missing; review restore state.');
                $counts['skipped']++;
                $progress('Unchanged (admin edits preserved)', $item, $operation->video_id);
                continue;
            }
            if ($operation && (int) $operation->admin_id !== $admin->id) throw new RuntimeException('Resume an unfinished import with its original --admin.');
            if ($operation && $operation->state === 'processing') throw new RuntimeException('Import still processing; inspect status/cleanup before retrying.');
            $video = app(VideoManager::class)->upload($admin, ['title' => $item['title'], 'arabic_skill_bank_id' => $banks[$item['slug']]->id],
                new UploadedFile($package['root'].'/'.$item['filename'], $item['filename'], null, null, true),
                new UploadedFile($package['root'].'/'.$item['cover'], $item['cover'], null, null, true), $ledger->request_key);
            $counts['imported']++;
            $progress('Imported', $item, $video->id);
        }

        return $counts;
    }
}
