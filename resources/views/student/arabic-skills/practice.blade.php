@extends('layouts.student')
@section('content')
<section data-qudrat-student data-arabic-root data-arabic-practice>
    <header class="session-toolbar">
        <h1 tabindex="-1" data-arabic-heading>{{ $question['bank']['title'] }}</h1>
        <span>السؤال {{ $session['position'] }} من {{ $session['question_count'] }}</span>
        <a class="practice-set-review" href="{{ route('student.arabic-skills.bank', $session['bank_id']) }}">المجموعات</a>
    </header>
    @if($errors->any())<div class="qudrat-errors" role="alert" tabindex="-1"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <progress aria-label="موضع السؤال في التدريب" value="{{ $session['position'] }}" max="{{ $session['question_count'] }}"></progress>
    <div class="arabic-workspace">
        <form method="POST" action="{{ route('student.arabic-skills.answer', [$session['id'], $item_id]) }}" data-arabic-action="answer">@csrf
            <x-arabic-skills.question :question="$question" :response="$response ?? old('response')" :disabled="$feedback !== null" :feedback="$feedback" :uid="'item-'.$item_id" :practice="true" />
            @unless($feedback)<div class="qudrat-practice-actions"><button class="qudrat-button" data-arabic-check>تحقق من الإجابة</button></div>@endunless
        </form>
        @if($feedback)
            @php
                $feedbackTitle = ['correct' => 'إجابة صحيحة', 'partial' => 'إجابة صحيحة جزئيًا', 'incorrect' => 'إجابة غير صحيحة'][$feedback['result']];
                $feedbackScore = $question['type'] === 'matching' ? [$feedback['earned_points'], $feedback['max_score']] : null;
            @endphp
            <x-session-feedback :result="$feedback['result']" :title="$feedbackTitle" :score="$feedbackScore" id="arabic-feedback">
                <x-slot:action>
                    <form method="POST" action="{{ route('student.arabic-skills.next', [$session['id'], $item_id]) }}" data-arabic-action="next">@csrf
                        <button class="qudrat-button">{{ $session['position'] === $session['question_count'] ? 'عرض النتيجة' : 'السؤال التالي' }}</button>
                    </form>
                </x-slot:action>
                <x-arabic-skills.feedback :question="$question" :response="$response" :feedback="$feedback" :embedded="true" />
            </x-session-feedback>
        @elseif($question['has_hint'])
            <form method="POST" action="{{ route('student.arabic-skills.hint', [$session['id'], $item_id]) }}" data-arabic-hint>@csrf<button class="qudrat-hint-button">إظهار تلميح</button></form>
            @php
                $hint = session('arabic_hint');
            @endphp
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
