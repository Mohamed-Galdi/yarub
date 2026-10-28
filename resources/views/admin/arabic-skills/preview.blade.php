@extends('layouts.admin')
@section('content')
<section data-qudrat-admin data-arabic-admin data-arabic-root dir="rtl">
    @include('admin.arabic-skills._nav')
    <div class="qudrat-heading"><div><h2>معاينة السؤال</h2><p>{{ $question->bank->title }} · {{ $question->sourceModel?->title ?? 'إضافة المشرف' }} · {{ $question->is_active ? 'منشور' : 'مؤرشف' }}</p></div><a class="qudrat-button" href="{{ route('admin.arabic-skills.questions.edit', $question) }}">تحرير السؤال</a></div>
    <div class="qudrat-player"><x-arabic-skills.question :question="$question->only(['prompt', 'type', 'options', 'matching_items'])" /></div>
    <details class="qudrat-preview-notes"><summary>الإجابة الصحيحة والتفسير</summary><x-arabic-skills.feedback :question="$question->only(['type', 'options', 'matching_items'])" :response="$question->answer" :feedback="['result' => 'correct', 'earned_points' => $question->max_score, 'max_score' => $question->max_score, 'answer' => $question->answer, 'explanation' => $question->explanation]" /></details>
    @if($question->hint)<details class="qudrat-preview-notes"><summary>التلميح</summary><p class="qudrat-prose">{{ $question->hint }}</p></details>@else<p class="qudrat-note">لا يوجد تلميح؛ زر التلميح مخفي للطالب.</p>@endif
</section>
@endsection
