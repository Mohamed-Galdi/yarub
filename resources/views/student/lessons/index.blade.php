@extends('layouts.student')
@section('content')
   <div class="w-full min-h-[calc(100vh-6rem)] bg-gradient-to-tr from-gray-400 to-slate-100 ">
        <div class="max-w-screen-xl mx-auto  p-4 space-y-8">
            <h1 class="text-4xl mt-4 font-bold text-slate-600">شروحاتي</h1>
            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3 mt-12">
                @forelse ($lessons as $lesson)
                    <div class="lg:px-8">
                        <x-card.student-lesson :lesson="$lesson" />
                    </div>
                @empty
                    <div id="noResultsMessage"
                        class="col-span-3 flex flex-col justify-center items-center w-full space-y-4 text-center">
                        <p class="lg:text-4xl md:text-3xl text-2xl text-slate-600">لست مشتركا في اي شرح بعد.</p>
                        <p class="text-lg font-judur text-slate-400">تصفح الشروحات من هنا 👇، و قم بالإشتراك</p>
                        <a href="{{ route('lessons') }}"
                            class="text-slate-50 bg-indigo-400 hover:bg-indigo-500 border-2 border-white p-2 rounded-md flex justify-center items-center gap-2">
                            <x-icons.link class="w-4 h-4 " />
                            <p>الشروحات</p>
                        </a>
                        <img class="lg:w-1/2 w-full" src="{{ asset('assets/images/empty.svg') }}" alt="صورة فارغة" />
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection()