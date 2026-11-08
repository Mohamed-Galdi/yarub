@props(['course'])

<x-panel.learning-card :attributes="$attributes" :title="$course->title" :type="$course->type" :description="$course->description"
    :href="route('student.courses.show', ['course' => $course->id])">
    <x-slot:details>
        <div><dt>محتوى الدورة</dt><dd>{{ $course->content->count() }} <span>درس</span></dd></div>
        <div class="panel-learning-card__detail-text"><dt>تاريخ الانضمام</dt><dd>{{ $course->pivot->created_at->diffForHumans() }}</dd></div>
    </x-slot:details>
    <x-panel.button :href="route('student.courses.show', ['course' => $course->id])" variant="primary" :aria-label="'متابعة الدورة: '.$course->title">
        متابعة الدورة
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="M19 12H5m6-6-6 6 6 6"/></svg>
    </x-panel.button>
</x-panel.learning-card>
