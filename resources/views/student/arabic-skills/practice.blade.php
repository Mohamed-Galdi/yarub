@extends('layouts.student')
@section('content')
<section data-qudrat-student data-arabic-root data-arabic-practice>
    @include('student.arabic-skills._header', ['heading' => $question['bank']['title'], 'description' => $question['model_title'] ?? 'تدريب سريع'])
    <div class="arabic-session-top">
        <a class="arabic-secondary-link" href="{{ route('student.arabic-skills.bank', $session['bank_id']) }}">العودة إلى البنك</a>
        <span>السؤال {{ $session['position'] }} من {{ $session['question_count'] }}</span>
    </div>
    <progress aria-label="موضع السؤال في التدريب" value="{{ $session['position'] }}" max="{{ $session['question_count'] }}"></progress>
    <div class="arabic-workspace">
        <form method="POST" action="{{ route('student.arabic-skills.answer', [$session['id'], $item_id]) }}" data-arabic-action="answer">@csrf
            <x-arabic-skills.question :question="$question" :response="$response ?? old('response')" :disabled="$feedback !== null" :uid="'item-'.$item_id" />
            @unless($feedback)<div class="qudrat-practice-actions"><button class="qudrat-button" data-arabic-check>تحقق من الإجابة</button><span class="qudrat-note">اختر إجابتك ثم تحقق منها لعرض الشرح.</span></div>@endunless
        </form>
        @if($feedback)
            <div data-arabic-feedback-area tabindex="-1" aria-label="نتيجة الإجابة والخطوة التالية">
            <x-arabic-skills.feedback :question="$question" :response="$response" :feedback="$feedback" />
            <form method="POST" action="{{ route('student.arabic-skills.next', [$session['id'], $item_id]) }}" data-arabic-action="next" class="qudrat-practice-actions">@csrf
                <button class="qudrat-button">{{ $session['position'] === $session['question_count'] ? 'عرض النتيجة' : 'السؤال التالي' }}</button>
            </form>
            </div>
        @elseif($question['has_hint'])
            <form method="POST" action="{{ route('student.arabic-skills.hint', [$session['id'], $item_id]) }}" data-arabic-hint>@csrf<button class="qudrat-hint-button">إظهار تلميح</button></form>
            @php($hint = session('arabic_hint'))
            <aside class="qudrat-hint" tabindex="-1" aria-label="تلميح للتفكير" data-arabic-hint-content @if(($hint['item_id'] ?? null) !== $item_id) hidden @endif><h2>تلميح للتفكير</h2><p class="qudrat-prose">{{ ($hint['item_id'] ?? null) === $item_id ? $hint['hint'] : '' }}</p></aside>
        @endif
        <p class="qudrat-note" role="status" aria-live="polite" data-arabic-status></p>
        <details class="arabic-abandon"><summary>إنهاء التدريب دون إكماله</summary>
            <p class="qudrat-note">ستُحفظ إجاباتك السابقة، وسيظهر التدريب غير مكتمل. يمكنك بدء تدريب جديد.</p>
            <form method="POST" action="{{ route('student.arabic-skills.abandon', $session['id']) }}">@csrf<button class="qudrat-button qudrat-secondary">تأكيد إنهاء التدريب</button></form>
        </details>
    </div>
</section>
@endsection
