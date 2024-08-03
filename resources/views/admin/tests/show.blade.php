@extends('layouts.admin')
@section('content')
    <div class="flex justify-between">
        <h1 class="lg:text-4xl text-2xl text-nowrap truncate text-indigo-700 mb-4"><span class="text-gray-800">{{ $test->title }} <span
                    class="font-nitaqat text-gray-500 text-2xl">({{ $test->course_id ? ' الدرس : ' . $test->course->title : ' الشرح : ' . $test->lesson->title }})</span></span>
        </h1>
        {{-- // back button --}}
        <x-btn.back route="admin.tests" />
    </div>
    
    
    <div class="flex lg:flex-row flex-col justify-center items-center gap-6">
        <div
            class="lg:w-1/2 w-full flex px-8 py-3 justify-between items-center bg-gray-800 text-gray-200 text-2xl rounded-xl border border-indigo-500 ">
            <div>
                <x-icons.students class="w-8 h-8 text-gray-50" />
                <p>إجمالي الإجتيازات </p>
            </div>
            <div>
                {{-- <p>{{ $lesson->students->count() }}</p> --}}
                <p>{{ $test->attempts->count() }}</p>
            </div>
        </div>
        <div
            class="lg:w-1/2 w-full flex px-8 py-3 justify-between items-center bg-gray-800 text-gray-200 text-2xl rounded-xl border border-indigo-500 ">
            <div>
                <x-icons.test class="w-8 h-8 text-gray-50" />
                <p>معدل النتائج </p>
            </div>
            <div>
                <p>{{ $test->attempts->avg('score') }} %</p>
            </div>
        </div>
    </div>
    {{-- students table --}}
    <div class="mt-8">
        <livewire:test-attempts-table :testId="$test->id" />
    </div>
@endsection
