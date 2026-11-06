@extends('layouts.admin')
@section('panel-own-title', true)

@section('content')
    <div>
        <div class="flex justify-between">
            <h1 class="panel-page-title">{{ $lesson->title }}</h1>
            {{-- // back button --}}
            <x-btn.back route="admin.lessons" />
        </div>
        <dl class="panel-grid panel-grid--charts panel-section">
            <x-panel.metric label="مجموع المشتركين" :value="$lesson->students->count()" />
            <x-panel.metric label="مجموع المداخيل" :value="$lesson->students()->sum('student_lesson_sub.cost')" unit="ر.س" />
        </dl>
        {{-- students table --}}
        <div class="mt-8">
            <livewire:lesson-students-table :lessonId="$lesson->id" />
        </div>
    </div>
@endsection
