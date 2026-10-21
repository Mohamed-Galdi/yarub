@props(['prompt', 'options', 'category', 'title', 'passage' => null])
<div class="qudrat-player {{ $passage ? 'qudrat-reading' : '' }}" dir="rtl">
    @if($passage)<x-qudrat.passage :text="$passage" />@endif
    <section class="qudrat-question" aria-label="السؤال">
        <p class="qudrat-note">{{ $title }}</p>
        <h3 class="qudrat-prompt {{ $category === 'verbal_analogies' ? 'qudrat-analogy' : '' }}" dir="rtl">{{ $prompt ?: 'السؤال غير مكتمل' }}</h3>
        <x-qudrat.options :options="$options" />
    </section>
</div>
