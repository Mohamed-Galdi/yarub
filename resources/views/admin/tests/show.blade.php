@extends('layouts.admin')
@section('panel-own-title', true)
@section('content')
    <div class="flex justify-between">
        <h1 class="panel-page-title"><span
                class="text-gray-800">{{ $test->title }} <span
                    class="font-nitaqat text-gray-500 text-2xl">({{ $test->course_id ? ' الدورة : ' . $test->course->title : ' الشرح : ' . $test->lesson->title }})</span></span>
        </h1>
        {{-- // back button --}}
        <x-btn.back route="admin.tests" />
    </div>


    <dl class="panel-grid panel-grid--charts panel-section">
        <x-panel.metric label="إجمالي الاجتيازات" :value="$test->attempts->count()" />
        <x-panel.metric label="معدل النتائج" :value="$test->attempts->avg('score')" unit="%" />
    </dl>
    {{-- students table --}}
    <div class="mt-8">
        <livewire:test-attempts-table :testId="$test->id" />
    </div>
@endsection
