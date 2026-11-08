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
        <div class="panel-grid panel-learning-grid">
            @forelse ($courses as $course)
                <x-panel.learning-card :title="$course->title" :type="$course->type" :description="$course->description"
                    :href="route('admin.courses.view', $course->id)" :heading-level="3">
                    <x-slot:status>
                        <span @class(['panel-learning-card__status', 'is-published' => $course->is_published])>
                            {{ $course->is_published ? 'منشورة' : 'غير منشورة' }}
                        </span>
                    </x-slot:status>
                    <x-slot:details>
                        <div><dt>الدروس</dt><dd>{{ $course->content()->count() }} <span>درس</span></dd></div>
                        <div><dt>المشتركون</dt><dd>{{ $course->students()->count() }} <span>مشترك</span></dd></div>
                    </x-slot:details>
                    <x-slot:pricing>
                        <span class="panel-learning-card__price-label">سعر الدورة</span>
                        <p class="panel-learning-card__price"><bdi>{{ $course->price }}</bdi> <span>ر.س</span></p>
                    </x-slot:pricing>
                    <x-panel.button :href="route('admin.courses.view', $course->id)" :aria-label="'تصفح البيانات: '.$course->title">
                        تصفح البيانات
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="M19 12H5m6-6-6 6 6 6"/></svg>
                    </x-panel.button>
                    <x-panel.button :href="route('admin.courses.edit', $course->id)" variant="quiet" :aria-label="'تعديل: '.$course->title">
                        <x-icons.edit aria-hidden="true" />
                        تعديل
                    </x-panel.button>
                </x-panel.learning-card>
            @empty
                <x-panel.card class="panel-grid-full">
                    <x-panel.empty title="لم يتم العثور على أي دورة" message="اضغط على زر الإضافة لإنشاء دورة." />
                </x-panel.card>
            @endforelse
        </div>
    </section>
@endsection
