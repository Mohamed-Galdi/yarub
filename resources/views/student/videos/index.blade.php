@extends('layouts.student')
@section('content')
<section class="student-videos" lang="ar" data-video-library data-endpoint="{{ route('student.videos.catalog') }}" data-player-endpoint="{{ url('/student-dashboard/videos') }}" data-canonical="{{ $canonicalUrl }}" aria-labelledby="video-library-title">
    <h1 id="video-library-title">الفيديوهات</h1>
    <p class="sv-intro">شروحات في القدرات ومهارات اللغة العربية.</p>
    <form class="sv-filters" action="{{ route('student.videos.index') }}" method="GET" role="search" aria-label="البحث في الفيديوهات" data-video-filters>
        <div><label for="video-search">ابحث عن فيديو</label><input id="video-search" type="search" name="q" maxlength="200" value="{{ $state['q'] }}" placeholder="عنوان الفيديو أو وصفه" autocomplete="off"></div>
        <div><label for="video-type">النوع</label><select id="video-type" name="type"><option value="">الكل</option>
            @foreach($typeGroups as $group)<optgroup label="{{ $group['label'] }}">@foreach($group['items'] as $type)<option value="{{ $group['prefix'] }}:{{ $type->id }}" @selected($state['type'] === $group['prefix'].':'.$type->id)>{{ $type->title }}</option>@endforeach</optgroup>@endforeach
        </select></div>
        <button class="sv-button" type="submit">بحث</button>
        <a class="sv-reset" href="{{ route('student.videos.index') }}" data-video-reset>مسح الفلاتر</a>
    </form>
    <p class="sv-live" role="status" aria-live="polite" data-video-status></p>
    <div class="sv-error" data-video-error role="alert" hidden><p data-video-error-message>تعذّر تحديث الفيديوهات. تحقق من الاتصال وأعد المحاولة.</p><button type="button" class="sv-button sv-secondary" data-video-retry>إعادة المحاولة</button></div>
    <div data-video-results aria-busy="false">@include('student.videos._results')</div>
    <noscript><p>فعّل JavaScript لفتح مشغّل الفيديو ومشاركته.</p></noscript>
    @include('student.videos._player')
</section>
@endsection
