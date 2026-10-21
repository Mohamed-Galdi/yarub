@extends('layouts.admin')
@section('content')
<section data-qudrat-admin>
    @include('admin.qudrat._nav')
    <div class="qudrat-heading"><div><h2>بنك الأسئلة</h2><p>ابحث في نص السؤال أو خياراته، وصفِّ النتائج حسب القسم والحالة.</p></div>
        <a class="qudrat-button" href="{{ route('admin.qudrat.questions.create', ['category_id' => $filters['category_id'] ?? null]) }}">إضافة سؤال</a>
    </div>
    @if(!empty($filters['category_id']) || !empty($filters['passage_id']))
        <p class="qudrat-notice">تُعرض أسئلة القسم أو النص المحدد. <a href="{{ route('admin.qudrat.questions.index') }}">عرض جميع الأسئلة</a></p>
    @endif
    <div class="qudrat-table" aria-label="قائمة أسئلة القدرات">
        <livewire:qudrat-questions-table :category-id="isset($filters['category_id']) ? (int) $filters['category_id'] : null" :passage-id="isset($filters['passage_id']) ? (int) $filters['passage_id'] : null" />
    </div>
</section>
@endsection
