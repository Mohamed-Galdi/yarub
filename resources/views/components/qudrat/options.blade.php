@props(['options', 'name' => 'preview_option'])
<fieldset class="qudrat-options"><legend class="sr-only">اختر إجابة واحدة</legend>
    @foreach($options as $option)
        <label class="qudrat-choice"><input type="radio" name="{{ $name }}" value="{{ $option['key'] }}"><span class="qudrat-choice-label">{{ ['a' => 'أ', 'b' => 'ب', 'c' => 'ج', 'd' => 'د'][$option['key']] ?? $option['key'] }}</span><span class="qudrat-prose">{{ $option['text'] }}</span></label>
    @endforeach
</fieldset>
