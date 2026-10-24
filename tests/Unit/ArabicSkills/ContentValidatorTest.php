<?php

namespace Tests\Unit\ArabicSkills;

use App\Services\ArabicSkills\ContentValidator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ContentValidatorTest extends TestCase
{
    private function content(): array
    {
        return (new ContentValidator)->validateDirectory(dirname(__DIR__, 3).'/arabic_skills_content');
    }

    public function test_complete_package_preserves_arabic_and_has_expected_inventory(): void
    {
        $content = $this->content();
        $this->assertCount(5, $content['banks']);
        $this->assertSame('في الجملة «قرأ القارئُ كتابًا.» ما الفاعل؟', $content['banks'][0]['questions'][0]['prompt']);
        foreach ($content['banks'] as $bank) {
            $this->assertCount(300, $bank['questions']);
            $this->assertCount(10, $bank['models']);
            $this->assertSame(600, array_sum(array_column($bank['questions'], 'max_score')));
            $this->assertSame([null], array_values(array_unique(array_column($bank['questions'], 'hint'))));
        }
    }

    public function test_rejects_broken_identity_membership_scoring_and_payloads(): void
    {
        $original = $this->content();
        $mutations = [
            fn (&$c) => $c['catalog']['schema_version'] = '2',
            fn (&$c) => $c['catalog']['banks'][0]['file'] = '../01_nasaq.json',
            fn (&$c) => $c['banks'][0]['questions'][0]['answer']['option_id'] = 'd',
            fn (&$c) => $c['banks'][0]['questions'][4]['answer']['option_id'] = true,
            fn (&$c) => $c['banks'][0]['questions'][0]['explanation'] = '',
            fn (&$c) => $c['banks'][0]['questions'][0]['id'] = 'nasaq_002',
            fn (&$c) => $c['banks'][0]['questions'][0]['context_group_number'] = 2,
            fn (&$c) => $c['banks'][0]['questions'][0]['max_score'] = 1,
            fn (&$c) => $c['banks'][0]['models'][0]['question_ids'][0] = 'nasaq_031',
            fn (&$c) => $c['banks'][0]['questions'][7]['answer']['matches'][1]['item_id'] = 'item_1',
            fn (&$c) => $c['banks'][0]['questions'][7]['answer']['matches'][0]['option_id'] = 'c',
            fn (&$c) => $c['banks'][0]['questions'][7]['scoring']['points_per_correct_match'] = 2,
            fn (&$c) => $c['banks'][0]['questions'][0]['options'][0]['text'] = '',
        ];
        foreach ($mutations as $index => $mutate) {
            $content = $original;
            $mutate($content);
            try {
                (new ContentValidator)->validate($content);
                $this->fail('Accepted invalid mutation '.$index);
            } catch (InvalidArgumentException $exception) {
                $this->assertNotEmpty($exception->getMessage());
            }
        }
    }

    public function test_digest_changes_for_answer_and_text_edits_but_ignores_object_key_order(): void
    {
        $validator = new ContentValidator;
        $content = $this->content();
        $digest = $validator->digest($content);
        $this->assertSame($digest, $validator->digest(array_reverse($content, true)));
        $content['banks'][0]['questions'][0]['prompt'] .= ' ';
        $this->assertNotSame($digest, $validator->digest($content));
        $content = $this->content();
        $content['banks'][0]['questions'][0]['answer']['option_id'] = 'b';
        $this->assertNotSame($digest, $validator->digest($content));
    }

    public function test_catalog_only_loading_ignores_unlisted_json(): void
    {
        $directory = sys_get_temp_dir().'/arabic-skills-'.bin2hex(random_bytes(8));
        mkdir($directory);
        try {
            foreach (glob(dirname(__DIR__, 3).'/arabic_skills_content/*.json') as $file) {
                copy($file, $directory.'/'.basename($file));
            }
            file_put_contents($directory.'/unlisted.json', 'invalid JSON');
            $this->assertCount(5, (new ContentValidator)->validateDirectory($directory)['banks']);
            $catalog = json_decode(file_get_contents($directory.'/06_section_catalog.json'), true);
            $catalog['banks'][0]['file'] = '../outside.json';
            file_put_contents($directory.'/06_section_catalog.json', json_encode($catalog));
            $this->expectException(InvalidArgumentException::class);
            (new ContentValidator)->validateDirectory($directory);
        } finally {
            foreach (glob($directory.'/*') as $file) {
                unlink($file);
            }
            rmdir($directory);
        }
    }
}
