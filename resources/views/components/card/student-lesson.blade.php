{{-- lesson props as array --}}
@props(['lesson'])
@php
    $subscription = Auth::user()->getLessonsSubRemainingDays($lesson);
@endphp
<div
    {{ $attributes->merge(['class' => 'w-full min-h-96 bg-gradient-to-tr from-teal-800 to-teal-400 rounded-xl shadow-lg p-4 space-y-4 overflow-hidden border-2 border-white']) }}>
    <div class="w-full h-44 rounded-xl overflow-hidden">
        <x-card.course-img title="'{{ $lesson->title }}'" />
    </div>
    <div class="">
        <p class="text-white text-2xl truncate text-nowrap">{{ $lesson->title }}</p>
        <p class=" ms-4 text-slate-900 px-1 bg-slate-300 rounded-xl w-fit mt-1">{{ Str::words($lesson->type, 3) }}</p>

    </div>
    <div class="flex text-white justify-center gap-8 items-center ">
        <div class=" flex flex-col justify-center items-center w-1/3">
            <p class="text-yellow-300">عدد الدروس</p>
            <div class="flex gap-2 items-center">
                <p>{{ $lesson->content->count() }}</p>
                <x-icons.video class="w-5 h-5 " />
            </div>
        </div>
        <div class="h-12 w-[2px] bg-white rounded-md"></div>
        <div class=" flex flex-col justify-center items-center w-1/3">
            <p class="text-yellow-300 text-nowrap truncate">الإشتراك </p>
            <div class="flex gap-2 items-center justify-center">
                <p class="text-nowrap truncate {{ $subscription['is_expiring_soon'] ? 'text-red-500' : '' }}">
                    {{ $subscription['days'] }} يوم متبقي
                </p>
            </div>
        </div>


    </div>
    <div class="pt-1">
        <x-btn.enrol class="" route="student.lessons.show" id="{{ $lesson->id }}" type="lesson">متابعة
            الدورة</x-btn.enrol>
    </div>
</div>
