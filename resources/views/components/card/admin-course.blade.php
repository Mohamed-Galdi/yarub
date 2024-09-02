<div
    {{ $attributes->merge(['class' => 'w-full h-fit bg-gradient-to-tr rounded-xl shadow-lg p-4 space-y-4' . ($published ? ' from-indigo-800 to-indigo-400' : ' from-gray-800 to-gray-400')]) }}>
    <div class="w-full h-44 rounded-xl overflow-hidden">
        <x-card.course-img title="'{{ $title }}'" />
    </div>
    <div>
        <div class="flex justify-start items-center gap-2">
            @if ($published)
                <x-card.live />
            @else
                <x-card.not-live />
            @endif
            <p class="text-white text-nowrap truncate">{{ $title }}</p>
        </div>
        <div class="flex justify-start items-center gap-2">
            @if ($contentType === 'live_session')
            <p class=" ms-4 text-slate-100  px-2 bg-red-500 rounded-xl w-fit mt-1">حصة مباشرة</p>
            @else
            <p class=" ms-4 text-slate-900 px-1 bg-slate-300 rounded-xl w-fit mt-1">{{ $type }}</p>
            @endif
        </div>

    </div>

    <div class="flex text-white justify-between items-center">
        @if ($contentType === 'video')
            <div class="border-l-2 border-white flex flex-col justify-center items-center w-1/3">
                <p class="text-yellow-300">الدروس</p>
                <div class="flex gap-2 items-center">
                    <x-icons.video class="w-5 h-5 " />
                    <p>{{ $numberOfLessons }}</p>
                </div>
            </div>
        @else
            <div class="border-l-2 border-white flex flex-col justify-center items-center w-1/3">
                <p class="text-yellow-300">حصة مباشرة</p>
                <div class="flex gap-2 items-center">
                    <x-icons.live class="w-5 h-5" />

                </div>
            </div>
        @endif
        <div class="border-l-2 border-white flex flex-col justify-center items-center w-1/3">
            <p class="text-yellow-300">المشتركين</p>
            <div class="flex gap-2 items-center justify-center">
                <x-icons.user class="w-5 h-5 " />
                <p>{{ $numberOfStudents }}</p>
            </div>
        </div>
        <div class=" flex flex-col justify-center items-center w-1/3">
            <p class="text-yellow-300">المبلغ</p>
            <div class="flex gap-2 items-center justify-center">
                {{-- <x-icons.money-bag class="w-5 h-5 " /> --}}
                <p class="text-no-wrap truncate">{{ $price }} رس</p>
            </div>
        </div>

    </div>
    <div class="pt-3 flex justify-center items-center gap-2">
        <x-btn.view class="w-1/2 text-nowrap truncate" route="admin.courses.view" id="{{ $courseId }}">تصفح
            البيانات</x-btn.view>
        @if ($contentType === 'video')
        <x-btn.edit class="w-1/2" route="admin.courses.edit" id="{{ $courseId }}">تعديل</x-btn.edit>
        @else
        <x-btn.edit class="w-1/2" route="admin.live-session.edit" id="{{ $courseId }}">تعديل</x-btn.edit>
        @endif
    </div>
</div>
