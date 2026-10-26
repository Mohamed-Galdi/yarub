@extends('layouts.admin')
@section('compact-admin', true)
@section('content')
<section data-student-detail data-users-list dir="rtl" aria-labelledby="student-title" x-data>
    <a class="users-text-link student-back" href="{{ route('admin.students', $list) }}">العودة إلى المستخدمين</a>
    <header class="student-profile-header">
        <span class="users-avatar student-profile-avatar" aria-hidden="true" x-data="{ failed: false }"><span>{{ mb_substr(trim($summary['name']), 0, 1) ?: 'ط' }}</span>@if($summary['avatar'] && !str_starts_with($summary['avatar'], 'data:'))<img src="{{ asset($summary['avatar']) }}" alt="" width="52" height="52" x-cloak x-init="if ($el.complete && $el.naturalWidth === 0) failed = true" x-show="!failed" x-on:error="failed = true">@endif</span>
        <div class="student-profile-identity"><div class="student-profile-name"><h1 id="student-title" tabindex="-1">{{ $summary['name'] }}</h1><span class="users-status {{ $summary['blocked'] ? 'is-blocked' : '' }}">{{ $summary['blocked'] ? 'محظور' : 'فعال' }}</span></div><p class="student-email" dir="ltr">{{ $summary['email'] }}</p>
            <dl class="student-profile-dates"><div><dt>الانضمام</dt><dd><x-admin.tracking-time :value="$summary['joined_at']" empty="غير متاح" /></dd></div><div><dt>آخر تدريب</dt><dd><x-admin.tracking-time :value="$summary['last_practice_at']" :relative="true" /></dd></div></dl>
        </div>
        <div class="student-profile-actions">@include('admin.students.account-menu')</div>
    </header>

    @php($legacy = !in_array($state['tab'], ['overview','qudrat','arabic_skills'], true))
    <nav class="student-tabs" aria-label="تفاصيل الطالب">
        @foreach(['overview' => 'نظرة عامة', 'qudrat' => 'القدرات', 'arabic_skills' => 'مهارات اللغة العربية', 'courses' => 'سجلات التعلم'] as $tab => $title)
        <a href="{{ route('admin.view_student', ['id' => $student->id] + array_replace($navigation, ['tab' => $tab])) }}" @if($state['tab'] === $tab || ($tab === 'courses' && $legacy)) aria-current="page" @endif>{{ $title }}</a>
        @endforeach
    </nav>

    @if($state['tab'] === 'overview')
        <div class="student-overview users-surface">
            @foreach(['qudrat' => 'القدرات', 'arabic_skills' => 'مهارات اللغة العربية'] as $code => $title)
                @include('admin.students.program-summary', ['metric' => $summary['programs'][$code]])
            @endforeach
        </div>
        <p class="student-method-note">تعتمد الأرقام على الإجابات المحفوظة. التغطية للأسئلة المتاحة حاليًا وقد تتغير مع المحتوى؛ دقة أول إجابة تختلف عن درجة التدريب.</p>
        <nav class="student-legacy-shortcuts" aria-label="السجلات السابقة">
            @foreach(['courses' => ['الدورات', $student->active_courses_count], 'lessons' => ['الشروحات', $student->active_lessons_count], 'tests' => ['الاختبارات', $student->test_attempts_count], 'certificates' => ['الشهادات', $student->certificates_count]] as $tab => [$title, $count])<a href="{{ route('admin.view_student', ['id' => $student->id] + array_replace($navigation, ['tab' => $tab])) }}">{{ $title }} <span class="users-count">{{ $count }}</span></a>@endforeach
        </nav>
    @elseif(in_array($state['tab'], ['qudrat','arabic_skills'], true))
        @include('admin.students.program', ['program' => $state['tab']])
    @else
        @include('admin.students.legacy')
    @endif
</section>
@endsection
