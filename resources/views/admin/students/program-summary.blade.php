<article class="student-program-summary">
    <h2><a href="{{ route('admin.view_student', ['id' => $student->id] + array_replace($navigation, ['tab' => $code])) }}">{{ $title }}</a></h2>
    <p class="student-field-label">تغطية الأسئلة المتاحة</p>
    <x-admin.student-coverage :metric="$metric" :label="'تغطية '.$title" />
    <dl class="student-summary-facts">
        <div><dt>دقة أول إجابة</dt><dd>{{ $metric['first_attempt_accuracy'] === null ? 'غير متاح' : $metric['first_attempt_accuracy'].'٪' }}</dd></div>
        <div><dt>التدريبات المكتملة</dt><dd>{{ $metric['completed_sessions'] }}</dd></div>
        <div><dt>آخر تدريب</dt><dd><x-admin.tracking-time :value="$metric['last_practice_at']" :relative="true" /></dd></div>
    </dl>
    <a class="users-text-link student-summary-link" href="{{ route('admin.view_student', ['id' => $student->id] + array_replace($navigation, ['tab' => $code])) }}">عرض تفاصيل {{ $title }}</a>
</article>
