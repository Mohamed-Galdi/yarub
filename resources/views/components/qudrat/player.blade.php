@props(['prompt', 'options', 'category', 'title', 'passage' => null, 'selected' => null, 'disabled' => false, 'name' => 'preview_option', 'correctKey' => null, 'practice' => false])
<div class="qudrat-player {{ $passage ? 'qudrat-reading' : '' }}" dir="rtl">
    @if($passage)
        @if($practice)
            <details class="session-reading-disclosure" open><summary>النص القرائي</summary><div class="session-reading-scroll qudrat-prose" tabindex="0" role="region" aria-label="النص القرائي">{{ $passage }}</div></details>
        @else
            <x-qudrat.passage :text="$passage" />
        @endif
    @endif
    <section class="qudrat-question" aria-label="السؤال">
        @unless($practice)<p class="qudrat-note">{{ $title }}</p>@endunless
        <h3 class="qudrat-prompt {{ $category === 'verbal_analogies' ? 'qudrat-analogy' : '' }}" dir="rtl">{{ $prompt ?: 'السؤال غير مكتمل' }}</h3>
        {{ $hint ?? '' }}
        <x-qudrat.options :options="$options" :selected="$selected" :disabled="$disabled" :name="$name" :correct-key="$correctKey" />
        {{ $slot }}
    </section>
</div>
