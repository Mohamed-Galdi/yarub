<?php

namespace Tests\Unit\ArabicSkills;

use App\Support\ArabicSkillPrompt;
use PHPUnit\Framework\TestCase;

class PromptPresentationTest extends TestCase
{
    public function test_reading_activity_parts_preserve_every_word_and_punctuation_mark(): void
    {
        $content = json_decode(file_get_contents(dirname(__DIR__, 3).'/arabic_skills_content/supplements/burhan_coffee_filtration.json'), true);
        foreach ($content['questions'] as $question) {
            $parts = ArabicSkillPrompt::sections($question['prompt']);
            $this->assertNotNull($parts['context']);
            $context = $parts['context'];
            $this->assertSame($question['prompt'], $context['metadata']."\n".$context['title']."\n\n".$context['instruction']."\n\n".$context['passage']."\n\n".$parts['question']);
        }
    }

    public function test_ordinary_multiline_and_unrecognized_prompts_remain_intact(): void
    {
        foreach (["السؤال الأول\nالسياق اللغوي: جملة.", "١. ما الهدف؟", "نوع | موضوع\nعنوان\n\nنص دون بنية معروفة."] as $prompt) {
            $this->assertSame(['context' => null, 'question' => $prompt], ArabicSkillPrompt::sections($prompt));
        }
    }
}
