@extends('layouts.student')
@section('content')
<section data-qudrat-student data-arabic-root>
    @include('student.arabic-skills._header', ['heading' => 'مهارات اللغة العربية'])
    <div class="qudrat-student-categories">
        @foreach($banks as $entry)
            @php
                $bank = $entry['bank'];
                $icon = ['nasaq' => 'sentence_completion', 'rasm' => 'contextual_errors', 'bayan' => 'reading_comprehension', 'midad' => 'verbal_analogies', 'burhan' => 'association_and_difference'][$bank['slug']] ?? 'reading_comprehension';
            @endphp
            <article class="qudrat-student-category">
                <div class="qudrat-card-title"><x-qudrat.category-icon :type="$icon" /><h2>{{ $bank['title'] }}</h2></div>
                <p class="qudrat-category-description">{{ $bank['subtitle'] }}</p>
                <p class="qudrat-bank"><strong>{{ $entry['total'] }}</strong> سؤال في البنك</p>
                <dl class="qudrat-counts"><div><dt>أجبت عن</dt><dd>{{ $entry['attempted'] }}</dd></div><div><dt>تدريبات مكتملة</dt><dd>{{ $entry['completed_sessions'] }}</dd></div></dl>
                <label for="arabic-progress-{{ $bank['id'] }}">التقدّم: {{ round($entry['coverage']) }}٪</label>
                <progress id="arabic-progress-{{ $bank['id'] }}" value="{{ $entry['attempted'] }}" max="{{ max(1, $entry['total']) }}">{{ round($entry['coverage']) }}٪</progress>
                <div class="qudrat-card-footer">
                    <span class="qudrat-session-badge">10 أسئلة في التدريب</span>
                    <div class="qudrat-actions">
                        <a class="qudrat-button" aria-label="اختيار مجموعة — {{ $bank['title'] }}" href="{{ route('student.arabic-skills.bank', $bank['id']) }}">اختيار مجموعة</a>
                        @unless($bank['is_active'])<p class="qudrat-note">البنك متوقف للتدريبات الجديدة.</p>@endunless
                    </div>
                </div>
            </article>
        @endforeach
    </div>
    @if(empty($banks))<p class="qudrat-empty">لا توجد بنوك متاحة الآن. عُد لاحقًا لبدء التدريب.</p>@endif
</section>
@endsection
