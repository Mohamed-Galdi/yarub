@extends('layouts.admin')
@section('panel-heading', true)

@section('content')
    <x-panel.heading title="الشروحات">
        <x-slot:actions>
            <x-panel.button :href="route('admin.lessons.create')" variant="primary">إضافة شرح</x-panel.button>
        </x-slot:actions>
    </x-panel.heading>

    <div class="panel-section panel-grid panel-grid--courses">
        @forelse ($lessons as $lesson)
            <x-panel.card class="panel-course">
                <header class="panel-course-heading">
                    <span @class(['panel-status', 'panel-status--published' => $lesson->is_published])>
                        {{ $lesson->is_published ? 'منشور' : 'غير منشور' }}
                    </span>
                    <h2 class="panel-card-title" dir="auto">{{ $lesson->title }}</h2>
                    <p dir="auto">{{ $lesson->type }}</p>
                </header>
                <dl class="panel-course-stats panel-course-stats--pair">
                    <div><dt>الدروس</dt><dd>{{ $lesson->content()->count() }}</dd></div>
                    <div><dt>المشتركون</dt><dd>{{ $lesson->students()->count() }}</dd></div>
                    <div><dt>شهريًا</dt><dd><bdi>{{ $lesson->monthly_price }}</bdi> ر.س</dd></div>
                    <div><dt>سنويًا</dt><dd><bdi>{{ $lesson->annual_price }}</bdi> ر.س</dd></div>
                </dl>
                <div class="panel-actions">
                    <x-panel.button :href="route('admin.lessons.view', $lesson->id)">تصفح البيانات</x-panel.button>
                    <x-panel.button :href="route('admin.lessons.edit', $lesson->id)" variant="quiet">تعديل</x-panel.button>
                </div>
            </x-panel.card>
        @empty
            <x-panel.card class="panel-grid-full">
                <x-panel.empty title="لم يتم العثور على أي شرح" message="اضغط على زر الإضافة لإنشاء شرح." />
            </x-panel.card>
        @endforelse
    </div>
@endsection
