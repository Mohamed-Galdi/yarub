@extends('layouts.admin')
@section('content')
    <div class="space-y-8 ">
        <div class="flex justify-between">
            <h1 class="lg:text-4xl text-2xl text-nowrap truncate text-indigo-700">المعلومات الشخصية</h1>
            {{-- // back button --}}
            <x-btn.back route="admin.students" />
        </div>

        {{-- //////////////////// Student Info //////////////////// --}}
        <div
            class=" w-full flex flex-col lg:flex-row   bg-white rounded-xl  h-fit border border-gray-800 shadow-md shadow-blue-500/50">
            {{-- //////////////// Info //////////////// --}}
            <div class="lg:w-1/2 w-full py-2 flex flex-col lg:flex-row justify-start items-center">
                <img src="{{ asset($student->avatar ?? 'storage/users_avatars/default.png') }}" alt=""
                    class="w-44 h-44 rounded-full ms-4">
                <div
                    class="w-full border-r-3 border-gray-600 px-4 py-6 h-full flex flex-col justify-between lg:items-start items-center ">
                    <div class="text-center lg:text-right">
                        <p class="font-hacen text-3xl text-gray-800">{{ $student->name }}</p>
                        <p class="font-judur text-sm text-gray-400 ">{{ $student->email }}</p>
                    </div>
                    <div class="flex flex-row lg:flex-col justify-start items-start lg:items-center lg:gap-0 gap-2 ">
                        <div class="flex items-start justify-start gap-x-1">
                            <x-icons.calendar class="w-5 h-5 text-gray-800 " />
                            <p class="text-base text-gray-500 font-judur mb-2 ">تاريخ الانضمام : </p>
                        </div>
                        <div class="flex text-lg gap-1 text-indigo-400">
                            <p class=" ">{{ $student->created_at->format('d-m-Y') }}</p>
                            <p class=" ">({{ $student->created_at->diffForHumans() }})</p>
                        </div>
                    </div>
                </div>

            </div>
            {{-- //////////////// Badges //////////////// --}}
            <div class="lg:w-1/2 w-full grid grid-cols-2 gap-4 p-6 align-middle place-items-center">
                <div
                    class="w-[90%] p-3 rounded-lg h-20 bg-gradient-to-tr from-blue-400 to-blue-500 flex flex-col justify-center items-center">
                    <div class="flex justify-center items-center gap-3">
                        <p class="text-xl text-white">الدورات</p>
                        <x-icons.course class="w-5 h-5 text-white" />
                    </div>
                    <p class="text-3xl text-white">{{ $student->activeCourses->count() }}</p>
                </div>
                <div
                    class="w-[90%] p-3 rounded-lg h-20 bg-gradient-to-tr from-red-400 to-red-500 flex flex-col justify-center items-center">
                    <div class="flex justify-center items-center gap-3">
                        <p class="text-xl text-white">الشروحات</p>
                        <x-icons.lesson class="w-5 h-5 text-white" />
                    </div>
                    <p class="text-3xl text-white">{{ $student->activeLessons->count() }}</p>
                </div>
                <div
                    class="w-[90%] p-3 rounded-lg h-20 bg-gradient-to-tr from-green-400 to-green-500 flex flex-col justify-center items-center">
                    <div class="flex justify-center items-center gap-3">
                        <p class="text-xl text-white">الإختبارات</p>
                        <x-icons.test class="w-5 h-5 text-white" />
                    </div>
                    <p class="text-3xl text-white">{{ $student->test_attempts()->count() }}</p>
                </div>
                <div
                    class="w-[90%] p-3 rounded-lg h-20 bg-gradient-to-tr from-yellow-400 to-yellow-500 flex flex-col justify-center items-center">
                    <div class="flex justify-center items-center gap-3">
                        <p class="text-xl text-white">الشهادات</p>
                        <x-icons.certificate class="w-5 h-5 text-white" />
                    </div>
                    <p class="text-3xl text-white">{{ $student->certificates()->count() }}</p>
                </div>
            </div>


        </div>

        {{-- //////////////////// Student Items //////////////////// --}}
        <div
            class=" w-full bg-white rounded-xl md:min-h-screen h-fit px-6 py-4 border border-gray-800 shadow-md shadow-blue-500/50">

            <div class=" ">
                {{-- ///////////////////////// Tabs /////////////////////// --}}
                <div class="w-full">
                    <x-dynamic-tab-navigation :tabs="[
                        [
                            'id' => 'courses',
                            'label' => 'الدورات',
                            'icon' => 'icons.course',
                            'count' => $student->activeCourses->count(),
                        ],
                        [
                            'id' => 'lessons',
                            'label' => 'الشروحات',
                            'icon' => 'icons.lesson',
                            'count' => $student->activeLessons->count(),
                        ],
                        [
                            'id' => 'tests',
                            'label' => 'الإختبارات',
                            'icon' => 'icons.test',
                            'count' => $student->test_attempts->count(),
                        ],
                        [
                            'id' => 'certificates',
                            'label' => 'الشهادات',
                            'icon' => 'icons.certificate',
                            'count' => $student->certificates->count(),
                        ],
                    ]">
                        <x-slot name="courses">
                            <div class="flex mt-4 gap-4">
                                @if ($student->activeCourses->count() > 0)
                                    <table class="w-full text-left rtl:text-right text-gray-500 ">
                                        <thead class="text-lg  text-gray-100 uppercase bg-indigo-500 w-full">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 w-6/12">
                                                    عنوان الدورة
                                                </th>
                                                <th scope="col" class="px-6 py-3 w-3/12 text-nowrap truncate">
                                                    تاريخ الإنضمام
                                                </th>
                                                <th scope="col" class="px-6 py-3 w-3/12 text-nowrap truncate">
                                                    مبلغ الإشتراك
                                                </th>

                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($student->activeCourses as $course)
                                                <tr
                                                    class="border-b cursor-pointer  w-full text-slate-800 transition-all ease-in-out duration-00 bg-slate-200 hover:bg-slate-300">

                                                    <td class="px-6 py-4">
                                                        <div class="flex items-center">
                                                            <p class=" text-nowrap truncate w-full">
                                                                {{ $course->title }}</p>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        <div class="flex items-center">
                                                            <p class=" text-nowrap truncate w-full">
                                                                {{ $course->pivot->created_at->format('d-m-Y') }}</p>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        <div class="flex items-center">
                                                            <p class=" text-nowrap truncate w-full">
                                                                {{ $course->pivot->cost }} ريال</p>
                                                        </div>
                                                    </td>

                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <x-card.empty-state title="لا توجد دورات بعد لدى هذا الطالب" message=""
                                        :image="true" class="w-1/2 h-auto mx-auto" />
                                @endif
                            </div>
                        </x-slot>

                        <x-slot name="lessons">
                            <div class="mt-4 space-y-6">
                                @if ($student->activeCourses->count() > 0)
                                    <table class="w-full text-left rtl:text-right text-gray-500 ">
                                        <thead class="text-lg  text-gray-100 uppercase bg-indigo-500 w-full">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 w-2/6">
                                                    عنوان الشرح
                                                </th>
                                                <th scope="col" class="px-6 py-3 w-1/6">
                                                    الإشتراك
                                                </th>
                                                <th scope="col" class="px-6 py-3 w-1/6 text-nowrap truncate">
                                                    تاريخ الإنضمام
                                                </th>
                                                <th scope="col" class="px-6 py-3 w-1/6 text-nowrap truncate">
                                                    تاريخ الإنتهاء
                                                </th>
                                                <th scope="col" class="px-6 py-3 w-1/6 text-nowrap truncate">
                                                    مبلغ الإشتراك
                                                </th>

                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($student->activeLessons as $lesson)
                                                <tr
                                                    class="border-b cursor-pointer  w-full text-slate-800 transition-all ease-in-out duration-00 bg-slate-200 hover:bg-slate-300">

                                                    <td class="px-6 py-4">
                                                        <div class="flex items-center">
                                                            <p class=" text-nowrap truncate w-full">
                                                                {{ $lesson->title }}</p>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        <div class="flex items-center">
                                                            <p class=" text-nowrap truncate w-full">
                                                                {{ $lesson->pivot->sub_plan == 'monthly' ? 'شهري' : 'سنوي' }}
                                                            </p>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        <div class="flex items-center">
                                                            <p class=" text-nowrap truncate w-full">
                                                                {{ $lesson->pivot->created_at->format('d-m-Y') }}</p>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        <div class="flex items-center">
                                                            <p class=" text-nowrap truncate w-full">
                                                                {{ $lesson->pivot->sub_plan == 'monthly' ? $lesson->pivot->created_at->addMonth()->format('d-m-Y') : $lesson->pivot->created_at->addYear()->format('d-m-Y') }}
                                                            </p>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        <div class="flex items-center">
                                                            <p class=" text-nowrap truncate w-full">
                                                                {{ $lesson->pivot->cost }} ريال</p>
                                                        </div>
                                                    </td>

                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <x-card.empty-state title="لا توجد شروحات بعد لدى هذا الطالب" message=""
                                        :image="true" class="w-1/2 h-auto mx-auto" />
                                @endif
                            </div>
                        </x-slot>

                        <x-slot name="tests">
                            <div class="mt-4 space-y-6">
                                @if ($student->test_attempts->count() > 0)
                                    <table class="w-full text-left rtl:text-right text-gray-500 ">
                                        <thead class="text-lg  text-gray-100 uppercase bg-indigo-500 w-full">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 w-2/6">
                                                    عنوان الإختبار
                                                </th>
                                                <th scope="col" class="px-6 py-3 w-2/6">
                                                    الدورة/الشرح
                                                </th>
                                                <th scope="col" class="px-6 py-3 w-1/6 text-nowrap truncate">
                                                    النتيجة </th>
                                                <th scope="col" class="px-6 py-3 w-1/6 text-nowrap truncate">
                                                    تاريخ الإجتياز
                                                </th>

                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($student->test_attempts as $test_attempt)
                                                <tr
                                                    class="border-b cursor-pointer  w-full text-slate-800 transition-all ease-in-out duration-00 bg-slate-200 hover:bg-slate-300">

                                                    <td class="px-6 py-4">
                                                        <div class="flex items-center">
                                                            <p class=" text-nowrap truncate w-full">
                                                                {{ $test_attempt->test->title }}</p>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        <div class="flex items-center">
                                                            <p class=" text-nowrap truncate w-full">
                                                                {{ $test_attempt->test->course_id ? $test_attempt->test->course->title : $test_attempt->test->lesson->title }}
                                                            </p>
                                                            </p>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        <div class="flex items-center">
                                                            <p class=" text-nowrap truncate w-full">
                                                                {{ $test_attempt->score }} % </p>
                                                        </div>
                                                    </td>

                                                    <td class="px-6 py-4">
                                                        <div class="flex items-center">
                                                            <p class=" text-nowrap truncate w-full">
                                                                {{ $test_attempt->created_at->format('d-m-Y') }}</p>
                                                        </div>
                                                    </td>

                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <x-card.empty-state title="لا توجد إختبارات بعد لدى هذا الطالب" message=""
                                        :image="true" class="w-1/2 h-auto mx-auto" />
                                @endif
                            </div>
                        </x-slot>

                        <x-slot name="certificates">
                            <div class="mt-4 space-y-6">
                                @if ($student->certificates->count() > 0)
                                    <table class="w-full text-left rtl:text-right text-gray-500 ">
                                        <thead class="text-lg  text-gray-100 uppercase bg-indigo-500 w-full">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 w-2/6">
                                                    الدورة/الشرح
                                                </th>
                                                <th scope="col" class="px-6 py-3 w-2/6">
                                                    تاريخ المنح
                                                </th>
                                                <th scope="col" class="px-6 py-3 w-1/6 text-nowrap truncate">
                                                    الإطلاع
                                                </th>

                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($student->certificates as $certificate)
                                                <tr
                                                    class="border-b cursor-pointer  w-full text-slate-800 transition-all ease-in-out duration-00 bg-slate-200 hover:bg-slate-300">


                                                    <td class="px-6 py-4">
                                                        <div class="flex items-center">
                                                            <p class=" text-nowrap truncate w-full">
                                                                {{ $certificate->course_id ? $certificate->course->title : $certificate->lesson->title }}
                                                            </p>
                                                            </p>
                                                        </div>
                                                    </td>


                                                    <td class="px-6 py-4">
                                                        <div class="flex items-center">
                                                            <p class=" text-nowrap truncate w-full">
                                                                {{ $certificate->created_at->format('d-m-Y') }}</p>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        <div class="flex items-center">
                                                            <a href="{{ route('admin.certificates.view', ['id' => $certificate->id]) }}"
                                                                target="_blank">
                                                                <div
                                                                    class="flex items-center gap-3 py-1 px-2 me-1 rounded-lg bg-indigo-400 text-gray-100 border border-gray-100 hover:bg-indigo-500 hover:text-white transition-all duration-300 ease-in-out cursor-pointer">
                                                                    <p> عرض </p>
                                                                    <x-icons.eye class="w-4 h-4" />
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </td>

                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <x-card.empty-state title="لا توجد شواهد بعد لدى هذا الطالب" message=""
                                        :image="true" class="w-1/2 h-auto mx-auto" />
                                @endif
                            </div>
                        </x-slot>

                    </x-dynamic-tab-navigation>
                </div>
                {{-- /////////////////////////////////////////////////////// --}}
            </div>
        </div>


    </div>
@endsection()
