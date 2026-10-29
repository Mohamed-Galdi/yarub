@props(['result', 'title', 'id', 'score' => null])
<section class="session-feedback-toast" data-result="{{ $result }}" id="{{ $id }}" tabindex="-1" role="region" aria-labelledby="{{ $id }}-title">
    <div class="session-feedback-top">
        <svg class="session-feedback-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="12" r="9" />
            @if($result === 'correct')<path d="m8 12 3 3 5-6" />@elseif($result === 'partial')<path d="M8 12h8" />@else<path d="m9 9 6 6m0-6-6 6" />@endif
        </svg>
        <h2 id="{{ $id }}-title">{{ $title }}@if($score) · <bdi dir="ltr">{{ $score[0] }} / {{ $score[1] }}</bdi>@endif</h2>
        <div class="session-feedback-action">{{ $action }}</div>
    </div>
    <details class="session-feedback-explanation">
        <summary>عرض الإجابة والشرح</summary>
        <div class="session-feedback-content">{{ $slot }}</div>
    </details>
</section>
