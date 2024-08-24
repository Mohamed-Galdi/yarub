@extends('layouts.student')

@section('content')
    <div>
        <div class="container max-w-screen-xl mx-auto mt-12  p-4 min-h-screen">
            <div class="flex justify-between items-center gap-2 mb-4">
                <div class="flex justify-start gap-2 items-center lg:text-3xl text-2xl text-nowrap truncate">
                    <p class="text-indigo-500">الرد على المحادثة:</p>
                    <p class="text-slate-500"> {{ $conversation->subject }}</p>
                </div>
                {{-- // back button --}}
                <x-btn.back route="student.support.index" />
            </div>

            <div class="mb-4 bg-white rounded-lg border border-gray-300 px-4 py-6">

                @foreach ($conversation->messages as $message)
                    <div class="w-full flex {{ $message->sender === 'student' ? 'justify-start' : 'justify-end' }}">

                        <div class="flex items-start gap-2 ">
                            <div class="{{ $message->sender === 'student' ? 'order-first' : 'order-last' }}">
                                @if ($message->sender === 'student')    
                                <img class="min-w-8 min-h-8 max-h-8 max-w-8 rounded-full "
                                    src="{{ asset($conversation->student->avatar) }}" alt="Student Avatar">
                                @else
                                        <div class="flex justify-center items-center p-1 bg-slate-500 rounded-full">
                                        <x-icons.admin class="w-6 h-6 text-gray-100" />
                                    </div>
                                @endif
                            </div>
                            <div class=" flex flex-col gap-1">
                                <div class="{{ $message->sender === 'student' ? 'text-start' : 'text-end' }}">
                                    <p
                                        class="text-sm font-semibold {{ $message->sender === 'student' ? 'text-indigo-500' : 'text-slate-600' }} ">
                                        {{ $message->sender === 'student' ?  $conversation->student->name : 'المشرف' }}</p>
                                </div>
                                <div
                                    class="p-2.5 rounded-b-xl {{ $message->sender === 'student' ? 'bg-indigo-400 rounded-tl-xl ' : 'bg-slate-300 rounded-tr-xl' }} ">
                                    {{ $message->content }}
                                </div>
                                <div class="{{ $message->sender === 'student' ? 'text-start' : 'text-end' }}">
                                    <p class="text-sm font-normal text-gray-500">
                                        {{ $message->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                <form class="lg:mx-8 mx-2 mt-6" action="{{ route('student.conversations.send-reply', $conversation) }}"
                    method="POST">
                    @csrf
                    <div class="relative">
                        <textarea name="content" id="content" rows="4"
                            class="block w-full p-4 ps-4 text-base text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 "
                            placeholder="أكتب رد للرسالة ..." required></textarea>
                        <div class="w-full flex justify-end mt-2">

                            <button type="submit"
                                class = "flex justify-center items-center text-white gap-2 bg-gradient-to-tr from-blue-600 to-blue-400 p-2 rounded-xl border-2 border-white overflow-hidden hover:scale-[1.03] transition-all duration-300 ease-in-out">
                                <p>إرسال </p>
                                <x-icons.send class="w-5 h-5 " />
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
