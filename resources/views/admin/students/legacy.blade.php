@php($tab = $state['tab'])
<nav class="student-legacy-tabs" aria-label="سجلات التعلم السابقة">
    @foreach(['courses' => ['الدورات', $student->active_courses_count], 'lessons' => ['الشروحات', $student->active_lessons_count], 'tests' => ['الاختبارات', $student->test_attempts_count], 'certificates' => ['الشهادات', $student->certificates_count]] as $key => [$title, $count])<a href="{{ route('admin.view_student', ['id' => $student->id] + array_replace($navigation, ['tab' => $key])) }}" @if($tab === $key) aria-current="page" @endif>{{ $title }} <span class="users-count">{{ $count }}</span></a>@endforeach
</nav>
<div class="users-surface student-record-surface">
    @if($records->isEmpty())<div class="student-inline-empty"><h2>لا توجد سجلات لعرضها</h2><p>لا توجد سجلات لهذا الطالب في القسم المحدد.</p></div>
    @else
    <table class="student-data-table"><caption class="users-sr-only">{{ ['courses'=>'الدورات','lessons'=>'الشروحات','tests'=>'الاختبارات','certificates'=>'الشهادات'][$tab] }}</caption>
        <thead><tr>
        @if($tab === 'courses')<th scope="col">عنوان الدورة</th><th scope="col">تاريخ الانضمام</th><th scope="col">مبلغ الاشتراك</th>
        @elseif($tab === 'lessons')<th scope="col">عنوان الشرح</th><th scope="col">الاشتراك</th><th scope="col">تاريخ الانضمام</th><th scope="col">تاريخ الانتهاء</th><th scope="col">مبلغ الاشتراك</th>
        @elseif($tab === 'tests')<th scope="col">الاختبار</th><th scope="col">الدورة / الشرح</th><th scope="col">الدرجة</th><th scope="col">التاريخ</th>
        @else<th scope="col">الدورة / الشرح</th><th scope="col">تاريخ الشهادة</th><th scope="col">التفاصيل</th>@endif
        </tr></thead><tbody>
        @foreach($records as $record)<tr>
            @if($tab === 'courses')
                <th scope="row">{{ $record->title }}</th><td data-label="تاريخ الانضمام"><x-admin.tracking-time :value="$record->pivot->created_at" empty="غير متاح" /></td><td data-label="مبلغ الاشتراك"><bdi>{{ $record->pivot->cost }}</bdi> ريال</td>
            @elseif($tab === 'lessons')
                <th scope="row">{{ $record->title }}</th><td data-label="الاشتراك">{{ $record->pivot->sub_plan === 'monthly' ? 'شهري' : 'سنوي' }}</td><td data-label="تاريخ الانضمام"><x-admin.tracking-time :value="$record->pivot->created_at" empty="غير متاح" /></td><td data-label="تاريخ الانتهاء"><x-admin.tracking-time :value="$record->pivot->sub_plan === 'monthly' ? $record->pivot->created_at?->copy()->addMonth() : $record->pivot->created_at?->copy()->addYear()" empty="غير متاح" /></td><td data-label="مبلغ الاشتراك"><bdi>{{ $record->pivot->cost }}</bdi> ريال</td>
            @elseif($tab === 'tests')
                <th scope="row">{{ $record->test?->title ?? 'غير متاح' }}</th><td data-label="الدورة / الشرح">{{ $record->test?->course?->title ?? $record->test?->lesson?->title ?? 'غير متاح' }}</td><td data-label="الدرجة">{{ $record->score === null ? 'غير متاح' : $record->score.'٪' }}</td><td data-label="التاريخ"><x-admin.tracking-time :value="$record->created_at" empty="غير متاح" /></td>
            @else
                <th scope="row">{{ $record->course?->title ?? $record->lesson?->title ?? 'غير متاح' }}</th><td data-label="تاريخ الشهادة"><x-admin.tracking-time :value="$record->created_at" empty="غير متاح" /></td><td><a class="users-text-link" href="{{ route('admin.certificates.view', ['id'=>$record->id]) }}" target="_blank" rel="noopener" aria-label="عرض الشهادة {{ $record->id }} في نافذة جديدة">عرض الشهادة</a></td>
            @endif
        </tr>@endforeach
        </tbody>
    </table>
    @endif
    {{ $records->onEachSide(1)->links('admin.students.page-links') }}
</div>
