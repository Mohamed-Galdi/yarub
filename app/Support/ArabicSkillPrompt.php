<?php

namespace App\Support;

class ArabicSkillPrompt
{
    public static function sections(string $prompt): array
    {
        // Recognize the annotated reading-activity format only. Ordinary/editor-authored
        // prompts remain intact; stored content and session snapshots are never rewritten.
        if (preg_match('/\A([^\r\n]+\|[^\r\n]+)\R([^\r\n]+)\R{2,}([^\r\n]+[:：])\R{2,}(.+?)\R{2,}([0-9٠-٩۰-۹]+[.)]\h+.+)\z/us', $prompt, $parts) !== 1) {
            return ['context' => null, 'question' => $prompt];
        }

        return ['context' => ['metadata' => $parts[1], 'title' => $parts[2], 'instruction' => $parts[3], 'passage' => $parts[4]],
            'question' => $parts[5]];
    }
}
