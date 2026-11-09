@extends('layouts.admin')
@section('panel-heading', true)
@section('content')
@php
    $d = $dashboard;
    $maxDaily = max(1, max(array_column($d['trend'], 'students')));
    $points = collect($d['trend'])->map(fn ($r, $i) => round(48 + $i * 884 / max(1, $d['days'] - 1), 2).','.round(180 - $r['students'] * 148 / $maxDaily, 2))->implode(' ');
@endphp
<div class="learning-dashboard">
    <x-panel.heading title="نشاط التعلّم">
        <x-slot:actions><nav class="analytics-period" aria-label="فترة التقرير">
            @foreach([7, 30, 90] as $days)
                <a href="{{ route('admin.dashboard', ['days' => $days]) }}" @if($d['days'] === $days) aria-current="page" @endif>{{ $days }} يومًا</a>
            @endforeach
        </nav></x-slot:actions>
    </x-panel.heading>
    <div class="analytics-context"><span><bdi>{{ $d['start']->format('Y/m/d') }}</bdi> — <bdi>{{ $d['end']->format('Y/m/d') }}</bdi></span><a href="#data-scope">تعريف المؤشرات</a></div>
    <dl class="analytics-summary">
        <div class="analytics-summary-primary"><dt>طلاب نشطون</dt><dd>{{ number_format($d['active']) }}</dd><small>السابق {{ number_format($d['previousActive']) }} @if($d['previousActive'])<bdi class="analytics-change">{{ $d['active'] > $d['previousActive'] ? '+' : '' }}{{ round(($d['active'] - $d['previousActive']) / $d['previousActive'] * 100) }}%</bdi>@endif</small></div>
        <div><dt>نشاط متكرر</dt><dd>{{ number_format($d['repeatStudents']) }}</dd><small>في يومين فأكثر · {{ $d['active'] ? round($d['repeatStudents'] / $d['active'] * 100) : 0 }}% من النشطين</small></div>
        <div><dt>جلسات مكتملة</dt><dd>{{ number_format(array_sum(array_column($d['programs'], 'completed'))) }}</dd><small>القدرات ومهارات اللغة</small></div>
        <div><dt>تفاعلات تعليمية</dt><dd>{{ number_format($d['actions']) }}</dd><small>إجابات واختبارات وفتح فيديوهات</small></div>
    </dl>
    <section class="analytics-surface analytics-trend" aria-labelledby="trend-heading">
        <div class="analytics-head"><div><h2 id="trend-heading">النشاط اليومي</h2></div><span class="analytics-key">طلاب نشطون</span></div>
        @if($d['active'])
        <p class="analytics-mobile-axis">النطاق اليومي: 0–{{ $maxDaily }} طالبًا</p>
        <svg class="analytics-chart" viewBox="0 0 960 220" preserveAspectRatio="none" role="img" aria-labelledby="chart-title chart-desc" dir="ltr">
            <title id="chart-title">النشاط اليومي خلال {{ $d['days'] }} يومًا</title><desc id="chart-desc">أعلى قيمة {{ $maxDaily }} طالبًا. البيانات التفصيلية في الجدول أدناه.</desc>
            @foreach([0, 0.5, 1] as $f)
                <line x1="48" x2="932" y1="{{ 180 - $f * 148 }}" y2="{{ 180 - $f * 148 }}" class="analytics-gridline" />
                <text x="34" y="{{ 185 - $f * 148 }}" text-anchor="end">{{ round($maxDaily * $f, 1) }}</text>
            @endforeach
            <polygon points="48,180 {{ $points }} 932,180" class="analytics-area" /><polyline points="{{ $points }}" class="analytics-line" />
            @foreach($d['trend'] as $row) @if($loop->first || $loop->last || $loop->index === intdiv($d['days'], 2))<text x="{{ 48 + $loop->index * 884 / max(1, $d['days'] - 1) }}" y="210" text-anchor="middle">{{ $row['label'] }}</text>@endif @endforeach
        </svg>
        <div class="analytics-mobile-axis analytics-chart-dates" dir="ltr"><bdi>{{ $d['start']->format('m/d') }}</bdi><bdi>{{ $d['end']->format('m/d') }}</bdi></div>
        @else<div class="analytics-empty"><h3>لا نشاط مسجّل في هذه الفترة</h3><p>جرّب فترة أطول.</p></div>@endif
        <details><summary>عرض البيانات اليومية</summary><div class="analytics-table-wrap" tabindex="0" role="region" aria-label="بيانات النشاط اليومي"><table><thead><tr><th scope="col">التاريخ</th><th scope="col">طلاب نشطون</th><th scope="col">تفاعلات</th></tr></thead><tbody>@foreach($d['trend'] as $row)<tr><th scope="row"><bdi>{{ $row['date'] }}</bdi></th><td>{{ $row['students'] }}</td><td>{{ $row['actions'] }}</td></tr>@endforeach</tbody></table></div></details>
    </section>
    <section class="analytics-performance" aria-labelledby="performance-heading">
        <div class="analytics-head"><div><h2 id="performance-heading">أداء البرامج</h2></div></div>
        <div class="analytics-programs">
        @foreach($d['programs'] as $key => $program)
            <article class="analytics-program" aria-labelledby="{{ $key }}-heading">
                <div class="analytics-head"><h3 id="{{ $key }}-heading">{{ $program['title'] }}</h3><a href="{{ route($key === 'qudrat' ? 'admin.qudrat.index' : 'admin.arabic-skills.index') }}">المحتوى</a></div>
                <dl class="analytics-program-stats"><div><dt>طلاب متفاعلون</dt><dd>{{ number_format($program['students']) }}</dd></div><div><dt>إجابات</dt><dd>{{ number_format($program['answers']) }}</dd></div></dl>
                @if($program['rows']->isEmpty())<div class="analytics-empty">لا توجد تصنيفات بعد.</div>@else
                <div class="analytics-table-wrap" tabindex="0" role="region" aria-label="أداء تصنيفات {{ $program['title'] }}"><table class="analytics-ranking"><thead><tr><th scope="col">التصنيف / الطلاب</th><th scope="col">الإجابات</th><th scope="col">الإكمال</th></tr></thead><tbody>
                    @foreach($program['rows'] as $row)<tr><th scope="row"><span class="analytics-name">{{ $row->title }} @unless($row->available)<small>غير متاح حاليًا</small>@endunless</span><span class="analytics-bar-row"><span class="analytics-bar" aria-hidden="true"><i style="width: {{ round($row->students / max(1, $program['rows']->max('students')) * 100, 2) }}%"></i></span><b>{{ number_format($row->students) }}</b></span></th><td>{{ number_format($row->answers) }}</td><td>@if($row->started)<bdi>{{ round($row->finished / $row->started * 100) }}%</bdi><small>{{ $row->finished }} من {{ $row->started }}</small>@else<span aria-label="لا توجد جلسات بدأت خلال الفترة">—</span>@endif</td></tr>@endforeach
                </tbody></table></div>@endif
                <div class="analytics-program-footer"><span>{{ number_format($program['started']) }} جلسة بدأت · {{ number_format($program['completed']) }} اكتملت</span>@if($program['unused'])<span class="analytics-unused">{{ $program['unused'] }} تصنيف بلا إجابات</span>@endif</div>
            </article>
        @endforeach
        </div>
    </section>
    <section class="analytics-surface analytics-courses" aria-labelledby="courses-heading">
        <div class="analytics-head"><div><h2 id="courses-heading">أداء الدورات</h2></div><a href="{{ route('admin.courses') }}">كل الدورات</a></div>
        @if($d['courses']->isEmpty())<div class="analytics-empty">لا توجد دورات لعرضها.</div>@else
        <div class="analytics-table-wrap" tabindex="0" role="region" aria-label="أداء الدورات"><table><thead><tr><th scope="col">الدورة</th><th scope="col">طلاب الاختبارات</th><th scope="col">التسليمات</th><th scope="col">ملتحقون جدد</th></tr></thead><tbody>
            @foreach($d['courses'] as $course)<tr><th scope="row"><a href="{{ route('admin.courses.view', $course->id) }}">{{ $course->title }}</a>@unless($course->is_published)<small>غير منشورة</small>@endunless</th><td>{{ number_format($course->learners) }}</td><td>{{ number_format($course->attempts) }}</td><td>{{ number_format($course->students) }}</td></tr>@endforeach
        </tbody></table></div>@endif
        <p class="analytics-footnote">المشاهدة غير متتبّعة؛ الأرقام تعكس الالتحاق والاختبارات فقط.</p>
        @if($d['quietCourses']->isNotEmpty())
        <details><summary>دورات بلا تسليمات اختبار</summary><ul class="analytics-quiet">@foreach($d['quietCourses'] as $course)<li><a href="{{ route('admin.courses.view', $course->id) }}">{{ $course->title }}</a><span>{{ $course->students }} ملتحق جديد</span></li>@endforeach</ul><p class="analytics-footnote">الأقل التحاقًا أولًا؛ قد تكون بلا اختبارات أصلًا.</p></details>
        @endif
    </section>
    <section class="analytics-surface analytics-library" aria-labelledby="library-heading">
        <div class="analytics-head"><div><h2 id="library-heading">مكتبة الفيديو</h2><p>{{ number_format($d['videos']) }} فيديو متاح حاليًا</p></div><a href="{{ route('admin.videos.index') }}">إدارة المكتبة</a></div>
        <dl class="analytics-program-stats"><div><dt>مرات الفتح</dt><dd>{{ number_format($d['videoActivity']['opens']) }}</dd></div><div><dt>طلاب فريدون</dt><dd>{{ number_format($d['videoActivity']['viewers']) }}</dd></div></dl>
        <p class="analytics-footnote">فتح المشغّل يُحتسب مرة؛ إعادة الفتح تُحتسب مجددًا. لا نقيس مدة المشاهدة.</p>
        @if($d['videoRankings']->isNotEmpty())
        <div class="analytics-table-wrap" tabindex="0" role="region" aria-label="أداء الفيديوهات"><table><thead><tr><th scope="col">الفيديو</th><th scope="col">مرات الفتح</th><th scope="col">طلاب فريدون</th></tr></thead><tbody>
            @foreach($d['videoRankings'] as $video)<tr><th scope="row"><a href="{{ route('admin.videos.edit', $video->id) }}">{{ $video->title }}</a></th><td>{{ number_format($video->opens) }}</td><td>{{ number_format($video->viewers) }}</td></tr>@endforeach
        </tbody></table></div>
        @else<div class="analytics-empty">لا توجد فيديوهات لعرضها.</div>@endif
        @if($d['videoCategoryActivity']->isNotEmpty())
        <details><summary>حسب التصنيف</summary><div class="analytics-table-wrap" tabindex="0" role="region" aria-label="فتح الفيديوهات حسب التصنيف"><table><thead><tr><th scope="col">التصنيف</th><th scope="col">مرات الفتح</th><th scope="col">طلاب فريدون</th></tr></thead><tbody>
            @foreach($d['videoCategoryActivity'] as $category)<tr><th scope="row">{{ $category->qudrat_title ?? $category->arabic_title }}<small>{{ $category->qudrat_category_id ? 'القدرات' : 'مهارات اللغة العربية' }}</small></th><td>{{ $category->opens }}</td><td>{{ $category->viewers }}</td></tr>@endforeach
        </tbody></table></div></details>@endif
    </section>
    <footer class="analytics-scope" id="data-scope">
        <details><summary>تعريف المؤشرات</summary><div class="analytics-definitions">
            <p><strong>النشاط:</strong> إجابة تدريب أو تسليم اختبار أو فتح فيديو بالمكتبة. الطالب يُحتسب مرة واحدة في الإجمالي، وقد يظهر في أكثر من تصنيف. يشمل إعادة التدريب وتخطّي الأسئلة، ويستبعد المشرفين والطلاب المحذوفين.</p>
            <p><strong>الجلسات المكتملة:</strong> اكتملت خلال الفترة، ولو بدأت قبلها. نسبة الإكمال في التصنيفات تخص الجلسات التي بدأت في الفترة واكتملت حتى وقت التقرير، وتشمل التدريب والمراجعة؛ وليست مقياسًا للإتقان.</p>
            <p><strong>الترتيب:</strong> التصنيفات حسب الطلاب المتفاعلين ثم الإجابات؛ أعلى 10 دورات حسب طلاب الاختبارات ثم الملتحقين الجدد. غياب التسليمات لا يكفي للحكم على جودة الدورة.</p>
            <p><strong>الفترة:</strong> بيانات اليوم غير مكتملة. مقارنة النشطين بالفترة السابقة تستخدم المدة نفسها. إحصاءات فتح الفيديوهات تخص الفترة المختارة والسجلات منذ تفعيل التتبع فقط؛ عدد الفيديوهات المتاحة يعكس الحالة الحالية.</p>
            <p>حتى <bdi>{{ $d['end']->format('H:i') }}</bdi> · {{ config('app.timezone') }} · قد تتأخر البيانات دقيقتين.</p>
        </div></details>
    </footer>
</div>
@endsection
