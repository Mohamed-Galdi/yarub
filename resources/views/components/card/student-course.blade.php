@props(['course'])

<x-panel.card :attributes="$attributes->class(['panel-course'])">
    <header class="panel-course-heading">
        <h2 class="panel-card-title" dir="auto">{{ $course->title }}</h2>
        <p dir="auto">{{ $course->type }}</p>
    </header>
    <dl class="panel-course-stats panel-course-stats--pair">
        <div><dt>عدد الدروس</dt><dd>{{ $course->content->count() }}</dd></div>
        <div><dt>تاريخ الانضمام</dt><dd>{{ $course->pivot->created_at->diffForHumans() }}</dd></div>
    </dl>
    <div class="panel-actions">
        <x-panel.button :href="route('student.courses.show', ['course' => $course->id])" variant="primary">
            متابعة الدورة
        </x-panel.button>
    </div>
</x-panel.card>
