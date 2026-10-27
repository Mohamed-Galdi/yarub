<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Qudrat\ImportEnvironment;
use App\Services\VideoLibrary\SeedPackage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class ImportVideoLibrary extends Command
{
    protected $signature = 'video-library:import {--path=vids} {--dry-run} {--admin= : Existing administrator ID or email} {--production} {--database=} {--sha256=}';

    protected $description = 'Import paired MP4/JPG files privately, preserving imported identities and administrator edits';

    public function handle(SeedPackage $seeds): int
    {
        try {
            $path = (string) $this->option('path');
            if (! str_starts_with($path, '/') && ! preg_match('/^[A-Za-z]:[\\\\\/]/', $path)) $path = base_path($path);
            $package = $seeds->scan($path);
            $this->line('Package SHA-256: '.$package['digest']);
            $this->table(['Type', 'Title'], array_map(fn ($item) => [$item['type'], $item['title']], $package['items']));
            if ($this->option('dry-run')) {
                $this->info(count($package['items']).' pairs verified by SHA-256; no database connection or media decoding.');
                return self::SUCCESS;
            }
            if ($this->option('production')) {
                if (! app()->environment('production') || getenv('VIDEO_LIBRARY_ALLOW_PRODUCTION_IMPORT') !== 'true'
                    || ! hash_equals($package['digest'], (string) $this->option('sha256'))) throw new \RuntimeException('Production requires APP_ENV=production, process opt-in and reviewed --sha256.');
                $raw = config('database.connections.'.DB::getDefaultConnection());
                if (($raw['driver'] ?? '') !== 'mysql' || empty($raw['host']) || empty($this->option('database')) || ($raw['database'] ?? '') !== $this->option('database')
                    || ! empty($raw['url']) || ! empty($raw['unix_socket']) || ! empty($raw['read']) || ! empty($raw['write'])) throw new \RuntimeException('Exact --database and one explicit MySQL target are required.');
                $target = $raw['host'].':'.($raw['port'] ?? 3306).'/'.$raw['database'];
                if (! $this->input->isInteractive() || ! $this->confirm('Import into '.$target.'? Videos become available to authenticated students; public example stays unchanged.', false)) return self::FAILURE;
                if (DB::selectOne('SELECT DATABASE() AS name')->name !== $this->option('database')) throw new \RuntimeException('Connected database mismatch.');
            } else {
                if ($this->option('database') || $this->option('sha256')) throw new \RuntimeException('Production target options require --production.');
                app(ImportEnvironment::class)->assertSafe();
            }
            $identity = (string) $this->option('admin');
            if ($identity === '') throw new \RuntimeException('--admin must identify an existing administrator.');
            $admin = User::where(ctype_digit($identity) ? 'id' : 'email', $identity)->where('role', 'admin')->firstOrFail();
            $counts = $seeds->import($package, $admin, fn ($status, $item, $id) => $this->line($status.' #'.$id.' '.$item['title']));
            $this->info("Imported {$counts['imported']}; skipped {$counts['skipped']}. Public example unchanged.");
            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());
            $this->warn('Import stopped. Earlier successful items remain; rerun the same package to resume without duplication.');
            return self::FAILURE;
        }
    }
}
