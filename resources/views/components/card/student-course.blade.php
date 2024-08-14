{{-- course props as array --}}
@props(['course'])

<div
    {{ $attributes->merge(['class' => 'w-full h-96 bg-gradient-to-tr from-indigo-800 to-indigo-400 rounded-xl shadow-lg p-4 space-y-4 overflow-hidden']) }}>
    <div class="w-full h-1/2 rounded-xl overflow-hidden">
        <x-card.course-img title="'{{ $course->title }}'" />
    </div>
    <div class="">
        <p class="text-white text-2xl truncate text-nowrap">{{ $course->title }}</p>
    </div>
    <div class="flex text-white justify-center gap-8 items-center ">
    
        <div class=" flex flex-col justify-center items-center w-1/3">
            <p class="text-yellow-300">تاريخ الإنضمام</p>
            <div class="flex gap-2 items-center justify-center">
                <p>{{$course->pivot->created_at->diffForHumans()}}</p>
            </div>
        </div>
        <div class="h-12 w-[2px] bg-white rounded-md"></div>
        <div class=" flex flex-col justify-center items-center w-1/3">
            <p class="text-yellow-300">الدورات</p>
            <div class="flex gap-2 items-center">
                <x-icons.video class="w-5 h-5 " />
                <p>5</p>
            </div>
        </div>

    </div>
    <div class="pt-1">
        <x-btn.enrol class="" route="admin.courses.edit" id="{{ $course->id }}">متابعة الدورة</x-btn.enrol>
    </div>
</div>
