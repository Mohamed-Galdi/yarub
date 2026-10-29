@extends('layouts.student')
@section('content')
<section data-qudrat-student data-arabic-root>
    @include('student.arabic-skills._header', ['heading' => 'نتيجة التدريب', 'description' => $reviews[0]['question']['bank']['title'] ?? 'مهارات اللغة العربية'])
    @if($session['practice_set_title'])<p class="qudrat-note">{{ $session['practice_set_title'] }}</p>@endif
    <dl class="qudrat-counts qudrat-result-counts">
        <div><dt>الدرجات</dt><dd><bdi dir="ltr">{{ $session['earned_points'] }} / {{ $session['available_points'] }}</bdi></dd></div>
        <div><dt>النسبة</dt><dd>{{ round($session['percentage'], 1) }}٪</dd></div>
        <div><dt>صحيحة</dt><dd>{{ $correct }}</dd></div><div><dt>صحيحة جزئيًا</dt><dd>{{ $partial }}</dd></div><div><dt>غير صحيحة</dt><dd>{{ $incorrect }}</dd></div>
    </dl>
    <div class="qudrat-actions">
        @if($session['model_id'] || $session['practice_set_key'])<form method="POST" action="{{ route('student.arabic-skills.start', $session['bank_id']) }}">@csrf<input type="hidden" name="mode" value="{{ $session['mode'] }}">@if($session['model_id'])<input type="hidden" name="model_id" value="{{ $session['model_id'] }}">@endif @if($session['practice_set_key'])<input type="hidden" name="set_key" value="{{ $session['practice_set_key'] }}">@endif<button class="qudrat-button">تدرّب مرة أخرى</button></form>@endif
        <a class="qudrat-button qudrat-secondary" href="{{ route('student.arabic-skills.bank', $session['bank_id']) }}">العودة إلى البنك</a>
    </div>
    <h2 class="qudrat-section-title">مراجعة الإجابات</h2>
    @foreach($reviews as $review)
        <article class="qudrat-answer-review">
            <h3>السؤال {{ $review['position'] }}</h3>
            <x-arabic-skills.question :question="$review['question']" :response="$review['response']" :disabled="true" :review="true" :uid="'review-'.$review['item_id']" :name="'review_'.$review['item_id']" />
            <x-arabic-skills.feedback :question="$review['question']" :response="$review['response']" :feedback="$review['feedback']" :review="true" />
        </article>
    @endforeach
</section>
@endsection
