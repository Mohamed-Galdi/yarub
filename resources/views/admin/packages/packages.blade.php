@extends('layouts.admin')
@section('content')
     <div class="flex flex-col justify-start items-start min-h-screen space-y-6">
        <div class="flex justify-between items-center gap-2">
            <x-btn.add route="admin.packages.create">إنشاء حقيبة </x-btn.add>
        </div>
        <div
            class="bg-white rounded-lg border border-gray-300 p-4 w-full grid lg:grid-cols-3 grid-cols-1 place-items-center gap-6">
            @forelse ($packages as $package)
                <div>
                    <x-card.admin-package :title="$package->title" :number_of_lessons="$package->lessons()->count()" :number_of_courses="$package->courses()->count()" :price="$package->price"
                        :course_id="$package->id" :published="$package->is_active" :type="$package->type" :content_type="$package->content_type" />
                </div>
            @empty
                <x-card.empty-state title="لم يتم العثور على أي حقيبة" message="اضغط على زر الإضافة لإنشاء حقيبة"
                    :image="true" />
            @endforelse
        </div>
    </div>
@endsection()