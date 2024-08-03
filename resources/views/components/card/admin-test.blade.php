<div
    {{ $attributes->merge(['class' => 'w-full h-fit bg-gradient-to-tr rounded-xl shadow-lg p-4 space-y-4 overflow-hidden' . ($published ? ' from-orange-600 to-orange-400' : ' from-gray-800 to-gray-400')]) }}>
    <div class="space-y-2">
        <div class="flex justify-start items-center gap-2">
            @if ($published)
                <x-card.live />
            @else
                <x-card.not-live />
            @endif
            <p class="text-white text-3xl text-nowrap truncate">{{ $title }}</p>
        </div>
        <p
            class="{{ 'py-[2px] px-1 ms-6 text-gray-100 rounded-lg me-2 font-judur text-nowrap truncate' . ($published ? ' bg-orange-800' : ' bg-gray-800') }}">
            {{ $courseTitle }} </p>
    </div>
    <div class="flex text-white justify-between items-center">
        <div class="border-l-2 border-white flex flex-col justify-center items-center w-1/2 gap-y-2">
            <p class="text-indigo-200">نوع الإختبار</p>
            <div class="flex gap-2 items-center">
                <p class="py-1 px-2 bg-gray-100 text-gray-800 rounded-lg me-2 font-judur text-sm">{{ $type }}</p>
            </div>
        </div>
        <div class=" flex flex-col justify-center items-center w-1/2 gap-y-2">
            <p class="text-indigo-200">عداد الإجتيازات</p>
            <div class="flex gap-2 items-center justify-center">
                <x-icons.user class="w-5 h-5 " />
                <p>{{ $attemptsCount }}</p>
            </div>
        </div>


    </div>
    <div class="pt-3 flex justify-center items-center gap-2">
        <x-btn.view class="w-1/2 text-nowrap truncate" route="admin.tests.view" id="{{ $testId }}">تصفح البيانات</x-btn.view>
        <x-btn.edit class="w-1/2" route="admin.tests.edit" id="{{ $testId }}">تعديل</x-btn.edit>
    </div>
</div>
