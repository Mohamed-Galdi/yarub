{{-- course props as array --}}
@props(['course'])

<div
    {{ $attributes->merge(['class' => 'w-full min-h-96 bg-gradient-to-tr from-indigo-800 to-indigo-400 rounded-xl shadow-lg p-4 space-y-4 overflow-hidden border-2 border-white']) }}>
    <div class="w-full h-44 rounded-xl overflow-hidden">
        <x-card.course-img title="'{{ $course->title }}'" />
    </div>
    <div class="">
        <p class="text-white text-2xl truncate text-nowrap">{{ $course->title }}</p>
        @if ($course->content_type == 'live_session')
            <div
                class="flex justify-start items-center gap-2 text-slate-100 px-1 text-sm py-1  bg-red-500 rounded-xl w-fit">
                <p>حصة مباشرة</p>
                <x-icons.live class="w-5 h-5 ml-2" />
            </div>
        @else
            <p class=" ms-4 text-slate-900 px-1 bg-slate-300 rounded-xl w-fit mt-1">{{ Str::words($course->type, 3) }}
            </p>
        @endif

    </div>
    @if ($course->content_type === 'video')
        <div class="flex text-white justify-center gap-8 items-center ">
            <div class=" flex flex-col justify-center items-center w-1/3">
                <p class="text-yellow-300">عدد الدروس</p>
                <div class="flex gap-2 items-center">
                    <p>{{ $course->content->count() }}</p>
                    <x-icons.video class="w-5 h-5 " />
                </div>
            </div>
            <div class="h-12 w-[2px] bg-white rounded-md"></div>

            <div class=" flex flex-col justify-center items-center w-1/3">
                <p class="text-yellow-300 text-nowrap truncate">تاريخ الإنضمام</p>
                <div class="flex gap-2 items-center justify-center">
                    <p class="text-nowrap truncate">{{ $course->pivot->created_at->diffForHumans() }}</p>
                </div>
            </div>
        </div>
    @else
        <div class="w-full ">
            <p class="text-yellow-300 text-nowrap truncate">تاريخ البث: </p>
            <div class="flex justify-start gap-2 text-slate-100">
                <p class="text-nowrap truncate" dir="ltr"> /
                    {{ Carbon\Carbon::parse($course->liveSession->start_time)->format('d-m-Y H:i') }}
                <p class="text-nowrap truncate" dir="rtl">
                    {{ Carbon\Carbon::parse($course->liveSession->start_time)->diffForHumans() }}
                </p>

            </div>
        </div>
    @endif

    @if ($course->content_type === 'video')
        <div class="pt-1">
            <x-btn.enrol class="" route="student.courses.show" id="{{ $course->id }}" type="course">متابعة
                الدورة</x-btn.enrol>
        </div>
    @else
        <div class="pt-1">
            <x-btn.enrol class="" route="student.courses.showLiveSession" id="{{ $course->id }}" type="course">
                إنضم للحصة المباشرة</x-btn.enrol>
        </div>
    @endif
</div>
