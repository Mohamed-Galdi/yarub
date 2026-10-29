@extends('layouts.student')
@section('content')
<section data-qudrat-student>
    @include('student.qudrat._header', ['heading' => $session->status === 'closed' ? 'توقّف هذا التدريب' : 'نتيجة التدريب'])
    @if($session->practice_set_title)<p class="qudrat-note">{{ $session->practice_set_title }}</p>@endif
    @if($session->status === 'closed')<p class="qudrat-notice">لم تعد بعض أسئلة التدريب متاحة. حُفظت إجاباتك السابقة، ويمكنك بدء تدريب جديد. الأسئلة غير المجاب عنها لا تُحسب أخطاء.</p>@endif
    <dl class="qudrat-counts qudrat-result-counts"><div><dt>الصحيحة</dt><dd>{{ $correct }}</dd></div><div><dt>غير الصحيحة</dt><dd>{{ $answered - $correct }}</dd></div><div><dt>تمت الإجابة</dt><dd>{{ $answered }} / {{ $total }}</dd></div><div><dt>استخدام التلميح</dt><dd>{{ $hints }}</dd></div><div><dt>دقة الإجابات</dt><dd>{{ $answered ? round($correct / $answered * 100) . '٪' : '—' }}</dd></div></dl>
    <div class="qudrat-actions">
        <a class="qudrat-button qudrat-secondary" href="{{ route('student.qudrat.results', ['session' => $session, 'mistakes' => !$mistakesOnly]) }}">{{ $mistakesOnly ? 'عرض جميع الإجابات' : 'مراجعة الأخطاء' }}</a>
        @if($session->practice_set_key)<form method="POST" action="{{ route('student.qudrat.start', $session->category_id) }}">@csrf<input type="hidden" name="mode" value="practice"><input type="hidden" name="set_key" value="{{ $session->practice_set_key }}"><button class="qudrat-button">إعادة التدريب</button></form>@endif
        <a class="qudrat-button qudrat-secondary" href="{{ route('student.qudrat.category', $session->category_id) }}">اختيار مجموعة أخرى</a>
        <a href="{{ route('student.qudrat.index') }}" class="qudrat-button qudrat-secondary">العودة للأقسام</a>
    </div>
    <h2 class="qudrat-section-title">{{ $mistakesOnly ? 'مراجعة الأخطاء' : 'مراجعة الإجابات' }}</h2>
    @php($shown = collect($reviews)->filter(fn($review) => !$mistakesOnly || !$review['correct']))
    @forelse($shown as $review)
        <article class="qudrat-answer-review">
            <h3>{{ $review['correct'] ? 'إجابة صحيحة' : 'إجابة غير صحيحة' }}</h3>
            <x-qudrat.player :prompt="$review['question']['prompt']" :options="$review['question']['options']" :category="$review['question']['category']" :title="$review['question']['title']" :passage="$review['question']['passage']" :selected="$review['selected']" :disabled="true" :name="'review_'.$loop->index" />
            <x-qudrat.feedback :correct-key="$review['correct_key']" :explanation="$review['explanation']" />
            @if($review['used_hint'])<p class="qudrat-note">استُخدم تلميح</p>@endif
        </article>
    @empty<p class="qudrat-empty">{{ $mistakesOnly && $answered ? 'أحسنت، لا توجد أخطاء للمراجعة في هذا التدريب.' : 'لا توجد إجابات لعرضها.' }}</p>@endforelse
</section>
@endsection
