@extends('layouts.admin')
@section('panel-own-title', true)

@section('content')
    <div>
        <div class="flex justify-between">
            <h1 class="panel-page-title">{{ $course->title }}</h1>
            {{-- // back button --}}
            <x-btn.back route="admin.courses" />
        </div>
        <dl class="panel-grid panel-grid--charts panel-section">
            <x-panel.metric label="مجموع المشتركين" :value="$course->students->count()" />
            <x-panel.metric label="مجموع المداخيل" :value="$course->students()->sum('student_course_sub.cost')" unit="ر.س" />
        </dl>
        {{-- students table --}}
        <div class="mt-8">
            <livewire:course-students-table :courseId="$course->id" />
        </div>
    </div>
@endsection
