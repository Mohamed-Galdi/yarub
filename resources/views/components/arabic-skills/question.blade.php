@props(['question', 'response' => null, 'disabled' => false, 'name' => 'response', 'uid' => 'question', 'review' => false, 'feedback' => null, 'practice' => false])
@php
    $presentation = \App\Support\ArabicSkillPrompt::sections($question['prompt']);
@endphp
<div @class(['arabic-question', 'arabic-question-practice' => $practice])>
    @if($presentation['context'])
        @if($practice)
            <details class="arabic-reading-context session-reading-disclosure" open>
                <summary class="arabic-reading-title">{{ $presentation['context']['title'] }}</summary>
                <div class="session-reading-scroll" tabindex="0" role="region" aria-label="نص القراءة"><p class="arabic-reading-passage qudrat-prose">{{ $presentation['context']['passage'] }}</p></div>
            </details>
        @else
        <section class="arabic-reading-context" aria-label="نص القراءة">
            @if($review)
                <h4 class="arabic-reading-title">{{ $presentation['context']['title'] }}</h4>
            @else
                <h2 class="arabic-reading-title" tabindex="-1" data-arabic-prompt>{{ $presentation['context']['title'] }}</h2>
            @endif
            <p class="arabic-reading-instruction">{{ $presentation['context']['instruction'] }}</p>
            <p class="arabic-reading-passage qudrat-prose">{{ $presentation['context']['passage'] }}</p>
        </section>
        @endif
    @endif
    <div class="arabic-answer-area">
    @if($review)
    <h4 class="arabic-prompt qudrat-prose">{{ $presentation['question'] }}</h4>
    @else
    <h2 class="arabic-prompt qudrat-prose" tabindex="-1" data-arabic-prompt>{{ $presentation['question'] }}</h2>
    @endif
    @if($question['type'] === 'matching')
        @unless($practice)
        <p class="qudrat-note arabic-matching-note">اختر المطابقة لكل عبارة. يمكنك اختيار الإجابة نفسها أكثر من مرة.</p>
        <ol class="arabic-matching-options" aria-label="خيارات المطابقة">
            @foreach($question['options'] as $option)<li><strong>{{ $option['label'] }}</strong><span class="qudrat-prose">{{ $option['text'] }}</span></li>@endforeach
        </ol>
        @endunless
        <div class="arabic-matching-items">
            @foreach($question['matching_items'] as $item)
                @php
                    $selected = collect($response['matches'] ?? [])->firstWhere('item_id', $item['id'])['option_id'] ?? '';
                @endphp
                <div class="arabic-matching-item">
                    <label class="qudrat-prose" for="{{ $uid }}-{{ $item['id'] }}">{{ $item['text'] }}</label>
                    <input type="hidden" name="{{ $name }}[matches][{{ $loop->index }}][item_id]" value="{{ $item['id'] }}">
                    <select id="{{ $uid }}-{{ $item['id'] }}" name="{{ $name }}[matches][{{ $loop->index }}][option_id]" @disabled($disabled) required data-arabic-match>
                        <option value="">اختر المطابقة</option>
                        @foreach($question['options'] as $option)<option value="{{ $option['id'] }}" data-choice-text="{{ $option['text'] }}" @selected($selected === $option['id'])>{{ $option['label'] }} — {{ $option['text'] }}</option>@endforeach
                    </select>
                    @unless($practice)<output class="qudrat-note qudrat-prose" for="{{ $uid }}-{{ $item['id'] }}" data-arabic-match-text>{{ collect($question['options'])->firstWhere('id', $selected)['text'] ?? '' }}</output>@endunless
                </div>
            @endforeach
        </div>
    @else
        <fieldset class="arabic-choices" data-question-type="{{ $question['type'] }}"><legend class="sr-only">اختر الإجابة الصحيحة</legend>
            @foreach($question['options'] as $option)
                @php
                    $correct = $feedback !== null && $feedback['answer']['option_id'] === $option['id'];
                    $incorrect = $feedback !== null && !$correct && ($response['option_id'] ?? '') === $option['id'];
                @endphp
                <label @class(['qudrat-choice', 'arabic-choice-correct' => $correct, 'arabic-choice-incorrect' => $incorrect]) for="{{ $uid }}-{{ $option['id'] }}">
                    <input type="radio" id="{{ $uid }}-{{ $option['id'] }}" name="{{ $name }}[option_id]" value="{{ $option['id'] }}" @checked(($response['option_id'] ?? '') === $option['id']) @disabled($disabled) required>
                    @if($question['type'] === 'multiple_choice')<strong class="qudrat-choice-label">{{ $option['label'] }}</strong>@endif
                    <span class="arabic-choice-text"><span class="qudrat-prose">{{ $option['text'] }}</span>@if($correct)<span class="arabic-choice-state">الإجابة الصحيحة</span>@elseif($incorrect)<span class="arabic-choice-state">إجابتك</span>@endif</span>
                </label>
            @endforeach
        </fieldset>
    @endif
    </div>
</div>
