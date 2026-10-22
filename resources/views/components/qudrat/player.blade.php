@props(['prompt', 'options', 'category', 'title', 'passage' => null, 'selected' => null, 'disabled' => false, 'name' => 'preview_option', 'correctKey' => null])
<div class="qudrat-player {{ $passage ? 'qudrat-reading' : '' }}" dir="rtl">
    @if($passage)<x-qudrat.passage :text="$passage" />@endif
    <section class="qudrat-question" aria-label="السؤال">
        <p class="qudrat-note">{{ $title }}</p>
        <h3 class="qudrat-prompt {{ $category === 'verbal_analogies' ? 'qudrat-analogy' : '' }}" dir="rtl">{{ $prompt ?: 'السؤال غير مكتمل' }}</h3>
        {{ $hint ?? '' }}
        <x-qudrat.options :options="$options" :selected="$selected" :disabled="$disabled" :name="$name" :correct-key="$correctKey" />
        {{ $slot }}
    </section>
</div>
