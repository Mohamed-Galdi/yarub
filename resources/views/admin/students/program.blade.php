@php($programTitle = $program === 'qudrat' ? 'القدرات' : 'مهارات اللغة العربية')
<div class="student-section-heading"><h2>{{ $programTitle }}</h2><p>تغطية الأسئلة ودقة أول إجابة حسب النوع.</p></div>
<div class="users-surface student-record-surface">
    <table class="student-data-table"><caption class="users-sr-only">تقدم الطالب حسب أنواع {{ $programTitle }}</caption>
        <thead><tr><th scope="col">النوع</th><th scope="col">تغطية الأسئلة</th><th scope="col">دقة أول إجابة</th><th scope="col">التدريبات المكتملة</th><th scope="col">آخر تدريب</th></tr></thead>
        <tbody>@forelse($types as $type)<tr><th scope="row">{{ $type['title'] }} @if(!$type['available'])<span class="student-unavailable-label">غير متاح حاليًا</span>@endif</th><td data-label="تغطية الأسئلة"><x-admin.student-coverage :metric="$type" :label="'تغطية '.$type['title']" /></td><td data-label="دقة أول إجابة">{{ $type['first_attempt_accuracy'] === null ? 'غير متاح' : $type['first_attempt_accuracy'].'٪' }}</td><td data-label="التدريبات المكتملة">{{ $type['completed_sessions'] }}</td><td data-label="آخر تدريب"><x-admin.tracking-time :value="$type['last_practice_at']" :relative="true" /></td></tr>@empty<tr><td colspan="5">لا توجد أنواع مسجلة في هذا البرنامج بعد.</td></tr>@endforelse</tbody>
    </table>
</div>
<p class="student-method-note">التغطية تخص الأسئلة المتاحة الآن، وتشمل الإجابات المحفوظة في تدريب غير مكتمل. دقة أول إجابة من المحاولات المسجلة، وقد تشمل استخدام التلميحات.</p>

<div class="student-history-heading"><h2>سجل التدريبات</h2>
    <form method="GET" action="{{ route('admin.view_student', $student->id) }}" class="student-history-filter">
        @foreach($navigation as $key => $value)@if(is_array($value))@foreach($value as $nestedKey => $nestedValue)<input type="hidden" name="{{ $key }}[{{ $nestedKey }}]" value="{{ $nestedValue }}">@endforeach @elseif($key !== $program.'_type' && $key !== $program.'_page')<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif @endforeach
        <input type="hidden" name="{{ $program }}_page" value="1">
        <label for="student-history-type">النوع</label><select id="student-history-type" name="{{ $program }}_type"><option value="">جميع الأنواع</option>@foreach($types as $type)<option value="{{ $type['id'] }}" @selected(($state[$program.'_type'] ?? null) === $type['id'])>{{ $type['title'] }}</option>@endforeach</select><button type="submit" class="users-button">تطبيق</button>
    </form>
</div>
<div class="users-surface student-record-surface">
    <table class="student-data-table student-history-table"><caption class="users-sr-only">سجل تدريبات {{ $programTitle }}</caption>
        <thead><tr><th scope="col">بدء التدريب</th><th scope="col">النوع / النمط</th><th scope="col">الحالة</th><th scope="col">الدرجة</th><th scope="col">المراجعة</th></tr></thead>
        <tbody>@forelse($history as $session)<tr><td data-label="بدء التدريب"><x-admin.tracking-time :value="$session['started_at']" /><span class="student-cell-note"><bdi dir="ltr">{{ \Carbon\Carbon::parse($session['started_at'])->format('H:i') }}</bdi></span></td><td data-label="النوع / النمط"><span title="اسم النوع الحالي؛ تتضمن المراجعة نسخة المحتوى وقت التدريب.">{{ $session['current_type_title'] }}</span><span class="student-cell-note">{{ ['practice' => 'تدريب', 'review' => 'مراجعة', 'model' => 'نموذج'][$session['mode']] ?? $session['mode'] }}@if($session['practice_set_title'] ?? null) · {{ $session['practice_set_title'] }}@endif @if($session['current_model_title']) · {{ $session['current_model_title'] }}@endif</span></td><td data-label="الحالة"><span class="student-session-status">{{ ['active' => 'جارٍ', 'completed' => 'مكتمل', 'closed' => 'مغلق', 'abandoned' => 'متروك دون إكمال'][$session['status']] ?? $session['status'] }}</span><span class="student-cell-note"><bdi dir="ltr">{{ $session['submitted_count'] }} / {{ $session['question_count'] }}</bdi> إجابة</span></td><td data-label="الدرجة">@include('admin.students.score', ['score' => $session['score']])</td><td class="student-review-link-cell"><a class="users-text-link" href="{{ route('admin.students.tracking.review', ['student' => $student->id, 'program' => $program, 'session' => $session['id']] + $navigation) }}" aria-label="مراجعة التدريب {{ $session['id'] }}">مراجعة التدريب</a></td></tr>@empty<tr><td colspan="5"><div class="student-inline-empty"><h3>لا توجد تدريبات مسجلة</h3><p>لم تُسجل تدريبات لهذا الطالب ضمن الاختيار الحالي.</p></div></td></tr>@endforelse</tbody>
    </table>
    {{ $history->onEachSide(1)->links('admin.students.page-links') }}
</div>
