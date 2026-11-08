@props(['lesson'])
@php($subscription = Auth::user()->getLessonsSubRemainingDays($lesson))

<x-panel.learning-card :attributes="$attributes" :title="$lesson->title" :type="$lesson->type" :description="$lesson->description"
    :href="route('student.lessons.show', ['lesson' => $lesson->id])" kind="lesson">
    <x-slot:details>
        <div><dt>محتوى الشرح</dt><dd>{{ $lesson->content->count() }} <span>درس</span></dd></div>
        <div @class(['is-expiring' => $subscription['is_expiring_soon']])>
            <dt>الاشتراك</dt>
            <dd>{{ $subscription['days'] }} <span>يوم متبقي</span></dd>
        </div>
    </x-slot:details>
    <x-panel.button :href="route('student.lessons.show', ['lesson' => $lesson->id])" variant="primary" :aria-label="'متابعة الشرح: '.$lesson->title">
        متابعة الشرح
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="M19 12H5m6-6-6 6 6 6"/></svg>
    </x-panel.button>
</x-panel.learning-card>
