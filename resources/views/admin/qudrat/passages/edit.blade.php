@extends('layouts.admin')
@section('content')
<section data-qudrat-admin>
    @include('admin.qudrat._nav')
    <div class="qudrat-heading"><div><h2>تحرير النص {{ $passage->sort_order }}</h2><p>{{ $passage->category->title }}</p></div><a href="{{ route('admin.qudrat.passages.index') }}">جميع النصوص</a></div>
    <p class="qudrat-notice">هذا النص مشترك بين {{ $questions->count() }} أسئلة. ستظهر التعديلات في جميع معايناتها والتدريبات الجديدة. لن تتغير نسخ التدريبات السابقة.</p>
    <form class="qudrat-form qudrat-editor" action="{{ route('admin.qudrat.passages.update', $passage) }}" method="POST">@csrf @method('PUT')
        <input type="hidden" name="revision" value="{{ hash('sha256', $passage->text) }}">
        <label for="passage-text">النص القرائي<textarea name="text" id="passage-text" rows="12" required maxlength="20000">{{ old('text', $passage->text) }}</textarea></label>
        <p class="qudrat-note">احتفظ بالفواصل وعلامات الترقيم. افصل الفقرات بسطر فارغ عند الحاجة بعد مراجعة المصدر.</p>
        <button class="qudrat-button" type="submit">حفظ النص المشترك</button>
    </form>
    <h3 class="qudrat-section-title">الأسئلة المرتبطة</h3>
    <ul class="qudrat-linked-questions">@foreach($questions as $question)
        <li><span>{{ $question->sort_order }}. {{ $question->prompt ?: 'سؤال غير مكتمل' }}</span><div><a href="{{ route('admin.qudrat.questions.edit', $question) }}">تحرير</a><a href="{{ route('admin.qudrat.questions.preview', $question) }}">معاينة</a></div></li>
    @endforeach</ul>
</section>
@endsection
