<?php

namespace App\Services\Qudrat;

use App\Models\QudratCategory;
use App\Models\QudratPassage;
use App\Models\QudratQuestion;
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
            throw new InvalidArgumentException('Production import cannot overwrite admin edits; resolve conflicts individually in the admin editor.');
        }
        $known = [];
        foreach ($content as $category) {
            $known[] = $category['category'];
            $known = array_merge($known, array_column($category['questions'], 'id'), array_column($category['passages'] ?? [], 'id'));
        }
        foreach ($overwriteIds as $key) {
            if (! in_array($key, $known, true)) {
                throw new InvalidArgumentException('Unknown targeted overwrite identity: '.$key);
            }
        }
        $digest = $this->validator->digest($content);
        $this->environment->assertSafe($approval, $digest);
        $version = 'v1:'.$digest;

        return DB::transaction(function () use ($content, $overwriteIds, $version) {
            $report = ['counts' => [], 'conflicts' => []];
            foreach (['categories', 'passages', 'questions', 'options'] as $entity) {
                $report['counts'][$entity] = array_fill_keys(['created', 'updated', 'unchanged', 'skipped', 'conflicting'], 0);
            }
            foreach ($content as $categoryPosition => $source) {
                $slug = $source['category'];
                $category = QudratCategory::where('slug', $slug)->lockForUpdate()->first() ?? new QudratCategory(['slug' => $slug]);
                $categoryData = ['slug' => $slug, 'title' => $source['title'], 'sort_order' => $categoryPosition + 1];
                $categoryStatus = $this->sync($category, $categoryData, $slug, $overwriteIds, $version, $report, 'categories');
                $passageMap = [];
                $blockedPassages = [];
                foreach ($source['passages'] ?? [] as $position => $passageSource) {
                    $key = $passageSource['id'];
                    if ($categoryStatus === 'skipped') {
                        $this->skip($report, 'passages', $key, 'Category has an unresolved import conflict.');
                        $blockedPassages[$key] = true;

                        continue;
                    }
                    $passage = QudratPassage::where('external_key', $key)->lockForUpdate()->first() ?? new QudratPassage(['external_key' => $key]);
                    $status = $this->sync($passage, [
                        'external_key' => $key, 'category_id' => $category->id,
                        'text' => $passageSource['text'], 'sort_order' => $position + 1,
                    ], $key, $overwriteIds, $version, $report, 'passages');
                    $passageMap[$key] = $passage->id;
                    if ($status === 'skipped') {
                        $blockedPassages[$key] = true;
                    }
                }

                foreach ($source['questions'] as $questionSource) {
                    $key = $questionSource['id'];
                    $passageKey = $questionSource['passage_id'] ?? null;
                    if ($categoryStatus === 'skipped' || isset($blockedPassages[$passageKey])) {
                        $this->skip($report, 'questions', $key, 'Category or passage has an unresolved import conflict.');
                        $report['counts']['options']['skipped'] += 4;

                        continue;
                    }
                    $question = QudratQuestion::where('external_key', $key)->lockForUpdate()->first() ?? new QudratQuestion(['external_key' => $key]);
                    $options = $question->exists ? $question->options()->lockForUpdate()->get() : collect();
                    $question->setRelation('options', $options);
                    $data = [
                        'external_key' => $key, 'category_id' => $category->id,
                        'passage_id' => $passageKey ? $passageMap[$passageKey] : null,
                        'prompt' => $questionSource['question'], 'hint' => $questionSource['hint'],
                        'explanation' => $questionSource['explanation'], 'sort_order' => $questionSource['order'],
                        'correct_option_key' => $questionSource['correct_option_id'],
                    ];
                    $optionData = [];
                    foreach ($questionSource['options'] as $position => $option) {
                        $optionData[] = ['local_key' => $option['id'], 'text' => $option['text'], 'sort_order' => $position + 1];
                    }
                    if ($question->exists && $question->category_id !== $category->id && $question->sessionQuestions()->exists()) {
                        $this->skip($report, 'questions', $key, 'Attempted question category cannot change.');
                        $report['counts']['options']['skipped'] += 4;

                        continue;
                    }
                    $status = $this->sync($question, $data, $key, $overwriteIds, $version, $report, 'questions', $optionData);
                    if ($status === 'skipped') {
                        $report['counts']['options']['skipped'] += 4;

                        continue;
                    }
                    foreach ($optionData as $attributes) {
                        $option = $options->firstWhere('local_key', $attributes['local_key']) ?? $question->options()->make();
                        $exists = $option->exists;
                        $option->fill($attributes);
                        $changed = $option->isDirty();
                        if (! $exists || $changed) {
                            $option->save();
                        }
                        $report['counts']['options'][$exists ? ($changed ? 'updated' : 'unchanged') : 'created']++;
                    }
                }
            }

            return $report;
        });
    }

    private function sync(Model $record, array $data, string $key, array $overwriteIds, string $version, array &$report, string $entity, ?array $options = null): string
    {
        $exists = $record->exists;
        $incoming = $data;
        $current = [];
        foreach ($data as $field => $value) {
            $current[$field] = $record->getAttribute($field);
        }
        if ($options !== null) {
            $incoming['options'] = $options;
            $current['options'] = $record->options->map(fn ($option) => $option->only(['local_key', 'text', 'sort_order']))->all();
        }
        if ($exists && ($record->import_checksum === null || ! hash_equals($record->import_checksum, $this->checksum($current)))
            && ! in_array($key, $overwriteIds, true)) {
            $this->skip($report, $entity, $key, 'Current content differs from its last imported baseline, or has no baseline.');

            return 'skipped';
        }
        $status = $exists ? ($this->checksum($current) === $this->checksum($incoming) ? 'unchanged' : 'updated') : 'created';
        $checksum = $this->checksum($incoming);
        if ($status !== 'unchanged' || $record->import_checksum !== $checksum) {
            $record->fill($data);
            $record->import_checksum = $checksum;
            $record->import_version = $version;
            $record->save();
        }
        $report['counts'][$entity][$status]++;

        return $status;
    }

    private function checksum(array $data): string
    {
        return hash('sha256', json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }

    private function skip(array &$report, string $entity, string $key, string $reason): void
    {
        $report['counts'][$entity]['skipped']++;
        $report['counts'][$entity]['conflicting']++;
        $report['conflicts'][] = ['entity' => $entity, 'id' => $key, 'reason' => $reason];
    }
}
