@extends('layouts.student')
@section('content')
<section data-qudrat-student>
    @include('student.qudrat._header', ['heading' => 'تدرّب على القدرات اللفظية', 'description' => 'اختر قسمًا وابدأ تدريبًا قصيرًا. يُحفظ اختيارك وتقدّمك لتكمل في أي وقت.'])
    <p class="qudrat-note">حتى 10 أسئلة في التدريب. تبقى أسئلة النص القرائي معًا، وقد يزيد عددها قليلًا. التلميح لا يخصم من النتيجة.</p>
    <div class="qudrat-student-categories">
    @foreach($categories as $entry)
        @php
            $category = $entry['category'];
            $descriptions = ['verbal_analogies' => 'اكتشف العلاقة بين كلمتين واختر الزوج المماثل.', 'sentence_completion' => 'أكمل الفراغ بما يناسب معنى الجملة.', 'contextual_errors' => 'حدّد الكلمة التي لا تناسب سياق العبارة.', 'odd_word_out' => 'ميّز الكلمة المختلفة عن بقية المجموعة.', 'reading_comprehension' => 'اقرأ النص وأجب عن الأسئلة المرتبطة به.', 'association_and_difference' => 'تعرّف على الروابط والفروق بين الكلمات.'];
        @endphp
        <article class="qudrat-student-category">
            <h2>{{ $category->title }}</h2><p>{{ $descriptions[$category->slug] ?? '' }}</p>
            <dl class="qudrat-counts"><div><dt>الأسئلة المتاحة</dt><dd>{{ $entry['total'] }}</dd></div><div><dt>أجبت عن</dt><dd>{{ $entry['attempted'] }}</dd></div><div><dt>دقة أول إجابة</dt><dd>{{ $entry['accuracy'] === null ? '—' : $entry['accuracy'].'٪' }}</dd></div></dl>
            <label for="progress-{{ $category->id }}">التقدّم: {{ $entry['progress'] }}٪</label><progress id="progress-{{ $category->id }}" value="{{ $entry['attempted'] }}" max="{{ max(1, $entry['total']) }}">{{ $entry['progress'] }}٪</progress>
            <div class="qudrat-actions">
                @if($entry['active'])<a class="qudrat-button" href="{{ route('student.qudrat.show', $entry['active']) }}">متابعة التدريب</a>
                @elseif($entry['total'] > $entry['attempted'])<form method="POST" action="{{ route('student.qudrat.start', $category) }}">@csrf<input type="hidden" name="mode" value="practice"><button class="qudrat-button">ابدأ التدريب</button></form>
                @elseif($entry['total'] > 0)<p class="qudrat-note">أكملت جميع الأسئلة المتاحة. يمكنك الآن المراجعة.</p>
                @else<p class="qudrat-note">لا توجد أسئلة متاحة حاليًا. جرّب قسمًا آخر.</p>@endif
                @if(!$entry['active'] && $entry['attempted'] > 0)<form method="POST" action="{{ route('student.qudrat.start', $category) }}">@csrf<input type="hidden" name="mode" value="review"><button class="qudrat-button qudrat-secondary">مراجعة الأسئلة السابقة</button></form>@endif
            </div>
        </article>
    @endforeach
    </div>
    @if(empty($categories))<p class="qudrat-empty">لا توجد أقسام متاحة الآن. عُد لاحقًا لبدء التدريب.</p>@endif
    <p class="qudrat-note qudrat-metrics-note">التقدّم يحسب الأسئلة المختلفة التي أجبت عنها. دقة أول إجابة تعتمد على محاولتك الأولى فقط، ولا تتغيّر بإعادة التدريب.</p>
</section>
@endsection
