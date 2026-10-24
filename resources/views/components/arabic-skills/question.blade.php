@props(['question', 'response' => null, 'disabled' => false, 'name' => 'response', 'uid' => 'question', 'review' => false])
<div class="arabic-question">
    @if($review)
    <h4 class="arabic-prompt qudrat-prose">{{ $question['prompt'] }}</h4>
    @else
    <h2 class="arabic-prompt qudrat-prose" tabindex="-1" data-arabic-prompt>{{ $question['prompt'] }}</h2>
    @endif
    @if($question['type'] === 'matching')
        <p class="qudrat-note arabic-matching-note">اختر المطابقة لكل عبارة. يمكنك اختيار الإجابة نفسها أكثر من مرة.</p>
        <ol class="arabic-matching-options" aria-label="خيارات المطابقة">
            @foreach($question['options'] as $option)<li><strong>{{ $option['label'] }}</strong><span class="qudrat-prose">{{ $option['text'] }}</span></li>@endforeach
        </ol>
        <div class="arabic-matching-items">
            @foreach($question['matching_items'] as $item)
                @php($selected = collect($response['matches'] ?? [])->firstWhere('item_id', $item['id'])['option_id'] ?? '')
                <div class="arabic-matching-item">
                    <label class="qudrat-prose" for="{{ $uid }}-{{ $item['id'] }}">{{ $item['text'] }}</label>
                    <input type="hidden" name="{{ $name }}[matches][{{ $loop->index }}][item_id]" value="{{ $item['id'] }}">
                    <select id="{{ $uid }}-{{ $item['id'] }}" name="{{ $name }}[matches][{{ $loop->index }}][option_id]" @disabled($disabled) required data-arabic-match>
                        <option value="">اختر المطابقة</option>
                        @foreach($question['options'] as $option)<option value="{{ $option['id'] }}" data-choice-text="{{ $option['text'] }}" @selected($selected === $option['id'])>{{ $option['label'] }} — {{ $option['text'] }}</option>@endforeach
                    </select>
                    <output class="qudrat-note qudrat-prose" for="{{ $uid }}-{{ $item['id'] }}" data-arabic-match-text>{{ collect($question['options'])->firstWhere('id', $selected)['text'] ?? '' }}</output>
                </div>
            @endforeach
        </div>
    @else
        <fieldset class="arabic-choices"><legend class="sr-only">اختر الإجابة الصحيحة</legend>
            @foreach($question['options'] as $option)
                <label class="qudrat-choice" for="{{ $uid }}-{{ $option['id'] }}">
                    <input type="radio" id="{{ $uid }}-{{ $option['id'] }}" name="{{ $name }}[option_id]" value="{{ $option['id'] }}" @checked(($response['option_id'] ?? '') === $option['id']) @disabled($disabled) required>
                    @if($question['type'] === 'multiple_choice')<strong class="qudrat-choice-label">{{ $option['label'] }}</strong>@endif
                    <span class="qudrat-prose">{{ $option['text'] }}</span>
                </label>
            @endforeach
        </fieldset>
    @endif
</div>
