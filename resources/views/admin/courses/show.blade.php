@extends('layouts.admin')

@section('content')
    <div>
        <h1 class="text-4xl text-gray-500 mb-6">{{ $course->title }}</h1>
        <div class="flex justify-center items-center gap-6">
            <div
                class="w-1/2 flex px-8 py-3 justify-between items-center bg-gray-800 text-gray-200 text-2xl rounded-xl border border-indigo-500 ">
                <div>
                    <x-icons.students class="w-8 h-8 text-gray-50" />
                    <p>مجموع الطلاب</p>
                </div>
                <div>
                    <p>{{ $course->students->count() }}</p>
                </div>
            </div>
            <div
                class="w-1/2 flex px-8 py-3 justify-between items-center bg-gray-800 text-gray-200 text-2xl rounded-xl border border-indigo-500 ">
                <div>
                    <x-icons.payments class="w-8 h-8 text-gray-50" />
                    <p>مجموع المداخيل</p>
                </div>
                <div>
                    <p>{{ $course->students->count() }}</p>
                </div>
            </div>
        </div>
        {{-- students table --}}
        <div class="mt-6">
            <livewire:course-students-table :courseId="$course->id" />
        </div>
    </div>
@endsection
