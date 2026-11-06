@extends('layouts.student')
@section('content')
    <div class="panel-student-content">
        <x-panel.heading title="الاختبارات" />
        <div class="panel-section space-y-4">
            <div class="panel-table-region relative overflow-x-auto shadow-md sm:rounded-lg w-full" tabindex="0" role="region" aria-label="جدول البيانات">

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
                            <tr
                                class="bg-slate-200 hover:bg-slate-300 transition-all duration-300 ease-in-out cursor-pointer">
                                <td class="px-6 py-4 w-3/12">
                                    <div class="flex items-center">
                                        <p class="text-nowrap truncate ">
                                            <span class="font-bold">{{ $test->course ? 'دورة' : 'شرح' }} : </span>
                                            {{ Str::words($test->course ? $test->course->title : ($test->lesson ? $test->lesson->title : 'N/A'), 7) }}
                                        </p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 w-3/12">
                                    <p class="text-nowrap truncate w-32">{{ Str::words($test->title, 5) }}</p>
                                </td>
                                <td class="px-6 py-4 w-1/12">
                                    <div class="panel-status">
                                        {{ $test->type }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 w-2/12">
                                    {{ $testAttempts->get($test->id) ? $testAttempts->get($test->id)->created_at->diffForHumans() : 'لم يتم الإجتياز بعد' }}
                                </td>
                                <td class="px-6 py-4 w-1/12">
                                    <div class="panel-status panel-status--published">
                                        {{ $testAttempts->get($test->id) ? $testAttempts->get($test->id)->score : '- -' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 w-2/12">
                                    @if (!$testAttempts->get($test->id))
                                        <a href="{{ route('student.tests.take', $test->id) }}"
                                            class="panel-button panel-button--secondary p-2 flex justify-center items-center gap-2 rounded-lg text-center transition-all duration-300 ease-in-out cursor-pointer">
                                            <p>إجراء </p>
                                            <x-icons.test class="w-5 h-5 text-white" />
                                        </a>
                                    @else
                                        <a href="{{ route('student.tests.result', $test) }}"
                                            class="panel-button panel-button--secondary p-2 flex justify-center items-center gap-2 rounded-lg text-center transition-all duration-300 ease-in-out cursor-pointer">
                                            <p>تفقد النتائج </p>
                                            <x-icons.eye class="w-5 h-5 text-white" />
                                        </a>
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
