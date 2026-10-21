<?php

namespace Tests\Unit\Qudrat;

use App\Services\Qudrat\ContentValidator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ContentValidatorTest extends TestCase
{
    public function test_supplied_content_has_the_expected_complete_inventory(): void
    {
        $content = (new ContentValidator)->validateDirectory(dirname(__DIR__, 3).'/qudrat_content');
        $this->assertCount(6, $content);
        $this->assertSame(1200, array_sum(array_map(fn ($category) => count($category['questions']), $content)));
        $this->assertCount(40, $content[4]['passages']);
        $this->assertSame('تنمي — مدارك', $content[1]['questions'][1]['options'][1]['text']);
    }

    #[DataProvider('invalidCases')]
    public function test_invalid_content_is_rejected(string $case): void
    {
        $validator = new ContentValidator;
        $content = $validator->validateDirectory(dirname(__DIR__, 3).'/qudrat_content');
        switch ($case) {
            case 'category': $content[0]['title'] = 'other';
                break;
            case 'duplicate_id': $content[0]['questions'][1]['id'] = $content[0]['questions'][0]['id'];
                break;
            case 'order': $content[0]['questions'][0]['order'] = '1';
                break;
            case 'count': array_pop($content[5]['questions']);
                break;
            case 'options': array_pop($content[0]['questions'][0]['options']);
                break;
            case 'option_key': $content[0]['questions'][0]['options'][1]['id'] = 'a';
                break;
            case 'empty_text': $content[0]['questions'][0]['hint'] = " \n\t";
                break;
            case 'answer': $content[0]['questions'][0]['correct_option_id'] = 'e';
                break;
            case 'passage': $content[4]['questions'][0]['passage_id'] = 'missing';
                break;
            case 'unexpected_passage': $content[0]['questions'][0]['passage_id'] = 'reading_comprehension_p001';
                break;
            case 'empty_passage': $content[4]['passages'][0]['text'] = '';
                break;
            case 'split_group': $content[4]['questions'][1]['passage_id'] = $content[4]['passages'][1]['id'];
                break;
        }
        $this->expectException(InvalidArgumentException::class);
        $validator->validate($content);
    }

    public static function invalidCases(): array
    {
        return array_map(fn ($case) => [$case], ['category', 'duplicate_id', 'order', 'count', 'options',
            'option_key', 'empty_text', 'answer', 'passage', 'unexpected_passage', 'empty_passage', 'split_group']);
    }
}
