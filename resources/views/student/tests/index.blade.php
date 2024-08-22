@extends('layouts.student')
@section('content')
    <div class="w-full min-h-[calc(100vh-6rem)] bg-gradient-to-t from-gray-200 to-slate-200 ">
        <div class="max-w-screen-xl mx-auto  p-4 space-y-8">
            <h1 class="text-4xl mt-4 font-bold text-slate-600">اختبارات</h1>
            <div class="relative overflow-x-auto shadow-md sm:rounded-lg w-full">

                <table class="w-full text-left rtl:text-right text-gray-500">
                    <thead class="text-lg text-gray-700 uppercase bg-white w-full">
                        <tr>
                            <th scope="col" class="ps-8 py-3 w-3/12">عنوان الدورة/الشرح</th>
                            <th scope="col" class="px-6 py-3 w-3/12">عنوان الإختبار</th>
                            <th scope="col" class="ps-8 py-3 w-1/12">النوع</th>
                            <th scope="col" class="px-6 py-3 w-2/12 text-nowrap truncate">تاريخ الإجتياز</th>
                            <th scope="col" class="px-6 py-3 w-1/12">النتيجة</th>
                            <th scope="col" class="px-6 py-3 w-2/12">إجتياز الإختبار</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($availableTests as $test)
                            <tr class="bg-slate-200 hover:bg-slate-300 transition-all duration-300 ease-in-out cursor-pointer">
                                <td class="px-6 py-4 w-3/12">
                                    <div class="flex items-center">
                                        <p class="text-nowrap truncate ">
                                            <span class="font-bold">{{ $test->course ? 'دورة' : 'شرح' }} : </span>
                                            {{Str::words( $test->course ? $test->course->title : ($test->lesson ? $test->lesson->title : 'N/A'),7 )}}
                                        </p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 w-3/12">
                                    <p class="text-nowrap truncate w-32">{{Str::words( $test->title,5 )}}</p>
                                </td>
                                <td class="px-6 py-4 w-1/12">
                                    <div class="p-2 rounded-lg bg-slate-700 text-slate-200 text-center">
                                        {{$test->type}}
                                    </div>
                                </td>
                                <td class="px-6 py-4 w-2/12">
                                    {{ $testAttempts->get($test->id) ? $testAttempts->get($test->id)->created_at->diffForHumans() : 'لم يتم الإجتياز بعد' }}
                                </td>
                                <td class="px-6 py-4 w-1/12">
                                    <div class="p-2 rounded-lg bg-green-700 text-slate-200 text-center">
                                        {{ $testAttempts->get($test->id) ? $testAttempts->get($test->id)->score : '- -' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 w-2/12">
                                    @if (!$testAttempts->get($test->id))
                                        <a href="{{ route('student.tests.take', $test->id) }}"
                                            class="p-2 flex justify-center items-center gap-2 rounded-lg bg-indigo-400 hover:bg-indigo-500 text-white text-center transition-all duration-300 ease-in-out cursor-pointer">
                                            <p>إجراء </p>
                                            <x-icons.test class="w-5 h-5 text-white" />
                                        </a>
                                    @else
                                    <p class="text-center w-full">
                                        تم الإجتياز
                                    </p>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center">لا توجد إختبارات متاحة</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
@endsection()
