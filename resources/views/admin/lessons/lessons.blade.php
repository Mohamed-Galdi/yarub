@extends('layouts.admin')
@section('content')
    <div class="flex flex-col justify-start items-start min-h-screen space-y-6">

        <div>

            <x-btn.add route="admin.lessons.create">إضافة شرح</x-btn.add>
        </div>
        <div
            class="bg-white rounded-lg border border-gray-300 p-4 w-full grid lg:grid-cols-3 grid-cols-1 place-items-center gap-6">
            @forelse ($lessons as $lesson)
                <x-card.admin-lesson :title="$lesson->title" :numberOfLessons="$lesson->content()->count()" :numberOfStudents="$lesson->students()->count()" :monthly-price="$lesson->monthly_price" :annual_price="$lesson->annual_price"
                    :lessonId="$lesson->id" :published="$lesson->is_published" :type="$lesson->type" />
            @empty
                    <x-card.empty-state title="لم يتم العثور على أي شرح" message="اضغط على زر الإضافة لإنشاء شرح" :image="true" />
            @endforelse
        </div>
    </div>
@endsection()
