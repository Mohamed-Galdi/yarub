<?php

namespace App\Services\Qudrat;

use InvalidArgumentException;
use JsonException;

class ContentValidator
{
    public const FILES = [
        '01_verbal_analogies.json' => ['verbal_analogies', 'التناظر اللفظي'],
        '02_sentence_completion.json' => ['sentence_completion', 'إكمال الجمل'],
        '03_contextual_errors.json' => ['contextual_errors', 'الخطأ السياقي'],
        '04_odd_word_out.json' => ['odd_word_out', 'المفردة الشاذة'],
        '05_reading_comprehension.json' => ['reading_comprehension', 'استيعاب المقروء'],
        '06_association_and_difference.json' => ['association_and_difference', 'الارتباط والاختلاف'],
    ];

    public function validateDirectory(string $directory): array
    {
        $files = glob(rtrim($directory, '/\\').'/*.json') ?: [];
        $names = array_map('basename', $files);
        sort($names);
        if ($names !== array_keys(self::FILES)) {
            throw new InvalidArgumentException('Expected exactly the six Qudrat JSON files.');
        }

        $content = [];
        foreach (self::FILES as $file => $expected) {
            if (! is_readable($directory.DIRECTORY_SEPARATOR.$file)) {
                throw new InvalidArgumentException($file.': content file is not readable.');
            }
            try {
                $content[] = json_decode(file_get_contents($directory.DIRECTORY_SEPARATOR.$file), true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $exception) {
                throw new InvalidArgumentException($file.': invalid UTF-8 JSON.', 0, $exception);
            }
        }

        return $this->validate($content);
    }

    public function validate(array $content): array
    {
        $this->require(count($content) === 6 && array_is_list($content), 'Expected six ordered categories.');
        $identities = [];
        foreach (array_values(self::FILES) as $index => [$slug, $title]) {
            $category = $content[$index];
            $this->require(is_array($category), $slug.': invalid category.');
            $this->require(($category['category'] ?? null) === $slug && ($category['title'] ?? null) === $title, $slug.': unexpected slug or title.');
            $questions = $category['questions'] ?? null;
            $this->require(is_array($questions) && array_is_list($questions) && count($questions) === 200, $slug.': expected 200 ordered questions.');
            $reading = $slug === 'reading_comprehension';
            $passages = $category['passages'] ?? [];
            $this->require(is_array($passages) && array_is_list($passages) && count($passages) === ($reading ? 40 : 0), $slug.': invalid passages.');
            $passageKeys = [];
            foreach ($passages as $passage) {
                $this->require(is_array($passage), $slug.': invalid passage.');
                $key = $passage['id'] ?? null;
                $this->identity($key, $identities);
                $this->require(str_starts_with($key, $slug.'_p'), $key.': unexpected passage key.');
                $this->text($passage['text'] ?? null, $key.': passage');
                $passageKeys[$key] = true;
            }

            $used = [];
            $runs = [];
            $previousPassage = null;
            foreach ($questions as $position => $question) {
                $this->require(is_array($question), $slug.': invalid question.');
                $key = $question['id'] ?? null;
                $this->identity($key, $identities);
                $this->require($key === $slug.'_'.str_pad((string) ($position + 1), 3, '0', STR_PAD_LEFT), $key.': invalid question identity/order.');
                $this->require(($question['order'] ?? null) === $position + 1, $key.': invalid order.');
                foreach (['question', 'hint', 'explanation'] as $field) {
                    $this->text($question[$field] ?? null, $key.': '.$field);
                }
                $options = $question['options'] ?? null;
                $this->require(is_array($options) && array_is_list($options) && count($options) === 4, $key.': expected four options.');
                foreach (['a', 'b', 'c', 'd'] as $optionPosition => $optionKey) {
                    $option = $options[$optionPosition];
                    $this->require(is_array($option) && ($option['id'] ?? null) === $optionKey, $key.': invalid option identities/order.');
                    $this->text($option['text'] ?? null, $key.': option '.$optionKey);
                }
                $this->require(in_array($question['correct_option_id'] ?? null, ['a', 'b', 'c', 'd'], true), $key.': invalid answer reference.');
                $passageKey = $question['passage_id'] ?? null;
                if ($reading) {
                    $this->require(is_string($passageKey) && isset($passageKeys[$passageKey]), $key.': invalid passage reference.');
                    $used[$passageKey] = true;
                    if ($passageKey !== $previousPassage) {
                        $this->require(! isset($runs[$passageKey]), $key.': non-contiguous passage group.');
                        $runs[$passageKey] = true;
                    }
                    $previousPassage = $passageKey;
                } else {
                    $this->require(! array_key_exists('passage_id', $question), $key.': unexpected passage link.');
                }
            }
            $this->require(count($used) === count($passageKeys), $slug.': unused passage.');
            $this->require(array_keys($runs) === array_keys($passageKeys), $slug.': passage order differs from question group order.');
        }

        return $content;
    }

    private function identity($key, array &$identities): void
    {
        $this->require(is_string($key) && preg_match('/^[a-z][a-z0-9_]{0,99}$/D', $key) === 1, 'Invalid external identity.');
        $this->require(! isset($identities[$key]), $key.': duplicate identity.');
        $identities[$key] = true;
    }

    private function text($value, string $label): void
    {
        $this->require(is_string($value) && preg_match('/\S/u', $value) === 1, $label.' must be nonempty text.');
    }

    private function require(bool $valid, string $message): void
    {
        if (! $valid) {
            throw new InvalidArgumentException($message);
        }
    }
}
