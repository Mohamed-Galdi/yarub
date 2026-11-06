@extends('layouts.student')
@section('content')
    <div class="panel-student-content">
        <x-panel.heading title="الشهادات" />
        <div class="panel-section space-y-4">
            <div class="panel-table-region relative overflow-x-auto shadow-md sm:rounded-lg w-full" tabindex="0" role="region" aria-label="جدول البيانات">

                <table class="w-full text-left rtl:text-right text-gray-500">
                    <thead class="text-lg text-gray-700 uppercase bg-white w-full">
                        <tr>
                            <th scope="col" class="ps-8 py-3 w-2/6">عنوان الدورة/الشرح</th>
                            <th scope="col" class="px-6 py-3 w-2/6">تاريخ المنح</th>
                            <th scope="col" class="ps-8 py-3 w-1/6">العرض</th>
                            <th scope="col" class="px-6 py-3 w-1/6">التحميل</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($certificates as $certificate)
                            <tr
                                class="bg-slate-200 hover:bg-slate-300 transition-all duration-300 ease-in-out cursor-pointer">
                                <td class="px-6 py-4 w-2/6">
                                    <div class="flex items-center">
                                        <p class="text-nowrap truncate ">
                                            <span class="font-bold">{{ $certificate->course ? 'دورة' : 'شرح' }} : </span>
                                            {{ Str::words($certificate->course ? $certificate->course->title : ($certificate->lesson ? $certificate->lesson->title : 'N/A'), 7) }}
                                        </p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 w-2/6">
                                    <p class="text-nowrap truncate">
                                        <span class="font-semibold text-black">
                                            {{ $certificate->created_at->format('d/m/Y') }}</span>
                                        ({{ $certificate->created_at->diffForHumans() }})
                                    </p>
                                </td>
                                <td class="px-6 py-4 w-1/6">
                                    <a href="{{ route('admin.certificates.view', ['id' => $certificate->id]) }}"
                                        target="_blank">
                                        <div
                                            class="panel-button panel-button--secondary">
                                            <p> عرض </p>
                                            <x-icons.eye class="w-4 h-4" />
                                        </div>
                                    </a>
                                </td>
                                <td class="px-6 py-4 w-1/6">
                                    <a href="{{ route('admin.certificates.download', ['id' => $certificate->id]) }}"
                                        target="_blank">
                                        <div
                                            class="panel-button panel-button--secondary">
                                            <p> تنزيل </p>
                                            <x-icons.download class="w-4 h-4" />
                                        </div>
                                    </a>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center">لا توجد لديك شواهد بعد !</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
@endsection()
