<?php

namespace App\Console\Commands;

use App\Services\Qudrat\ContentImporter;
use App\Services\Qudrat\ContentValidator;
use Illuminate\Console\Command;
use InvalidArgumentException;
use RuntimeException;

class ImportQudratContent extends Command
{
    protected $signature = 'qudrat:import
        {--path=qudrat_content : Directory containing the six content files}
        {--dry-run : Validate content without connecting to the database}
        {--overwrite-id=* : Explicit external question/passage key or category slug to overwrite}';

    protected $description = 'Validate Qudrat content and import automatically published content into an isolated development database';

    public function handle(ContentValidator $validator, ContentImporter $importer): int
    {
        try {
            $path = $this->option('path');
            if (! str_starts_with($path, DIRECTORY_SEPARATOR) && preg_match('/^[A-Za-z]:[\\\\\/]/', $path) !== 1) {
                $path = base_path($path);
            }
            $content = $validator->validateDirectory($path);
            $this->info('Valid: 6 categories, 1,200 questions, 4,800 options, 1,200 explanations/hints and 40 passages.');
            if ($this->option('dry-run')) {
                if ($this->option('overwrite-id')) {
                    throw new InvalidArgumentException('--overwrite-id cannot be combined with --dry-run.');
                }

                return self::SUCCESS;
            }
            $report = $importer->import($content, $this->option('overwrite-id'));
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
