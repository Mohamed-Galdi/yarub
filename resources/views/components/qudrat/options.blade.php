@props(['options', 'name' => 'preview_option', 'selected' => null, 'disabled' => false, 'correctKey' => null])
<fieldset class="qudrat-options"><legend class="sr-only">اختر إجابة واحدة</legend>
    @foreach($options as $option)
        <label class="qudrat-choice {{ $correctKey === $option['key'] ? 'qudrat-choice-correct' : ($correctKey && $selected === $option['key'] ? 'qudrat-choice-incorrect' : '') }}"><input type="radio" name="{{ $name }}" value="{{ $option['key'] }}" @checked($selected === $option['key']) @disabled($disabled)><span class="qudrat-choice-label">{{ ['a' => 'أ', 'b' => 'ب', 'c' => 'ج', 'd' => 'د'][$option['key']] ?? $option['key'] }}</span><span class="qudrat-prose">{{ $option['text'] }}</span>@if($correctKey === $option['key'])<span class="qudrat-choice-state">الإجابة الصحيحة</span>@elseif($correctKey && $selected === $option['key'])<span class="qudrat-choice-state">إجابتك</span>@endif</label>
    @endforeach
</fieldset>
