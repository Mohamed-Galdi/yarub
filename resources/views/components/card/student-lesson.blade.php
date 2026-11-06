@props(['lesson'])
@php($subscription = Auth::user()->getLessonsSubRemainingDays($lesson))

<x-panel.card :attributes="$attributes->class(['panel-course'])">
    <header class="panel-course-heading">
        <h2 class="panel-card-title" dir="auto">{{ $lesson->title }}</h2>
        <p dir="auto">{{ $lesson->type }}</p>
    </header>
    <dl class="panel-course-stats panel-course-stats--pair">
        <div><dt>عدد الدروس</dt><dd>{{ $lesson->content->count() }}</dd></div>
        <div>
            <dt>الاشتراك</dt>
            <dd @class(['panel-status panel-status--warning' => $subscription['is_expiring_soon']])>
                {{ $subscription['days'] }} يوم متبقي
            </dd>
        </div>
    </dl>
    <div class="panel-actions">
        <x-panel.button :href="route('student.lessons.show', ['lesson' => $lesson->id])" variant="primary">
            متابعة الشرح
        </x-panel.button>
    </div>
</x-panel.card>
