@extends('layouts.admin')
@section('content')
    <div class="flex flex-col justify-start items-start min-h-screen space-y-6">
        <div class="flex justify-between items-center gap-2">
            <x-btn.add route="admin.courses.create">إضافة دورة</x-btn.add>
            <a href="{{route('admin.live-sessions.create')}}"
                class = 'flex justify-center items-center text-white gap-1 bg-gradient-to-tr from-red-600 to-red-400 py-2 px-3 rounded-xl border-2 border-white overflow-hidden hover:scale-[1.03] transition-all duration-300 ease-in-out '>
                <p>إنشاء حصة مباشرة</p>
                <x-icons.live class="w-5 h-5 mr-2" />
            </a>
        </div>
        <div
            class="bg-white rounded-lg border border-gray-300 p-4 w-full grid lg:grid-cols-3 grid-cols-1 place-items-center gap-6">
            @forelse ($courses as $course)
                <x-card.admin-course :title="$course->title" :number_of_lessons="$course->content()->count()" :number_of_students="$course->students()->count()" :price="$course->price"
                    :course_id="$course->id" :published="$course->is_published" :type="$course->type" :content_type="$course->content_type" />
            @empty
                <x-card.empty-state title="لم يتم العثور على أي دورة" message="اضغط على زر الإضافة لإنشاء دورة"
                    :image="true" />
            @endforelse
        </div>
    </div>
@endsection()
