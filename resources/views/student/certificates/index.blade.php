@extends('layouts.student')
@section('content')
    <div class="w-full min-h-[calc(100vh-6rem)] bg-gradient-to-t from-gray-200 to-slate-200 ">
        <div class="max-w-screen-xl mx-auto  p-4 space-y-8">
            <h1 class="text-4xl mt-4 font-bold text-slate-600">الشواهد</h1>
            <div class="relative overflow-x-auto shadow-md sm:rounded-lg w-full">

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
                                       <span class="font-semibold text-black"> {{ $certificate->created_at->format('d/m/Y') }}</span> ({{ $certificate->created_at->diffForHumans() }})
                                    </p>
                                </td>
                                <td class="px-6 py-4 w-1/6">
                                    <a href="{{route('admin.certificates.view', ['id' => $certificate->id])}}" target="_blank">
                                        <div
                                            class="flex items-center justify-center gap-3 py-1 px-2 me-1 rounded-lg bg-indigo-400 text-gray-100 border border-gray-100 hover:bg-indigo-500 hover:text-white transition-all duration-300 ease-in-out cursor-pointer">
                                            <p> عرض </p>
                                            <x-icons.eye class="w-4 h-4" />
                                        </div>
                                    </a>
                                </td>
                                <td class="px-6 py-4 w-1/6">
                                    <a href="{{ route('admin.certificates.download', ['id' => $certificate->id])}}" target="_blank">
                                        <div
                                            class="flex items-center justify-center gap-3 py-1 px-2 me-1 rounded-lg bg-green-400 text-gray-100 border border-gray-100 hover:bg-green-500 hover:text-white transition-all duration-300 ease-in-out cursor-pointer">
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
