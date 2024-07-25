@extends('layouts.admin')
@section('content')
    <div class="flex flex-col justify-start items-start min-h-screen space-y-6">
        <div>
            <x-btn.add route="admin.courses.create">إضافة دورة</x-btn.add>
        </div>
        <div class="bg-white rounded-lg border border-gray-300 p-4 w-full grid lg:grid-cols-3 grid-cols-1 place-items-center gap-6">
            @forelse ($courses as $course)
                <x-card.admin-course :title="$course->title" :number_of_lessons="$course->content()->count()" :number_of_students="$course->students()->count()" :price="$course->price"
                    :course_id="$course->id" :published="$course->is_published" />
            @empty
                    <x-card.empty-state title="لم يتم العثور على أي دورة" message="اضغط على زر الإضافة لإنشاء دورة" :image="true" />
            @endforelse
        </div>
    </div>
@endsection()
