@extends('layouts.admin')

@section('content')
    <div>
        <div class="flex justify-between">
            <h1 class="lg:text-4xl text-2xl text-nowrap truncate text-gray-500 mb-6">{{ $course->title }}</h1>
            </h1>
            {{-- // back button --}}
            <x-btn.back route="admin.courses" />
        </div>
        <div class="flex lg:flex-row flex-col justify-center items-center gap-6">
            <div
                class="lg:w-1/2 w-full flex px-8 py-3 justify-between items-center bg-gray-800 text-gray-200 text-2xl rounded-xl border border-indigo-500 ">
                <div>
                    <x-icons.students class="w-8 h-8 text-gray-50" />
                    <p>مجموع الطلاب</p>
                </div>
                <div>
                    <p class="font-hacen text-3xl">{{ $course->students->count() }}</p>
                </div>
            </div>
            <div
                class="lg:w-1/2 w-full flex px-8 py-3 justify-between items-center bg-gray-800 text-gray-200 text-2xl rounded-xl border border-indigo-500 ">
                <div>
                    <x-icons.payments class="w-8 h-8 text-gray-50" />
                    <p>مجموع المداخيل</p>
                </div>
                <div class="">
                    <p class="font-hacen text-3xl">{{ $course->students()->sum('student_course_sub.cost') }} </p>
                    <p class="font-judur text-geay-700 text-sm">ريال سعودي</p>
                </div>
            </div>
        </div>
        {{-- students table --}}
        <div class="mt-8">
            <livewire:course-students-table :courseId="$course->id" />
        </div>
    </div>
@endsection
