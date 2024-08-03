@extends('layouts.admin')

@section('content')
    <div class="container">
        {{-- <x-form.errors :errors="$errors" /> --}}
        <div class="flex justify-between">
            <h1 class="lg:text-4xl text-2xl text-nowrap truncate text-indigo-700 mb-4">إنشاء محادثة جديدة</h1>
            {{-- // back button --}}
            <x-btn.back route="admin.conversations.index" />
        </div>
        <form action="{{ route('admin.conversations.store') }}" method="POST">
            @csrf
            <div class="mb-6">
                <label for="" class="block text-lg font-medium text-gray-700 mb-2">اختر الطلاب</label>
                <button id="dropdownSearchButton" data-dropdown-toggle="dropdownSearch"
                    class="flex justify-center items-center gap-2 w-full h-[3.1rem] rounded-md bg-slate-400 hover:bg-slate-500 transition-colors ease-in-out duration-200 text-white shadow-sm"
                    type="button">
                    <p>تحديد الطلاب</p>
                    <x-icons.arrow-down class="w-5 h-5 ml-2 text-gray-50" />
                </button>
                <!-- Dropdown menu -->
                <div id="dropdownSearch" class="z-10 hidden bg-white rounded-lg shadow w-1/2 mt-2">
                    <div class="p-3">
                        <div class="relative">
                            <input type="text" id="searchInput"
                                class="w-full p-2 pl-8 rounded-md border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                placeholder="بحث عن طالب...">
                            <svg class="absolute left-2 top-2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <ul class="h-48 px-3 pb-3 overflow-y-auto text-sm text-gray-700" aria-labelledby="dropdownSearchButton">
                        <li>
                            <div class="flex items-center p-2 rounded hover:bg-slate-300 bg-slate-200">
                                <input id="checkbox-all" type="checkbox" value="all" name="students[]"
                                    class="w-4 h-4 text-indigo-600 bg-gray-100 rounded border-gray-300 focus:ring-indigo-500">
                                <label for="checkbox-all" class="w-full ms-2 text-lg font-medium text-indigo-900  ">جميع
                                    الطلاب</label>
                            </div>
                        </li>
                        @foreach ($students as $student)
                            <li>
                                <div class="flex items-center p-2 rounded hover:bg-gray-100">
                                    <input id="checkbox-student-{{ $student->id }}" type="checkbox"
                                        value="{{ $student->id }}" name="students[]"
                                        class="w-4 h-4 text-indigo-600 bg-gray-100 rounded border-gray-300 focus:ring-indigo-500">
                                    <label for="checkbox-student-{{ $student->id }}"
                                        class="w-full ms-2 text-sm font-medium text-gray-900 rounded">{{ $student->name }}</label>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="mb-6">
                <label for="subject" class="block text-lg font-medium text-gray-700 mb-2">الموضوع</label>
                <input type="text" name="subject" id="subject"
                    class="mt-1 block w-full rounded-md border-2 border-slate-300 shadow-sm 
       focus:border-slate-700 focus:ring-0 focus:ring-offset-0
       transition-all duration-200 ease-in-out"
                    required>
            </div>

            <div class="mb-6">
                <label for="message" class="block text-lg font-medium text-gray-700 mb-2">الرسالة</label>
                <textarea name="message" id="message" rows="5"
                    class="mt-1 block w-full rounded-md border-2 border-slate-300 shadow-sm 
       focus:border-slate-700 focus:ring-0 focus:ring-offset-0
       transition-all duration-200 ease-in-out"
                    required></textarea>
            </div>
            <button type="submit" class="w-full flex justify-center items-center gap-2 bg-blue-500 my-4 p-3 rounded-lg text-white  hover:scale-[0.99] hover:bg-blue-600 transition-all duration-200 ease-in-out">
                <p>إرسال</p>
                <x-icons.send class="w-5 h-5 ml-2 text-white" />
            </button>
        </form>
    </div>
@endsection
