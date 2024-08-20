@extends('layouts.student')
@section('content')
    <div class="w-full min-h-[calc(100vh-6rem)] bg-pr-200">
        <div class="max-w-screen-xl mx-auto  p-4 space-y-8">
            {{-- <h1 class="text-4xl mt-4 font-bold text-indigo-600">دوراتي</h1> --}}
            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3 mt-12">
                @forelse ($courses as $course)
                    <div class="lg:px-8">
                        <x-card.student-course :course="$course" />
                    </div>
                @empty
                    <div id="noResultsMessage"
                        class="col-span-3 flex flex-col justify-center items-center w-full space-y-4 text-center">
                        <p class="lg:text-4xl md:text-3xl text-2xl text-gray-100">لست مشتركا في اي دورة بعد.</p>
                        <p class="text-lg font-judur text-gray-300">تصفح الدورات من هنا 👇، و قم بالإشتراك</p>
                        <a href="{{ route('courses') }}"
                            class="text-slate-50 bg-indigo-400 hover:bg-indigo-500 border-2 border-white p-2 rounded-md flex justify-center items-center gap-2">
                            <x-icons.link class="w-4 h-4 " />
                            <p>الدورات</p>
                        </a>
                        <img class="lg:w-1/2 w-full" src="{{ asset('assets/images/empty.svg') }}" alt="صورة فارغة" />
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection()
