<?php

namespace Tests\Unit\ArabicSkills;

use App\Services\ArabicSkills\ContentValidator;
use App\Services\ArabicSkills\Grader;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class SupplementTest extends TestCase
{
    private function content(): array
    {
        $root = dirname(__DIR__, 3).'/arabic_skills_content';
        $validator = new ContentValidator;

        return $validator->withSupplement($validator->validateDirectory($root), $root.'/supplements/burhan_coffee_filtration.json');
    }

    public function test_coffee_questions_preserve_context_keys_and_original_models(): void
    {
        $content = $this->content();
        $bank = $content['banks'][4];
        $this->assertCount(300, $bank['questions']);
        $this->assertCount(10, $bank['models']);
        $questions = $bank['supplemental_questions'];
        $this->assertCount(8, $questions);
        $this->assertSame(['b', 'c', 'b', 'c', 'a', 'true', 'false'], array_map(fn ($q) => $q['answer']['option_id'], array_slice($questions, 0, 7)));
        $this->assertSame(18, array_sum(array_column($questions, 'max_score')));
        foreach ($questions as $question) {
            $this->assertStringContainsString('لكنه لا يزيل المواد الذائبة فيه.', $question['prompt']);
            $this->assertNull($question['hint']);
            $grade = (new Grader)->grade($question, $question['answer']);
            $this->assertSame('correct', $grade['result']);
            $this->assertSame($question['max_score'], $grade['earned_points']);
        }
        $question = $questions[7];
        $response = $question['answer'];
        $response['matches'][3]['option_id'] = 'a';
        $this->assertSame(3, (new Grader)->grade($question, $response)['earned_points']);
        $response['matches'][2]['option_id'] = 'a';
        $this->assertSame('partial', (new Grader)->grade($question, $response)['result']);
        $this->assertSame('incorrect', (new Grader)->grade($questions[0], ['option_id' => 'd'])['result']);
    }

    public function test_rejects_duplicate_supplement_identity_and_missing_fourth_match(): void
    {
        $validator = new ContentValidator;
        foreach (['identity', 'match', 'score'] as $mutation) {
            $content = $this->content();
            $questions = &$content['banks'][4]['supplemental_questions'];
            if ($mutation === 'identity') {
                $questions[1]['id'] = $questions[0]['id'];
            } elseif ($mutation === 'match') {
                array_pop($questions[7]['answer']['matches']);
            } else {
                $questions[7]['max_score'] = 2;
            }
            try {
                $validator->validate($content);
                $this->fail('Accepted invalid '.$mutation);
            } catch (InvalidArgumentException $exception) {
                $this->assertNotEmpty($exception->getMessage());
            }
            unset($questions);
        }
    }
}
