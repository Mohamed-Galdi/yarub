<?php

namespace App\Services\ArabicSkills;

use App\Models\ArabicSkillBank;
use App\Models\ArabicSkillModel;
use App\Models\ArabicSkillQuestion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ContentImporter
{
    public function __construct(private ContentValidator $validator, private ImportEnvironment $environment) {}

    public function import(array $content, array $overwriteIds = [], ?ProductionImportApproval $approval = null): array
    {
        $content = $this->validator->validate($content);
        if ($approval !== null && $overwriteIds !== []) {
            throw new InvalidArgumentException('Production import cannot overwrite admin edits.');
        }
        $keys = [];
        foreach ($content['banks'] as $bank) {
            $keys[] = $bank['bank']['id'];
            $keys = array_merge($keys, array_column($bank['models'], 'id'), array_column($bank['questions'], 'id'), array_column($bank['supplemental_questions'] ?? [], 'id'));
        }
        foreach ($overwriteIds as $key) {
            if (! in_array($key, $keys, true)) {
                throw new InvalidArgumentException('Unknown targeted overwrite identity: '.$key);
            }
        }
        $digest = $this->validator->digest($content);
        $this->environment->assertSafe($approval, $digest);

        return DB::transaction(function () use ($content, $overwriteIds, $digest) {
            $report = ['counts' => [], 'conflicts' => [], 'omissions' => [], 'banks' => [], 'models' => []];
            foreach (['banks', 'models', 'questions'] as $entity) {
                $report['counts'][$entity] = array_fill_keys(['created', 'updated', 'unchanged', 'skipped', 'conflicting'], 0);
            }
            foreach ($content['banks'] as $source) {
                $meta = $source['bank'];
                $slug = $meta['id'];
                $bank = ArabicSkillBank::where('slug', $slug)->lockForUpdate()->first() ?? new ArabicSkillBank;
                $blocked = $this->sync($bank, [
                    'slug' => $slug, 'title' => $meta['title'], 'subtitle' => $meta['subtitle'],
                    'sort_order' => $meta['order'], 'source_metadata' => $meta,
                ], $slug, $overwriteIds, $digest, $report, 'banks') === 'skipped';
                $models = [];
                foreach ($source['models'] as $modelSource) {
                    $key = $modelSource['id'];
                    if ($blocked) {
                        $this->skip($report, 'models', $key, 'Bank has an unresolved conflict.');

                        continue;
                    }
                    $model = ArabicSkillModel::where('source_id', $key)->lockForUpdate()->first() ?? new ArabicSkillModel;
                    $data = ['bank_id' => $bank->id, 'source_id' => $key, 'number' => $modelSource['number'], 'title' => $modelSource['title'], 'sort_order' => $modelSource['number']];
                    if ($model->exists && ($model->bank_id !== $bank->id || $model->number !== $modelSource['number'])) {
                        $this->skip($report, 'models', $key, 'Original model identity/membership cannot change.');

                        continue;
                    }
                    if ($this->sync($model, $data, $key, $overwriteIds, $digest, $report, 'models') !== 'skipped') {
                        $models[$modelSource['number']] = $model->id;
                    }
                }
                foreach (array_merge($source['questions'], $source['supplemental_questions'] ?? []) as $q) {
                    $key = $q['id'];
                    $supplemental = str_starts_with($key, 'supp_');
                    if ($blocked || (! $supplemental && ! isset($models[$q['model_number']]))) {
                        $this->skip($report, 'questions', $key, 'Bank/model has an unresolved conflict.');

                        continue;
                    }
                    $record = ArabicSkillQuestion::where('source_id', $key)->lockForUpdate()->first() ?? new ArabicSkillQuestion;
                    $data = [
                        'source_id' => $key, 'bank_id' => $bank->id, 'model_id' => $supplemental ? null : $models[$q['model_number']],
                        'number' => $supplemental ? null : $q['number'], 'context_group_number' => $supplemental ? null : $q['context_group_number'],
                    ];
                    if ($record->exists && ($record->bank_id !== $bank->id || $record->model_id !== $data['model_id'] || $record->number !== $data['number'] || $record->context_group_number !== $data['context_group_number'])) {
                        $this->skip($report, 'questions', $key, 'Source question identity/membership cannot change.');

                        continue;
                    }
                    foreach (['skill', 'type', 'prompt', 'options', 'matching_items', 'answer', 'explanation', 'hint', 'max_score', 'scoring'] as $field) {
                        $data[$field] = $q[$field];
                    }
                    $this->sync($record, $data, $key, $overwriteIds, $digest, $report, 'questions');
                }
                if ($bank->exists) {
                    $types = $bank->questions()->selectRaw('type, COUNT(*) AS total')->groupBy('type')->pluck('total', 'type');
                    $report['banks'][] = [$slug, $bank->models()->count(), $bank->questions()->count(), $types['multiple_choice'] ?? 0, $types['true_false'] ?? 0, $types['matching'] ?? 0, $bank->questions()->sum('max_score')];
                    foreach ($bank->models()->withCount('questions')->withSum('questions', 'max_score')->get() as $model) {
                        $report['models'][] = [$slug, $model->source_id, $model->questions_count, $model->questions_sum_max_score ?? 0];
                    }
                }
            }
            foreach ([ArabicSkillBank::class => 'slug', ArabicSkillModel::class => 'source_id', ArabicSkillQuestion::class => 'source_id'] as $class => $field) {
                $included = match ($class) {
                    ArabicSkillBank::class => array_column(array_column($content['banks'], 'bank'), 'id'),
                    ArabicSkillModel::class => array_merge(...array_map(fn ($b) => array_column($b['models'], 'id'), $content['banks'])),
                    default => array_merge(...array_map(fn ($b) => array_column(array_merge($b['questions'], $b['supplemental_questions'] ?? []), 'id'), $content['banks'])),
                };
                foreach ($class::whereNotNull('import_checksum')->whereNotIn($field, $included)->pluck($field) as $key) {
                    $report['omissions'][] = $key;
                }
            }

            return $report;
        }, 3);
    }

    private function sync(Model $record, array $data, string $key, array $overwriteIds, string $digest, array &$report, string $entity): string
    {
        $current = [];
        foreach ($data as $field => $value) {
            $current[$field] = $record->getAttribute($field);
        }
        $checksum = $this->validator->digest($data);
        $existingChecksum = $this->validator->digest($current);
        if ($record->exists && ($record->import_checksum === null || ! hash_equals($record->import_checksum, $existingChecksum)) && ! in_array($key, $overwriteIds, true)) {
            $this->skip($report, $entity, $key, 'Current content differs from its last imported baseline, or has no baseline.');

            return 'skipped';
        }
        $status = $record->exists ? ($checksum === $existingChecksum ? 'unchanged' : 'updated') : 'created';
        if ($status !== 'unchanged' || $record->import_checksum !== $checksum) {
            $record->fill($data);
            $record->import_checksum = $checksum;
            $record->import_version = 'v1:'.$digest;
            $record->save();
        }
        $report['counts'][$entity][$status]++;

        return $status;
    }

    private function skip(array &$report, string $entity, string $key, string $reason): void
    {
        $report['counts'][$entity]['skipped']++;
        $report['counts'][$entity]['conflicting']++;
        $report['conflicts'][] = ['entity' => $entity, 'id' => $key, 'reason' => $reason];
    }
}
