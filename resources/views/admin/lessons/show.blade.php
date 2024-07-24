@extends('layouts.admin')

@section('content')
    <div>
        <h1 class="text-4xl text-gray-500 mb-6">{{ $lesson->title }}</h1>
        <div class="flex lg:flex-row flex-col justify-center items-center gap-6">
            <div
                class="lg:w-1/2 w-full flex px-8 py-3 justify-between items-center bg-gray-800 text-gray-200 text-2xl rounded-xl border border-indigo-500 ">
                <div>
                    <x-icons.students class="w-8 h-8 text-gray-50" />
                    <p>مجموع الطلاب</p>
                </div>
                <div>
                    <p>{{ $lesson->students->count() }}</p>
                </div>
            </div>
            <div
                class="lg:w-1/2 w-full flex px-8 py-3 justify-between items-center bg-gray-800 text-gray-200 text-2xl rounded-xl border border-indigo-500 ">
                <div>
                    <x-icons.payments class="w-8 h-8 text-gray-50" />
                    <p>مجموع المداخيل</p>
                </div>
                <div>
                    <p>{{ $lesson->students->count() }}</p>
                </div>
            </div>
        </div>
        {{-- students table --}}
        <div class="mt-8">
            <livewire:lesson-students-table :lessonId="$lesson->id" />
        </div>
    </div>
@endsection
