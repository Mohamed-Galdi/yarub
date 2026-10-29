@props(['set', 'startUrl', 'showRoute', 'resultsRoute', 'context'])
@php
    $state = $set['latest_completed'] ? 'completed' : ($set['resume'] ? 'active' : (!$set['can_start'] ? 'unavailable' : (!empty($set['latest_incomplete']) ? 'incomplete' : 'new')));
    $stateLabel = ['completed' => 'مكتمل', 'active' => 'قيد التدريب', 'unavailable' => 'غير متاح', 'incomplete' => 'غير مكتمل', 'new' => 'لم تبدأ بعد'][$state];
@endphp
<article class="practice-set-card" data-state="{{ $state }}">
    <div class="practice-set-top">
        <div class="practice-set-badges">
            <span class="practice-set-status" data-status="{{ $state }}">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    @if($state === 'unavailable')
                        <rect x="5" y="10" width="14" height="11" rx="2" /><path d="M8 10V7a4 4 0 0 1 8 0v3m-4 5v2" />
                    @else
                        <circle cx="12" cy="12" r="9" />
                        @if($state === 'completed')<path d="m8 12 3 3 5-6" />@elseif($state === 'active')<path d="m10 8 6 4-6 4Z" />@elseif($state === 'incomplete')<path d="M9 8v8m6-8v8" />@else<circle cx="12" cy="12" r="2" fill="currentColor" stroke="none" />@endif
                    @endif
                </svg>
                {{ $stateLabel }}
            </span>
            @if($set['latest_completed'] && $set['resume'])<span class="practice-set-retry-status">إعادة التدريب جارية</span>@endif
        </div>
        <span class="practice-set-count">{{ $set['available'] }} أسئلة</span>
    </div>
    <h3>{{ $set['title'] }}</h3>
    @if($set['available'] < $set['total'])<p class="qudrat-note">{{ $set['available'] }} من {{ $set['total'] }} سؤالًا متاحًا حاليًا.</p>@endif
    @if($set['resume'])
        <p class="practice-set-detail">وصلت إلى السؤال {{ $set['resume']['position'] }} من {{ $set['resume']['question_count'] }}</p>
        <progress class="practice-set-progress" aria-label="موضعك في المجموعة" value="{{ $set['resume']['position'] }}" max="{{ $set['resume']['question_count'] }}"></progress>
    @elseif($set['latest_completed'])
        <p class="practice-set-detail">آخر نتيجة <strong>{{ round($set['latest_completed']['percentage']) }}٪</strong></p>
    @endif
    <div class="practice-set-actions">
        @if($set['resume'])
            <a class="qudrat-button" href="{{ route($showRoute, $set['resume']['id']) }}" aria-label="متابعة التدريب — {{ $context }} — {{ $set['title'] }}">متابعة التدريب</a>
        @elseif($set['can_start'])
            <form method="POST" action="{{ $startUrl }}">@csrf
                <input type="hidden" name="mode" value="practice"><input type="hidden" name="set_key" value="{{ $set['key'] }}">
                <button class="qudrat-button" aria-label="{{ $set['latest_completed'] ? 'إعادة التدريب' : 'ابدأ التدريب' }} — {{ $context }} — {{ $set['title'] }}">{{ $set['latest_completed'] ? 'إعادة التدريب' : 'ابدأ التدريب' }}</button>
            </form>
        @else
            <span class="qudrat-note">غير متاحة للتدريب حاليًا.</span>
        @endif
        @if($set['latest_completed'])<a class="practice-set-review" href="{{ route($resultsRoute, $set['latest_completed']['id']) }}" aria-label="مراجعة النتيجة — {{ $context }} — {{ $set['title'] }}">مراجعة النتيجة</a>@endif
    </div>
</article>
