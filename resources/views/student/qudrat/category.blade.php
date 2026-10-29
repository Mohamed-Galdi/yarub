@extends('layouts.student')
@section('content')
<section data-qudrat-student dir="rtl">
    @include('student.qudrat._header', ['heading' => $category->title])
    @if($category->slug === 'reading_comprehension')<p class="qudrat-notice">تبقى أسئلة النص الواحد معًا؛ لذلك قد يزيد عدد أسئلة بعض المجموعات على عشرة.</p>@endif
    @foreach($legacySessions as $legacy)
        <div class="qudrat-notice">لديك تدريب سابق لم يكتمل. <a href="{{ route('student.qudrat.show', $legacy) }}">متابعة التدريب السابق</a></div>
    @endforeach
    <div class="practice-sets-heading"><h2>مجموعات التدريب</h2><span>{{ count($sets) }} مجموعة</span></div>
    <div class="practice-set-grid">
        @forelse($sets as $set)
            <x-practice-set-card :set="$set" :start-url="route('student.qudrat.start', $category)" show-route="student.qudrat.show" results-route="student.qudrat.results" :context="$category->title" />
        @empty<p class="qudrat-empty">لا توجد مجموعات متاحة حاليًا. عُد لاحقًا للتدريب.</p>@endforelse
    </div>
</section>
@endsection
