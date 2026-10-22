@extends('layouts.student')
@section('content')
<section data-qudrat-student data-qudrat-practice>
    @include('student.qudrat._header', ['heading' => $question['title'], 'description' => $session->mode === 'review' ? 'مراجعة الأسئلة السابقة' : 'تدريب على أسئلة جديدة'])
    <div class="qudrat-session-progress"><p>السؤال {{ $session->current_position }} من {{ $total }}</p><progress aria-label="موضع السؤال في التدريب" value="{{ $session->current_position }}" max="{{ $total }}"></progress></div>
    <form method="POST" action="{{ route('student.qudrat.answer', [$session, $itemId]) }}" data-qudrat-answer data-selection-url="{{ route('student.qudrat.selection', [$session, $itemId]) }}">
        @csrf
        <x-qudrat.player :prompt="$question['prompt']" :options="$question['options']" :category="$question['category']" :title="$question['title']" :passage="$question['passage']" :selected="$selected" :disabled="$feedback !== null" :correct-key="$feedback['correct_key'] ?? null" name="option">
            <x-slot:hint>
                @if(!$feedback)
                    <div class="qudrat-hint-control">
                        @if($hint === null)
                            <button class="qudrat-hint-button" type="submit" data-qudrat-hint formaction="{{ route('student.qudrat.hint', [$session, $itemId]) }}">إظهار تلميح</button>
                            <span class="qudrat-note">مساعدة دون كشف الإجابة</span>
                        @else
                            <aside class="qudrat-hint" id="qudrat-hint" tabindex="-1" aria-labelledby="qudrat-hint-title">
                                <h2 id="qudrat-hint-title">تلميح للتفكير</h2><p class="qudrat-prose">{{ $hint }}</p>
                                <p class="qudrat-note">لا يخصم التلميح من نتيجتك.</p>
                            </aside>
                        @endif
                    </div>
                @endif
            </x-slot:hint>
            @if($feedback)
                <section class="qudrat-result-feedback {{ $feedback['correct'] ? 'qudrat-correct' : 'qudrat-incorrect' }}" id="qudrat-feedback" tabindex="-1" aria-labelledby="qudrat-feedback-title">
                    <h2 id="qudrat-feedback-title">{{ $feedback['correct'] ? 'إجابة صحيحة' : 'إجابة غير صحيحة' }}</h2>
                    <x-qudrat.feedback :correct-key="$feedback['correct_key']" :explanation="$feedback['explanation']" />
                    @if($feedback['used_hint'])<p>استخدمت تلميحًا في هذا السؤال، دون خصم من النتيجة.</p>@endif
                </section>
                <div class="qudrat-practice-actions"><button type="submit" form="qudrat-next" class="qudrat-button">{{ $session->current_position === $total ? 'عرض النتيجة' : 'السؤال التالي' }}</button></div>
            @else
                <div class="qudrat-practice-actions"><button class="qudrat-button" data-qudrat-check>تحقق من الإجابة</button><span class="qudrat-note" role="status" aria-live="polite" data-qudrat-save></span></div>
                <p class="qudrat-note">اختر إجابة ثم تحقق منها لعرض الشرح. لا يمكن تعديلها بعد التحقق.</p>
                <noscript><p class="qudrat-note">لحفظ اختيارك قبل التحقق اضغط «حفظ الاختيار».</p><button class="qudrat-button qudrat-secondary" formaction="{{ route('student.qudrat.selection', [$session, $itemId]) }}" name="_method" value="PATCH">حفظ الاختيار</button></noscript>
            @endif
        </x-qudrat.player>
    </form>
    @if($feedback)<form id="qudrat-next" method="POST" action="{{ route('student.qudrat.next', [$session, $itemId]) }}">@csrf</form>@endif
</section>
@endsection
