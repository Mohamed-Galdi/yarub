@extends('layouts.admin')
@section('panel-heading', true)

@section('content')
    <x-panel.heading title="الدورات" description="إدارة الدورات ومراجعة الدروس والمشتركين.">
        <x-slot:actions>
            <x-panel.button :href="route('admin.courses.create')" variant="primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                إضافة دورة
            </x-panel.button>
        </x-slot:actions>
    </x-panel.heading>

    <section class="panel-section" aria-labelledby="course-list">
        <div class="panel-section-heading">
            <h2 id="course-list">قائمة الدورات</h2>
            <span class="panel-count">{{ $courses->count() }}</span>
        </div>
        <div class="panel-grid panel-grid--courses">
            @forelse ($courses as $course)
                <x-panel.card class="panel-course">
                    <header class="panel-course-heading">
                        <span @class(['panel-status', 'panel-status--published' => $course->is_published])>
                            {{ $course->is_published ? 'منشورة' : 'غير منشورة' }}
                        </span>
                        <h3 class="panel-card-title" dir="auto">{{ $course->title }}</h3>
                        <p dir="auto">{{ $course->type }}</p>
                    </header>
                    <dl class="panel-course-stats">
                        <div><dt>الدروس</dt><dd>{{ $course->content()->count() }}</dd></div>
                        <div><dt>المشتركون</dt><dd>{{ $course->students()->count() }}</dd></div>
                        <div><dt>المبلغ</dt><dd><bdi>{{ $course->price }}</bdi> ر.س</dd></div>
                    </dl>
                    <div class="panel-actions panel-course-actions">
                        <x-panel.button :href="route('admin.courses.view', $course->id)" :aria-label="'تصفح البيانات: '.$course->title">
                            تصفح البيانات
                        </x-panel.button>
                        <x-panel.button :href="route('admin.courses.edit', $course->id)" variant="quiet" :aria-label="'تعديل: '.$course->title">
                            تعديل
                        </x-panel.button>
                    </div>
                </x-panel.card>
            @empty
                <x-panel.card class="panel-grid-full">
                    <x-panel.empty title="لم يتم العثور على أي دورة" message="اضغط على زر الإضافة لإنشاء دورة." />
                </x-panel.card>
            @endforelse
        </div>
    </section>
@endsection
