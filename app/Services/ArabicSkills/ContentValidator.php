<?php

namespace App\Services\ArabicSkills;

use InvalidArgumentException;
use JsonException;

class ContentValidator
{
    public const BANKS = ['nasaq', 'rasm', 'bayan', 'midad', 'burhan'];

    public const TYPES = ['multiple_choice' => 120, 'true_false' => 90, 'matching' => 90];

    public function digest(array $content): string
    {
        return hash('sha256', json_encode($this->canonical($content), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }

    private function canonical(array $data): array
    {
        if (! array_is_list($data)) {
            ksort($data);
        }
        foreach ($data as &$value) {
            if (is_array($value)) {
                $value = $this->canonical($value);
            }
        }

        return $data;
    }

    public function validateDirectory(string $directory): array
    {
        $root = realpath($directory);
        $this->require($root !== false && is_dir($root), 'Content directory does not exist.');
        $catalog = $this->read($root.DIRECTORY_SEPARATOR.'06_section_catalog.json');
        $this->require(is_array($catalog['banks'] ?? null) && array_is_list($catalog['banks']), 'Catalog banks must be a list.');
        $banks = [];
        $files = [];
        foreach ($catalog['banks'] as $entry) {
            $file = is_array($entry) ? ($entry['file'] ?? null) : null;
            $this->require(is_string($file) && preg_match('/^[0-9]{2}_[a-z]+\.json$/D', $file) === 1 && $file !== '06_section_catalog.json' && ! isset($files[$file]), 'Invalid or duplicate catalog bank filename.');
            $resolved = realpath($root.DIRECTORY_SEPARATOR.$file);
            $this->require($resolved !== false && dirname($resolved) === $root, 'Bank file must remain inside the content directory.');
            $files[$file] = true;
            $banks[] = $this->read($resolved);
        }

        return $this->validate(['catalog' => $catalog, 'banks' => $banks]);
    }

    private function read(string $file): array
    {
        $this->require(is_file($file) && is_readable($file), basename($file).': unreadable JSON file.');
        try {
            $data = json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidArgumentException(basename($file).': invalid UTF-8 JSON.', 0, $exception);
        }
        $this->require(is_array($data) && ! array_is_list($data), basename($file).': expected an object.');

        return $data;
    }

    public function validate(array $content): array
    {
        $catalog = $content['catalog'] ?? [];
        $this->require(($catalog['schema_version'] ?? null) === '1.0.0', 'Unsupported catalog schema version.');
        $this->require(($catalog['section'] ?? null) === ['slug' => 'arabic-skills', 'title' => 'مهارات اللغة العربية', 'language' => 'ar'], 'Invalid section metadata.');
        foreach (['provided_bank_count' => 5, 'question_count' => 1500, 'model_count' => 50] as $key => $count) {
            $this->require(($catalog['content_status'][$key] ?? null) === $count, 'Invalid catalog '.$key.'.');
        }
        foreach (['practice_session_question_count' => 10, 'model_question_count' => 30, 'points_per_question' => 2, 'matching_points_per_correct_match' => 1, 'negative_marking' => false] as $key => $value) {
            $this->require(($catalog['defaults'][$key] ?? null) === $value, 'Invalid default '.$key.'.');
        }
        $entries = $catalog['banks'] ?? null;
        $banks = $content['banks'] ?? null;
        $this->require(is_array($entries) && array_is_list($entries) && count($entries) === 5 && is_array($banks) && array_is_list($banks) && count($banks) === 5, 'Expected five catalog banks and five bank files.');
        $identities = [];
        $titles = ['نَسَق', 'رَسْم', 'بَيان', 'مِداد', 'بُرهان'];
        $subtitles = ['النحو وبناء الجملة', 'الإملاء والتحرير الكتابي', 'البلاغة وتذوق النصوص', 'الكتابة الأدبية', 'الكتابة العلمية'];
        foreach (self::BANKS as $position => $slug) {
            $entry = $entries[$position];
            $bank = $banks[$position];
            $this->require(is_array($entry) && is_array($bank), $slug.': invalid bank.');
            $meta = $bank['bank'] ?? [];
            foreach (['id' => $slug, 'title' => $titles[$position], 'subtitle' => $subtitles[$position], 'order' => $position + 1, 'question_count' => 300, 'question_type_counts' => self::TYPES] as $key => $value) {
                $this->require(($entry[$key] ?? null) === $value && ($meta[$key] ?? null) === $value, $slug.': invalid '.$key.'.');
            }
            $this->require(($entry['model_count'] ?? null) === 10 && ($entry['file'] ?? null) === sprintf('%02d_%s.json', $position + 1, $slug), $slug.': invalid catalog file/model count.');
            $this->require(($bank['schema_version'] ?? null) === '1.0.0' && ($bank['section_slug'] ?? null) === 'arabic-skills' && ($meta['language'] ?? null) === 'ar' && ($meta['max_score'] ?? null) === 600, $slug.': invalid metadata.');
            foreach (['source_filename', 'author', 'edition', 'hint_policy'] as $key) {
                $this->text($meta[$key] ?? null, $slug.'.'.$key);
            }
            $this->require(is_string($meta['source_sha256'] ?? null) && preg_match('/^[a-f0-9]{64}$/D', $meta['source_sha256']) === 1, $slug.': invalid source hash.');
            $questions = $bank['questions'] ?? null;
            $models = $bank['models'] ?? null;
            $this->require(is_array($questions) && array_is_list($questions) && count($questions) === 300 && is_array($models) && array_is_list($models) && count($models) === 10, $slug.': expected 300 questions and ten models.');
            $counts = array_fill_keys(array_keys(self::TYPES), 0);
            foreach ($questions as $index => $question) {
                $this->require(is_array($question), $slug.': invalid question object.');
                $key = sprintf('%s_%03d', $slug, $index + 1);
                $this->require(($question['id'] ?? null) === $key && ! isset($identities[$key]) && ($question['number'] ?? null) === $index + 1 && ($question['model_number'] ?? null) === intdiv($index, 30) + 1 && ($question['context_group_number'] ?? null) === intdiv($index, 10) + 1, $key.': invalid identity, membership or context group.');
                $identities[$key] = true;
                $this->validateQuestion($question, $key);
                $this->require($question['max_score'] === 2 && count($question['options']) === ($question['type'] === 'multiple_choice' ? 3 : 2), $key.': invalid original question shape.');
                $counts[$question['type']]++;
            }
            $this->require($counts === self::TYPES, $slug.': invalid question type counts.');
            foreach ($models as $index => $model) {
                $key = sprintf('%s_model_%02d', $slug, $index + 1);
                $this->require(is_array($model) && ($model['id'] ?? null) === $key && ($model['number'] ?? null) === $index + 1 && ($model['question_count'] ?? null) === 30 && ($model['max_score'] ?? null) === 60, $key.': invalid model metadata.');
                $this->text($model['title'] ?? null, $key.'.title');
                $expected = array_column(array_slice($questions, $index * 30, 30), 'id');
                $this->require(($model['question_ids'] ?? null) === $expected, $key.': invalid ordered question membership.');
            }
        }

        foreach ($banks as $bank) {
            $supplement = $bank['supplemental_questions'] ?? [];
            $this->require(is_array($supplement) && array_is_list($supplement), 'Invalid supplemental questions.');
            foreach ($supplement as $question) {
                $key = $question['id'] ?? '';
                $this->require(is_string($key) && preg_match('/^supp_'.$bank['bank']['id'].'_[a-z0-9_]+$/D', $key) === 1 && ! isset($identities[$key]), 'Invalid supplemental identity.');
                $identities[$key] = true;
                $this->validateQuestion($question, $key);
            }
        }

        return $content;
    }

    public function withSupplement(array $content, string $file): array
    {
        $supplement = $this->read($file);
        $this->require(($supplement['schema_version'] ?? null) === '1.0.0' && in_array($supplement['bank_id'] ?? null, self::BANKS, true), 'Invalid supplement metadata.');
        $this->require(is_array($supplement['questions'] ?? null) && array_is_list($supplement['questions']) && $supplement['questions'] !== [], 'Supplement requires questions.');
        foreach ($content['banks'] as &$bank) {
            if ($bank['bank']['id'] === $supplement['bank_id']) {
                $bank['supplemental_questions'] = array_merge($bank['supplemental_questions'] ?? [], $supplement['questions']);
            }
        }
        unset($bank);

        return $this->validate($content);
    }

    // Shared structured-content contract; admin additions are not subject to source count/number limits.
    public function validateQuestion(array $question, string $key = 'question'): void
    {
        foreach (['skill', 'prompt', 'explanation'] as $field) {
            $this->text($question[$field] ?? null, $key.'.'.$field);
        }
        $this->require(array_key_exists('hint', $question) && ($question['hint'] === null || (is_string($question['hint']) && trim($question['hint']) !== '')), $key.': invalid hint.');
        $type = $question['type'] ?? null;
        $this->require(is_string($type) && isset(self::TYPES[$type]), $key.': invalid type.');
        $options = $question['options'] ?? null;
        $count = is_array($options) ? count($options) : 0;
        $this->require(in_array($count, $type === 'multiple_choice' ? [3, 4] : ($type === 'matching' ? [2, 4] : [2]), true), $key.': invalid option count.');
        $ids = $type === 'true_false' ? ['true', 'false'] : array_slice(['a', 'b', 'c', 'd'], 0, $count);
        $this->require(($question['max_score'] ?? null) === ($type === 'matching' ? $count : 2), $key.': invalid maximum score.');
        $this->require(is_array($options) && array_is_list($options) && count($options) === count($ids), $key.': invalid options.');
        foreach ($options as $index => $option) {
            $this->require(is_array($option) && ($option['id'] ?? null) === $ids[$index], $key.': invalid option identity/order.');
            $this->text($option['label'] ?? null, $key.'.option.label');
            $this->text($option['text'] ?? null, $key.'.option.text');
            if ($type === 'true_false') {
                $this->require($option['text'] === ($index === 0 ? 'صح' : 'خطأ'), $key.': invalid true/false text.');
            }
        }
        $answer = $question['answer'] ?? null;
        $items = $question['matching_items'] ?? null;
        $this->require(is_array($answer) && is_array($items) && array_is_list($items), $key.': invalid answer/items.');
        if ($type !== 'matching') {
            $this->require(array_keys($answer) === ['option_id'] && in_array($answer['option_id'], $ids, true) && $items === [] && ($question['scoring'] ?? null) === ['mode' => 'all_or_nothing'], $key.': invalid answer/scoring.');

            return;
        }
        $this->require(count($items) === $count && array_keys($answer) === ['matches'] && is_array($answer['matches']) && array_is_list($answer['matches']) && count($answer['matches']) === $count && ($question['scoring'] ?? null) === ['mode' => 'per_match', 'points_per_correct_match' => 1], $key.': invalid matching structure/scoring.');
        foreach ($items as $index => $item) {
            $this->require(is_array($item) && ($item['id'] ?? null) === 'item_'.($index + 1), $key.': invalid matching item.');
            $this->text($item['text'] ?? null, $key.'.matching_item.text');
        }
        $seen = [];
        foreach ($answer['matches'] as $match) {
            $this->require(is_array($match) && count($match) === 2 && in_array($match['item_id'] ?? null, array_column($items, 'id'), true) && in_array($match['option_id'] ?? null, $ids, true) && ! isset($seen[$match['item_id']]), $key.': invalid or duplicate matching mapping.');
            $seen[$match['item_id']] = true;
        }
    }

    private function text(mixed $value, string $key): void
    {
        $this->require(is_string($value) && trim($value) !== '', $key.': expected nonempty text.');
    }

    private function require(bool $condition, string $message): void
    {
        if (! $condition) {
            throw new InvalidArgumentException($message);
        }
    }
}
