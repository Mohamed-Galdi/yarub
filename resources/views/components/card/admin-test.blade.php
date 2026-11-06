@props(['published', 'testId', 'title', 'attemptsCount', 'type', 'courseTitle'])

<x-panel.card :attributes="$attributes->class(['panel-course'])">
    <header class="panel-course-heading">
        <span @class(['panel-status', 'panel-status--published' => $published])>
            {{ $published ? 'منشور' : 'غير منشور' }}
        </span>
        <h2 class="panel-card-title" dir="auto">{{ $title }}</h2>
        <p dir="auto">{{ $courseTitle }}</p>
    </header>
    <dl class="panel-course-stats panel-course-stats--pair">
        <div><dt>نوع الاختبار</dt><dd>{{ $type }}</dd></div>
        <div><dt>عدد المحاولات</dt><dd>{{ $attemptsCount }}</dd></div>
    </dl>
    <div class="panel-actions">
        <x-panel.button :href="route('admin.tests.view', $testId)">تصفح البيانات</x-panel.button>
        <x-panel.button :href="route('admin.tests.edit', $testId)" variant="quiet">تعديل</x-panel.button>
    </div>
</x-panel.card>
