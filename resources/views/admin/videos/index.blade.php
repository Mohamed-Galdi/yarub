@extends('layouts.admin')
@section('compact-admin', '1')
@section('content')
<section data-video-admin dir="rtl" data-example-url="{{ route('admin.videos.public-example') }}">
    <header class="video-page-heading"><div><h1>الفيديوهات</h1><p>شروحات قصيرة للقدرات ومهارات اللغة العربية.</p></div><a class="video-button" href="{{ route('admin.videos.create', request()->only(['q', 'type', 'visibility', 'page'])) }}">رفع فيديو</a></header>
    <div class="video-error video-page-error" data-page-error role="alert" tabindex="-1" hidden></div>
    <p class="video-example-notice" data-page-success role="status" hidden></p>
    @if($selected)
        <div class="video-example-notice"><div><strong>المثال العام:</strong> <a href="{{ route('admin.videos.edit', $selected) }}">{{ $selected->title }}</a><span>{{ $selected->isEligible() ? 'متاح للزوار' : 'غير متاح للزوار حاليًا' }}</span></div><button class="video-text-button" data-action="example" data-example-id="" type="button">إزالة الاختيار</button></div>
    @endif
    <div class="video-directory">
        <form method="GET" class="video-filters" data-filter-form>
            <div><label for="video-search">بحث</label><input id="video-search" type="search" name="q" value="{{ $state['q'] ?? '' }}" maxlength="200" placeholder="العنوان أو الوصف"></div>
            <div><label for="video-type-filter">النوع</label><select id="video-type-filter" name="type"><option value="">الكل</option>@include('admin.videos._type-options', ['selectedType' => $state['type'] ?? ''])</select></div>
            <div><label for="video-visibility">الظهور</label><select id="video-visibility" name="visibility"><option value="">الكل</option><option value="visible" @selected(($state['visibility'] ?? '') === 'visible')>ظاهر</option><option value="hidden" @selected(($state['visibility'] ?? '') === 'hidden')>مخفي</option></select></div>
            <div class="video-filter-actions"><button class="video-button video-secondary">عرض النتائج</button>@if(request()->filled('q') || request()->filled('type') || request()->filled('visibility'))<a href="{{ route('admin.videos.index') }}">مسح الفلاتر</a>@endif</div>
        </form>
        <p class="video-result-count" aria-live="polite">{{ $videos->total() }} فيديو<span>الأحدث رفعًا أولًا</span></p>
        @if($videos->isEmpty())
            <div class="video-empty"><h2>{{ request()->filled('q') || request()->filled('type') || request()->filled('visibility') ? 'لا توجد نتائج مطابقة' : 'ابدأ بأول فيديو' }}</h2><p>{{ request()->filled('q') || request()->filled('type') || request()->filled('visibility') ? 'جرّب عنوانًا آخر أو امسح الفلاتر.' : 'ارفع شرحًا قصيرًا، وسنجهّز غلافه ليظهر للطلاب.' }}</p><a href="{{ request()->filled('q') || request()->filled('type') || request()->filled('visibility') ? route('admin.videos.index') : route('admin.videos.create') }}" class="video-button video-secondary">{{ request()->filled('q') || request()->filled('type') || request()->filled('visibility') ? 'عرض جميع الفيديوهات' : 'رفع فيديو' }}</a></div>
        @else
            <table class="video-table"><caption class="sr-only">إدارة الفيديوهات</caption><thead><tr><th scope="col">الفيديو</th><th scope="col">البرنامج والنوع</th><th scope="col">الحالة</th><th scope="col">أول نشر</th><th scope="col">الإجراءات</th></tr></thead><tbody>
            @foreach($videos as $video)
                @php($eligible = in_array($video->id, $eligibleIds, true))
                <tr>
                    <td class="video-identity"><div class="video-identity-content"><div class="video-thumb">@if($video->cover_path)<img src="{{ route('admin.videos.preview', [$video, 'cover']) }}" alt="" width="54" height="88" loading="lazy">@endif<svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="5" y="2" width="14" height="20" rx="2"/><path d="m10 9 5 3-5 3z"/></svg>@if($video->duration_seconds)<bdi>{{ gmdate($video->duration_seconds >= 3600 ? 'H:i:s' : 'i:s', (int) round($video->duration_seconds)) }}</bdi>@endif</div><div><a class="video-title" href="{{ route('admin.videos.edit', ['video' => $video] + request()->only(['q', 'type', 'visibility', 'page'])) }}">{{ $video->title }}</a>@if($selected?->id === $video->id)<span class="video-public-label">المثال العام</span>@endif</div></div></td>
                    <td data-label="البرنامج والنوع"><span class="video-program">{{ $video->program === 'qudrat' ? 'القدرات' : 'مهارات اللغة العربية' }}</span><span>{{ $video->qudratCategory?->title ?? $video->arabicSkillBank?->title }}</span></td>
                    <td data-label="الحالة"><span class="video-status {{ $eligible ? 'video-success' : '' }}">{{ $eligible ? 'متاح للطلاب' : ($video->is_visible ? 'غير متاح للطلاب' : 'مخفي') }}</span><small class="{{ $video->processing_state === 'failed' || $video->processing_error ? 'video-error-text' : '' }}">{{ $video->processing_token ? 'جارٍ المعالجة' : (['pending' => 'بانتظار المعالجة', 'processing' => 'جارٍ المعالجة', 'failed' => 'فشلت المعالجة', 'ready' => ($video->processing_error ? 'تعذّر آخر تحديث' : 'جاهز')][$video->processing_state]) }}</small></td>
                    <td data-label="أول نشر"><bdi>{{ $video->published_at?->format('Y/m/d') ?? '—' }}</bdi></td>
                    <td class="video-row-actions"><a href="{{ route('admin.videos.edit', ['video' => $video] + request()->only(['q', 'type', 'visibility', 'page'])) }}">تحرير</a><button type="button" class="video-text-button" data-preview="{{ route('admin.videos.preview', [$video, 'media']) }}" data-poster="{{ $video->cover_path ? route('admin.videos.preview', [$video, 'cover']) : '' }}" data-title="{{ $video->title }}" @disabled(!$video->video_path)>معاينة</button><details class="video-actions-menu"><summary aria-label="إجراءات {{ $video->title }}">المزيد</summary><div><button type="button" data-action="visibility" data-url="{{ route('admin.videos.visibility', $video) }}" data-visible="{{ $video->is_visible ? '0' : '1' }}">{{ $video->is_visible ? 'إخفاء' : 'إظهار' }}</button><button type="button" data-action="example" data-example-id="{{ $selected?->id === $video->id ? '' : $video->id }}" data-title="{{ $video->title }}" @disabled(!$eligible && $selected?->id !== $video->id)>{{ $selected?->id === $video->id ? 'إزالة المثال العام' : 'اختيار كمثال عام' }}</button><button type="button" class="video-danger-text" data-action="delete" data-url="{{ route('admin.videos.destroy', $video) }}" data-title="{{ $video->title }}">حذف الفيديو</button></div></details></td>
                </tr>
            @endforeach
            </tbody></table>
        @endif
    </div>
    <div class="video-pagination">{{ $videos->links() }}</div>
    @include('admin.videos._preview')
</section>
@endsection
