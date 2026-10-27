<div class="sv-result-summary"><p>{{ $videos->total() }} فيديو</p><p>الأحدث أولًا</p></div>
@if($videos->isEmpty())
    <div class="sv-empty"><h2>{{ $state['q'] !== '' || $state['type'] !== '' ? 'لا توجد فيديوهات مطابقة' : 'لا توجد فيديوهات متاحة الآن' }}</h2><p>{{ $state['q'] !== '' || $state['type'] !== '' ? 'جرّب عبارة أخرى أو اختر «الكل» لعرض الأنواع المتاحة.' : 'عُد لاحقًا للاطلاع على الشروحات الجديدة.' }}</p></div>
@else
    <div class="sv-grid">
    @foreach($videos as $video)
        @php($seconds = (int) ceil($video->duration_seconds))
        <article class="sv-card" data-library-video-id="{{ $video->id }}">
            <a class="sv-card-link" data-open-video="{{ $video->id }}" href="{{ route('student.videos.index', array_filter($state, fn ($value, $key) => $key === 'page' ? $value > 1 : $value !== '', ARRAY_FILTER_USE_BOTH) + ['video' => $video->id]) }}">
            <div class="sv-cover"><img src="{{ route('student.videos.asset', [$video, 'cover']) }}" alt="" width="360" height="640" loading="lazy" decoding="async"><span class="sv-cover-fallback" hidden>الغلاف غير متاح</span><span class="sv-duration"><span class="sr-only">المدة </span><bdi>{{ $seconds >= 3600 ? sprintf('%d:%02d:%02d', intdiv($seconds, 3600), intdiv($seconds % 3600, 60), $seconds % 60) : sprintf('%d:%02d', intdiv($seconds, 60), $seconds % 60) }}</bdi></span></div>
            <h2>{{ $video->title }}</h2><p class="sv-type">{{ $video->qudratCategory?->title ?? $video->arabicSkillBank?->title }}</p>
            </a>
        </article>
    @endforeach
    </div>
@endif
@if($videos->hasPages())<div class="sv-pagination" data-video-pagination>{{ $videos->onEachSide(1)->links() }}</div>@endif
