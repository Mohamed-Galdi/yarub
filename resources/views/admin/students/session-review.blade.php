@extends('layouts.admin')
@section('compact-admin', true)
@section('content')
@php($programTitle = $program === 'qudrat' ? 'القدرات' : 'مهارات اللغة العربية')
<section data-student-review data-student-detail data-users-list dir="rtl" aria-labelledby="review-title">
    <nav class="student-review-breadcrumb" aria-label="مسار المراجعة"><a class="users-text-link" href="{{ route('admin.students', $list) }}">المستخدمون</a><span aria-hidden="true">/</span><a class="users-text-link" href="{{ route('admin.view_student', ['id' => $student] + $state + ['list' => $list]) }}">{{ $summary['name'] }} · {{ $programTitle }}</a></nav>
    <header class="student-review-heading"><div><h1 id="review-title">مراجعة التدريب <bdi>{{ $review['session']['id'] }}</bdi></h1><p>{{ $summary['name'] }} · {{ $programTitle }}@if($review['session']['practice_set_title'] ?? null) · {{ $review['session']['practice_set_title'] }}@endif</p></div><a class="users-button" href="{{ route('admin.view_student', ['id' => $student] + $state + ['list' => $list]) }}">العودة إلى سجل الطالب</a></header>
    <dl class="student-review-facts"><div><dt>الحالة</dt><dd>{{ ['active'=>'جارٍ','completed'=>'مكتمل','closed'=>'مغلق','abandoned'=>'متروك دون إكمال'][$review['session']['status']] ?? $review['session']['status'] }}</dd></div><div><dt>بدء التدريب</dt><dd><x-admin.tracking-time :value="$review['session']['started_at']" /></dd></div><div><dt>الإجابات المحفوظة</dt><dd><bdi dir="ltr">{{ $review['session']['submitted_count'] }} / {{ $review['session']['question_count'] }}</bdi></dd></div><div><dt>الدرجة</dt><dd>@include('admin.students.score', ['score' => $review['session']['score']])</dd></div></dl>
    <p class="student-method-note">مراجعة للقراءة فقط من النسخة المحفوظة عند بدء التدريب. لا تغيّر تعديلات المحتوى اللاحقة هذه الإجابات أو الدرجات.</p>
    @if($program === 'arabic_skills')<p class="student-method-note">استخدام التلميحات غير متاح في السجل المحفوظ لهذا البرنامج.</p>@endif
    @if($review['session']['closure_reason'] === 'content_unavailable')<p class="student-method-note">أُغلق هذا التدريب لأن بعض محتواه لم يعد متاحًا للطلاب.</p>@endif

    @forelse($review['items'] as $item)
    <article class="student-question-review users-surface">
        <header class="student-question-heading"><h2>السؤال {{ $item['position'] }}</h2><span class="student-result student-result-{{ $item['result'] }}">{{ ['correct'=>'إجابة صحيحة','incorrect'=>'إجابة غير صحيحة','partial'=>'إجابة صحيحة جزئيًا','unanswered'=>'لم تُرسل إجابة','skipped'=>'متجاوز','unavailable'=>'نتيجة غير متاحة'][$item['result']] ?? 'نتيجة غير متاحة' }}</span></header>
        @if(!$item['snapshot_available'])<p class="student-method-note">غير متاح: نسخة السؤال التاريخية غير مكتملة. لا يُستبدل بها السؤال الحالي.</p>
        @else
            @php($question = $item['question'])
            @if($program === 'qudrat')
                <div class="student-review-workspace {{ !empty($question['passage']) ? 'has-passage' : '' }}">
                    @if(!empty($question['passage']))<x-qudrat.passage :text="$question['passage']" />@endif
                    <section aria-label="السؤال وخياراته"><h3 class="student-review-prompt">{{ $question['prompt'] }}</h3><ul class="student-readonly-options" aria-label="الخيارات المحفوظة">@foreach($question['options'] as $option)<li><span class="student-review-prose">{{ ['a'=>'أ','b'=>'ب','c'=>'ج','d'=>'د'][$option['key']] ?? $option['key'] }} · {{ $option['text'] }}</span><span class="student-option-marks">@if($item['response'] === $option['key'] && $item['submitted_at'])<span>اختيار الطالب</span>@endif @if($question['correct_key'] === $option['key'])<span class="student-correct-label">الإجابة الصحيحة</span>@endif</span></li>@endforeach</ul></section>
                </div>
                <p class="student-method-note">{{ $item['used_hint'] ? 'طُلب تلميح لهذا السؤال.' : 'لا يوجد تلميح مسجل لهذا السؤال.' }}</p>
            @else
                @include('admin.students.arabic-review')
            @endif
            @if(!empty($question['explanation']))<details class="student-explanation"><summary>التفسير</summary><p class="student-review-prose">{{ $question['explanation'] }}</p></details>@else<p class="student-method-note">التفسير غير متاح في النسخة المحفوظة.</p>@endif
        @endif
        @if($item['submitted_at'])<p class="student-submission-time">وقت الإرسال: <time datetime="{{ $item['submitted_at'] }}"><bdi dir="ltr">{{ \Carbon\Carbon::parse($item['submitted_at'])->format('Y-m-d H:i:s') }}</bdi> UTC</time></p>@endif
    </article>
    @empty<div class="users-surface student-inline-empty"><h2>لا توجد أسئلة محفوظة</h2><p>تفاصيل أسئلة هذا التدريب غير متاحة.</p></div>@endforelse
</section>
@endsection
