@extends('layouts.admin')
@section('panel-heading', true)

@section('content')
    <x-panel.heading title="الشروحات">
        <x-slot:actions>
            <x-panel.button :href="route('admin.lessons.create')" variant="primary">إضافة شرح</x-panel.button>
        </x-slot:actions>
    </x-panel.heading>

    <div class="panel-section panel-grid panel-learning-grid">
        @forelse ($lessons as $lesson)
            <x-panel.learning-card :title="$lesson->title" :type="$lesson->type" :description="$lesson->description"
                :href="route('admin.lessons.view', $lesson->id)" kind="lesson">
                <x-slot:status>
                    <span @class(['panel-learning-card__status', 'is-published' => $lesson->is_published])>
                        {{ $lesson->is_published ? 'منشور' : 'غير منشور' }}
                    </span>
                </x-slot:status>
                <x-slot:details>
                    <div><dt>الدروس</dt><dd>{{ $lesson->content()->count() }} <span>درس</span></dd></div>
                    <div><dt>المشتركون</dt><dd>{{ $lesson->students()->count() }} <span>مشترك</span></dd></div>
                </x-slot:details>
                <x-slot:pricing>
                    <div>
                        <span class="panel-learning-card__price-label">الاشتراك الشهري</span>
                        <p class="panel-learning-card__price"><bdi>{{ $lesson->monthly_price }}</bdi> <span>ر.س</span></p>
                    </div>
                    <div>
                        <span class="panel-learning-card__price-label">الاشتراك السنوي</span>
                        <p class="panel-learning-card__price"><bdi>{{ $lesson->annual_price }}</bdi> <span>ر.س</span></p>
                    </div>
                </x-slot:pricing>
                <x-panel.button :href="route('admin.lessons.view', $lesson->id)" :aria-label="'تصفح البيانات: '.$lesson->title">
                    تصفح البيانات
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="M19 12H5m6-6-6 6 6 6"/></svg>
                </x-panel.button>
                <x-panel.button :href="route('admin.lessons.edit', $lesson->id)" variant="quiet" :aria-label="'تعديل: '.$lesson->title">
                    <x-icons.edit aria-hidden="true" />
                    تعديل
                </x-panel.button>
            </x-panel.learning-card>
        @empty
            <x-panel.card class="panel-grid-full">
                <x-panel.empty title="لم يتم العثور على أي شرح" message="اضغط على زر الإضافة لإنشاء شرح." />
            </x-panel.card>
        @endforelse
    </div>
@endsection
