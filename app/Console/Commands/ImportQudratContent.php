<?php

namespace App\Console\Commands;

use App\Services\Qudrat\ContentImporter;
use App\Services\Qudrat\ContentValidator;
use App\Services\Qudrat\ImportEnvironment;
use App\Services\Qudrat\ProductionImportApproval;
use Illuminate\Console\Command;
use InvalidArgumentException;
use RuntimeException;

class ImportQudratContent extends Command
{
    protected $signature = 'qudrat:import
        {--path=qudrat_content : Directory containing the six content files}
        {--dry-run : Validate content without connecting to the database}
        {--overwrite-id=* : Development only: explicit external identity to overwrite}
        {--production : Explicitly request guarded production import}
        {--database= : Exact expected production database name}
        {--sha256= : Reviewed SHA-256 digest printed by a local dry-run}
        {--force : Production only: explicitly approve a noninteractive import}';

    protected $description = 'Validate and import Qudrat content with isolated-development or explicit production safeguards';

    public function handle(ContentValidator $validator, ContentImporter $importer, ImportEnvironment $environment): int
    {
        try {
            $path = $this->option('path');
            if (! str_starts_with($path, DIRECTORY_SEPARATOR) && preg_match('/^[A-Za-z]:[\\\\\/]/', $path) !== 1) {
                $path = base_path($path);
            }
            $content = $validator->validateDirectory($path);
            $digest = $validator->digest($content);
            $this->info('Valid: 6 categories, 1,200 questions, 4,800 options, 1,200 explanations/hints and 40 passages.');
            $this->line('Content SHA-256: '.$digest);
            if ($this->option('dry-run')) {
                if ($this->option('overwrite-id') || $this->option('production') || $this->option('database') !== null
                    || $this->option('sha256') !== null || $this->option('force')) {
                    throw new InvalidArgumentException('Use --dry-run alone (with optional --path); production authorization applies only to a mutating import.');
                }

                return self::SUCCESS;
            }
            $approval = null;
            if ($this->option('production')) {
                if ($this->option('overwrite-id')) {
                    throw new InvalidArgumentException('--overwrite-id is not allowed in production mode.');
                }
                $approval = new ProductionImportApproval((string) $this->option('database'), strtolower((string) $this->option('sha256')));
                $target = $environment->productionTarget($approval, $digest);
                $destination = $target['host'].':'.$target['port'].'/'.$target['database'];
                $this->warn('Production target: '.$destination.'. New content publishes immediately; admin conflicts and attempt history are preserved.');
                if (! $this->option('force')) {
                    if (! $this->input->isInteractive() || ! $this->confirm('Import reviewed Qudrat content into '.$destination.'?', false)) {
                        $this->error('Import cancelled. No database connection was opened. Noninteractive production imports require explicit --force.');

                        return self::FAILURE;
                    }
                }
            } elseif ($this->option('database') !== null || $this->option('sha256') !== null || $this->option('force')) {
                throw new InvalidArgumentException('--database, --sha256 and --force require --production.');
            }
            $report = $importer->import($content, $this->option('overwrite-id'), $approval);
            $rows = [];
            foreach ($report['counts'] as $entity => $counts) {
                $rows[] = array_merge([$entity], array_values($counts));
            }
            $this->table(['Entity', 'Created', 'Updated', 'Unchanged', 'Skipped', 'Conflicting'], $rows);
            foreach ($report['conflicts'] as $conflict) {
                $this->warn($conflict['entity'].' '.$conflict['id'].': '.$conflict['reason']);
            }
            if ($report['conflicts']) {
                $this->warn('Conflicting records/dependencies were skipped. Resolve named IDs deliberately; publication and practice history are never reset.');

                return 2;
            }

            return self::SUCCESS;
        } catch (InvalidArgumentException|RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }
}
