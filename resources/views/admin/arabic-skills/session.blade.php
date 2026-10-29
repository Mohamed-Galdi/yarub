@extends('layouts.admin')
@section('content')
@php
    $firstSnapshot = $session->items->first()?->content_snapshot ?? [];
@endphp
<section data-qudrat-admin data-arabic-admin data-arabic-root dir="rtl">
    @include('admin.arabic-skills._nav')
    <div class="qudrat-heading"><div><h2>مراجعة التدريب {{ $session->id }}</h2><p>{{ $session->user?->name ?? 'طالب محذوف' }} · {{ $firstSnapshot['bank']['title'] ?? $session->bank->title }} · {{ $session->practice_set_title ?? $firstSnapshot['model_title'] ?? 'تدريب سريع' }}</p></div><a href="{{ route('admin.arabic-skills.sessions.index') }}">العودة إلى التدريبات</a></div>
    <dl class="qudrat-counts"><div><dt>الحالة</dt><dd>{{ ['active' => 'جارٍ', 'completed' => 'مكتمل', 'abandoned' => 'متروك دون إكمال'][$session->status] }}</dd></div><div><dt>النقاط{{ $session->status !== 'completed' ? ' غير النهائية' : '' }}</dt><dd><bdi dir="ltr">{{ $session->earned_points }} / {{ $session->available_points }}</bdi></dd></div><div><dt>الإجابات المسلّمة</dt><dd>{{ $session->items->whereNotNull('submitted_at')->count() }} من {{ $session->selected_question_count }}</dd></div></dl>
    <p class="qudrat-note">النسخة المحفوظة عند بدء التدريب. لا تغيّر تعديلات الأسئلة اللاحقة هذه الإجابات أو الدرجات.</p>
    @foreach($session->items as $item)
        <article class="qudrat-answer-review"><h3>السؤال {{ $item->position }}</h3>
            @if($item->submitted_at)
                <x-arabic-skills.question :question="$item->content_snapshot" :response="$item->response" :disabled="true" :review="true" :uid="'review-'.$item->id" :name="'review_'.$item->id" />
                <x-arabic-skills.feedback :question="$item->content_snapshot" :response="$item->response" :feedback="['result' => $item->result, 'earned_points' => $item->earned_points, 'max_score' => $item->content_snapshot['max_score'], 'answer' => $item->content_snapshot['answer'], 'explanation' => $item->content_snapshot['explanation']]" :review="true" />
                <p class="qudrat-note">وقت الإرسال: <bdi>{{ $item->submitted_at->format('Y-m-d H:i:s') }}</bdi></p>
            @else<p class="qudrat-note">لم تُرسل إجابة لهذا السؤال.</p>@endif
        </article>
    @endforeach
</section>
@endsection
