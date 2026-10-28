@extends('layouts.student')
@section('content')
@php($bank = $entry['bank'])
<section data-qudrat-student data-arabic-root>
    @include('student.arabic-skills._header', ['heading' => $bank['title'], 'description' => $bank['subtitle']])
    <div class="arabic-bank-summary">
        <div><h2>تدريب سريع</h2><p>10 أسئلة · تُحسب الدرجة بحسب الأسئلة المختارة</p></div>
        @include('student.arabic-skills._start', ['resume' => collect($entry['active_sessions'])->firstWhere('mode', 'practice'), 'available' => $entry['can_start_practice'], 'bankActive' => $bank['is_active'], 'bankId' => $bank['id'], 'contextLabel' => $bank['title']])
    </div>
    <p class="qudrat-note">أجبت عن {{ $entry['attempted'] }} من {{ $entry['total'] }} سؤالًا متاحًا · {{ $entry['completed_sessions'] }} تدريب مكتمل</p>
    <progress aria-label="تقدّمك في البنك" value="{{ $entry['attempted'] }}" max="{{ max(1, $entry['total']) }}">{{ round($entry['coverage']) }}٪</progress>
    <h2 class="qudrat-section-title">النماذج التدريبية</h2>
    <div class="arabic-models">
        @forelse($entry['models'] as $model)
            <article class="arabic-model">
                <div><h3>{{ $model['title'] }}</h3><p class="qudrat-note">30 سؤالًا · 60 درجة</p>
                    @if($model['latest_completed'])<p class="qudrat-note">آخر نتيجة: <bdi dir="ltr">{{ $model['latest_completed']['earned_points'] }} / {{ $model['latest_completed']['available_points'] }}</bdi> · {{ round($model['latest_completed']['percentage']) }}٪</p>@endif
                </div>
                @include('student.arabic-skills._start', ['resume' => $model['resume'], 'available' => $model['can_start'], 'bankActive' => $bank['is_active'], 'bankId' => $bank['id'], 'modelId' => $model['id'], 'contextLabel' => $bank['title'].' — '.$model['title']])
            </article>
        @empty<p class="qudrat-empty">لا توجد نماذج متاحة لهذا البنك حاليًا.</p>@endforelse
    </div>
    <h2 class="qudrat-section-title" id="history">سجل التدريب</h2>
    <div class="arabic-history">
        @forelse($history['sessions'] as $attempt)
            @php($attemptTitle = $attempt['mode'] === 'model' ? (collect($entry['models'])->firstWhere('id', $attempt['model_id'])['title'] ?? 'نموذج تدريبي') : 'تدريب سريع')
            <article class="arabic-history-row">
                <div><h3>{{ $attemptTitle }}</h3>
                    <p class="qudrat-note">{{ \Carbon\Carbon::parse($attempt['completed_at'] ?? $attempt['started_at'])->format('Y/m/d H:i') }}
                        · {{ ['active' => 'قيد التدريب', 'abandoned' => 'غير مكتمل', 'completed' => 'مكتمل'][$attempt['status']] }}</p>
                </div>
                @if($attempt['status'] === 'completed')
                    <p><bdi dir="ltr">{{ $attempt['earned_points'] }} / {{ $attempt['available_points'] }}</bdi> <span class="qudrat-note">({{ round($attempt['percentage']) }}٪)</span></p>
                    <a class="arabic-secondary-link" aria-label="مراجعة الإجابات — {{ $bank['title'] }} — {{ $attemptTitle }} — {{ \Carbon\Carbon::parse($attempt['completed_at'])->format('Y/m/d H:i') }}" href="{{ route('student.arabic-skills.results', $attempt['id']) }}">مراجعة الإجابات</a>
                @elseif($attempt['status'] === 'active')<a class="arabic-secondary-link" aria-label="متابعة التدريب — {{ $bank['title'] }} — {{ $attemptTitle }}" href="{{ route('student.arabic-skills.show', $attempt['id']) }}">متابعة التدريب</a>@endif
            </article>
        @empty<p class="qudrat-empty">لم تبدأ تدريبًا في هذا البنك بعد.</p>@endforelse
    </div>
    @if($history['total'] > $history['per_page'])
        <nav class="qudrat-actions qudrat-pagination" aria-label="صفحات سجل التدريب">
            @if($history['page'] > 1)<a class="qudrat-button qudrat-secondary" href="{{ route('student.arabic-skills.bank', ['bank' => $bank['id'], 'page' => $history['page'] - 1]) }}#history">السابق</a>@endif
            <span>صفحة {{ $history['page'] }}</span>
            @if($history['page'] * $history['per_page'] < $history['total'])<a class="qudrat-button qudrat-secondary" href="{{ route('student.arabic-skills.bank', ['bank' => $bank['id'], 'page' => $history['page'] + 1]) }}#history">التالي</a>@endif
        </nav>
    @endif
</section>
@endsection
