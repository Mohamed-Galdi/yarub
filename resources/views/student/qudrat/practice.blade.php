@extends('layouts.student')
@section('content')
<section data-qudrat-student>
    @include('student.qudrat._header', ['heading' => $question['title'], 'description' => $session->mode === 'review' ? 'مراجعة الأسئلة السابقة' : 'تدريب على أسئلة جديدة'])
    <div class="qudrat-session-progress"><p>السؤال {{ $session->current_position }} من {{ $total }}</p><progress aria-label="موضع السؤال في التدريب" value="{{ $session->current_position }}" max="{{ $total }}"></progress></div>
    <form method="POST" action="{{ route('student.qudrat.answer', [$session, $itemId]) }}" data-qudrat-answer data-selection-url="{{ route('student.qudrat.selection', [$session, $itemId]) }}">
        @csrf
        <x-qudrat.player :prompt="$question['prompt']" :options="$question['options']" :category="$question['category']" :title="$question['title']" :passage="$question['passage']" :selected="$selected" :disabled="$feedback !== null" name="option" />
        @if(!$feedback)
            <div class="qudrat-practice-actions"><button class="qudrat-button" data-qudrat-check>تحقق</button><button class="qudrat-button qudrat-secondary" type="submit" formaction="{{ route('student.qudrat.hint', [$session, $itemId]) }}">{{ $hint === null ? 'تلميح' : 'عرض التلميح مجددًا' }}</button><span class="qudrat-note" role="status" aria-live="polite" data-qudrat-save></span></div>
            <noscript><p class="qudrat-note">لحفظ اختيارك قبل التحقق اضغط «حفظ الاختيار».</p><button class="qudrat-button qudrat-secondary" formaction="{{ route('student.qudrat.selection', [$session, $itemId]) }}" name="_method" value="PATCH">حفظ الاختيار</button></noscript>
        @endif
    </form>
    @if($hint !== null)<aside class="qudrat-notice qudrat-hint" aria-label="التلميح"><h2>تلميح</h2><p class="qudrat-prose">{{ $hint }}</p></aside>@endif
    @if($feedback)
        <section class="qudrat-result-feedback {{ $feedback['correct'] ? 'qudrat-correct' : 'qudrat-incorrect' }}" role="status">
            <h2>{{ $feedback['correct'] ? 'إجابة صحيحة' : 'إجابة غير صحيحة' }}</h2>
            <x-qudrat.feedback :correct-key="$feedback['correct_key']" :explanation="$feedback['explanation']" />
            @if($feedback['used_hint'])<p class="qudrat-note">استخدمت تلميحًا في هذا السؤال، دون خصم من النتيجة.</p>@endif
        </section>
        <form method="POST" action="{{ route('student.qudrat.next', [$session, $itemId]) }}">@csrf<button class="qudrat-button">{{ $session->current_position === $total ? 'عرض النتيجة' : 'التالي' }}</button></form>
    @else<p class="qudrat-note qudrat-metrics-note">اختر إجابة ثم اضغط «تحقق». لا يمكنك تعديل الإجابة بعد التحقق منها.</p>@endif
</section>
@endsection
