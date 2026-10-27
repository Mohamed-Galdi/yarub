@extends('layouts.admin')
@section('compact-admin', '1')
@section('content')
@php($selectedType = $video->qudrat_category_id ? 'q:'.$video->qudrat_category_id : ($video->arabic_skill_bank_id ? 'a:'.$video->arabic_skill_bank_id : ''))
<section data-video-admin dir="rtl" data-example-url="{{ route('admin.videos.public-example') }}" data-edit-base="{{ route('admin.videos.index') }}" data-upload-timeout="{{ max(150, min(1800, (int) config('video_library.request_timeout_seconds'))) * 1000 }}">
    <a class="video-back" href="{{ $returnUrl }}">العودة إلى الفيديوهات</a>
    <header class="video-page-heading"><div><h1>{{ $video->exists ? 'تحرير الفيديو' : 'رفع فيديو' }}</h1><p>{{ $video->exists ? 'حدّث البيانات أو استبدل الملفات دون تغيير رابط الفيديو.' : 'أضف شرحًا قصيرًا واختر نوعه.' }}</p></div>@if($video->exists)<span class="video-status {{ $video->isEligible() ? 'video-success' : '' }}">{{ $video->isEligible() ? 'متاح للطلاب' : ($video->is_visible ? 'غير متاح للطلاب' : 'مخفي') }}</span>@endif</header>
    <div class="video-error video-page-error" data-page-error role="alert" tabindex="-1" hidden></div>
    <p class="video-example-notice" data-page-success role="status" hidden></p>
    @if($selected)<div class="video-example-notice"><p><strong>هذا هو المثال العام.</strong> استبدال الفيديو يحدّث ما يشاهده الزوار بعد نجاح المعالجة.</p></div>@endif
    @if($video->processing_token)<p class="video-example-notice">تجري معالجة هذا الفيديو. يمكنك تحديث بياناته أو إخفاؤه الآن.</p>@endif
    @if($video->processing_error)<p class="video-error" role="alert">{{ $video->processing_error }}</p>@endif
    <div class="video-edit-grid">
        <div class="video-edit-main">
            <form class="video-editor" data-video-form data-kind="{{ $video->exists ? 'metadata' : 'upload' }}" method="POST" action="{{ $video->exists ? route('admin.videos.update', $video) : route('admin.videos.store') }}" enctype="multipart/form-data">
                @csrf @if($video->exists) @method('PATCH') @endif
                <h2>بيانات الفيديو</h2>
                <div class="video-field"><label for="video-title">العنوان <span>مطلوب</span></label><input id="video-title" name="title" value="{{ $video->title }}" required maxlength="255" aria-describedby="error-title"><p id="error-title" data-error="title" class="video-error-text" hidden></p></div>
                <div class="video-field"><label for="video-type">النوع <span>مطلوب</span></label><select id="video-type" name="type" required aria-describedby="error-type"><option value="">اختر النوع</option>@include('admin.videos._type-options', ['activeOnly' => true])</select><p id="error-type" data-error="type" class="video-error-text" hidden></p></div>
                <div class="video-field"><label for="video-description">الوصف <span>اختياري</span></label><textarea id="video-description" name="description" rows="3" maxlength="10000" aria-describedby="error-description">{{ $video->description }}</textarea><p id="error-description" data-error="description" class="video-error-text" hidden></p></div>
                @unless($video->exists)
                    @include('admin.videos._files', ['initial' => true])
                @endunless
                <div class="video-form-footer"><button class="video-button" type="submit">{{ $video->exists ? 'حفظ البيانات' : 'رفع وتجهيز الفيديو' }}</button><a href="{{ $returnUrl }}">إلغاء</a></div>
                @include('admin.videos._progress')
            </form>
            @if($video->exists)
                <form class="video-editor" data-video-form data-kind="media" method="POST" action="{{ route('admin.videos.replace', $video) }}" enctype="multipart/form-data">
                    @csrf
                    <h2>{{ $video->video_path ? 'الفيديو والغلاف' : 'إعادة المحاولة' }}</h2><p class="video-help">{{ $video->video_path ? 'يبقى الفيديو الحالي متاحًا إذا تعذّر استبداله.' : 'اختر ملفًا صالحًا لإكمال تجهيز هذا الفيديو.' }}</p>
                    @include('admin.videos._files', ['initial' => false])
                    @if($video->cover_origin === 'custom')<label class="video-check"><input type="checkbox" name="reset_cover" value="1">إزالة الغلاف المخصص وإنشاء غلاف من الفيديو</label>@endif
                    <div class="video-form-footer"><button class="video-button video-secondary" type="submit" @disabled($video->processing_token)>تحديث الملفات</button></div>
                    @include('admin.videos._progress')
                </form>
                <section class="video-management"><h2>الظهور والإدارة</h2><div class="video-management-actions"><button type="button" class="video-button video-secondary" data-action="visibility" data-url="{{ route('admin.videos.visibility', $video) }}" data-visible="{{ $video->is_visible ? '0' : '1' }}">{{ $video->is_visible ? 'إخفاء عن الطلاب' : 'إظهار للطلاب' }}</button><button type="button" class="video-text-button" data-action="example" data-example-id="{{ $selected ? '' : $video->id }}" data-title="{{ $video->title }}" @disabled(!$selected && !$video->isEligible())>{{ $selected ? 'إزالة المثال العام' : 'اختيار كمثال عام' }}</button></div><button type="button" class="video-text-button video-danger-text" data-action="delete" data-url="{{ route('admin.videos.destroy', $video) }}" data-title="{{ $video->title }}" data-return="{{ $returnUrl }}">حذف الفيديو</button></section>
            @endif
        </div>
        <aside class="video-preview-panel">
            <h2>معاينة</h2>
            <div class="video-preview-stage">
                <video data-form-video controls playsinline preload="none" @if($video->video_path) src="{{ route('admin.videos.preview', [$video, 'media']) }}" @endif @if($video->cover_path) poster="{{ route('admin.videos.preview', [$video, 'cover']) }}" @endif @if(!$video->video_path) hidden @endif></video>
                <p data-preview-placeholder @if($video->video_path) hidden @endif>اختر فيديو لمعاينته قبل الرفع.</p>
            </div>
            <p class="video-error-text" data-form-playback-error hidden>تعذّرت المعاينة. تأكد من الملف أو اختر فيديو آخر.</p>
            <div class="video-cover-preview" data-cover-preview @if(!$video->cover_path) hidden @endif>
                <span data-cover-image-slot>@if($video->cover_path)<img data-cover-image src="{{ route('admin.videos.preview', [$video, 'cover']) }}" alt="غلاف الفيديو" width="90" height="160">@endif</span>
                <p data-cover-caption>{{ $video->cover_origin === 'custom' ? 'غلاف مخصص' : 'غلاف من الفيديو' }}</p>
            </div>
            @if($video->duration_seconds)<dl class="video-file-info"><div><dt>المدة</dt><dd><bdi>{{ gmdate($video->duration_seconds >= 3600 ? 'H:i:s' : 'i:s', (int) round($video->duration_seconds)) }}</bdi></dd></div><div><dt>الأبعاد</dt><dd><bdi>{{ $video->width }} × {{ $video->height }}</bdi></dd></div></dl>@endif
        </aside>
    </div>
</section>
@endsection
