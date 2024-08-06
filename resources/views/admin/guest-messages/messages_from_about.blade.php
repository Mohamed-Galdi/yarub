@extends('layouts.admin')
@section('content')
    <div class="space-y-4">
        <h1 class="lg:text-4xl text-2xl text-nowrap truncate font-bold text-gray-800">الرسائل المرسلة</h1>
        <div class="w-full flex md:flex-row flex-col md:justify-between">
            <a href="{{ route('messages_from_about') }}"
                class="md:w-1/2 w-full py-2 text-center md:rounded-tr-xl md:rounded-br-xl md:rounded-bl-none md:rounded-tl-none rounded-tl-xl rounded-tr-xl  cursor-pointer {{ request()->routeIs('messages_from_about') ? 'bg-indigo-500 text-slate-200 font-semibold ' : 'bg-slate-300 text-gray-800' }}">صفحة
                من نحن</a>
            <a href="{{ route('messages_from_contact') }}"
                class="md:w-1/2 w-full  py-2 text-center md:rounded-tl-xl md:rounded-bl-xl md:rounded-br-none md:rounded-tr-none rounded-br-xl rounded-bl-xl  cursor-pointer {{ request()->routeIs('messages_from_contact') ? 'bg-indigo-500 text-slate-200 font-semibold ' : 'bg-slate-300 text-gray-800' }}">صفحة
                تواصل معنا
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left rtl:text-right text-gray-500  ">
                <thead class="text-lg  text-gray-700 uppercase bg-gray-300 w-full">
                    <tr>
                        <th scope="col" class="ps-8 py-3 w-4/6 ">
                            الرسالة
                        </th>
                        <th scope="col" class="px-6 py-3 w-1/6">
                            تاريخ الإرسال
                        </th>

                    </tr>
                </thead>
                <tbody>
                    @forelse ($messages_from_about as $message)
                        <tr data-modal-target="message-modal-{{ $message->id }}"
                            data-modal-toggle="message-modal-{{ $message->id }}"
                            class="border-b cursor-pointer w-full text-slate-800 transition-all ease-in-out duration-00 bg-slate-200 hover:bg-slate-300 border-x-2 border-x-indigo-500">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <p class="text-nowrap truncate w-full">{{ Str::words($message->message, 8, '...') }}</p>
                                </div>
                            </td>
                            <td>
                                <p class="text-nowrap truncate w-32">{{ $message->created_at->diffForHumans() }}</p>
                            </td>
                        </tr>

                        <!-- Message Modal -->
                        <div id="message-modal-{{ $message->id }}" tabindex="-1" aria-hidden="true"
                            class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-full max-h-full bg-slate-700/50">
                            <div class="relative p-4 w-full max-w-2xl max-h-full">
                                <!-- Modal content -->
                                <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                                    <!-- Modal header -->
                                    <div
                                        class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                                            رسالة من صفحة تواصل معنا 
                                        </h3>
                                        <button type="button" data-modal-hide="message-modal-{{ $message->id }}">
                                            <span class="sr-only">X</span>
                                        </button>
                                    </div>
                                    <!-- Modal body -->
                                    <div class="p-4 md:p-5 space-y-4">
                                        <p class="text-base leading-relaxed text-gray-500 dark:text-gray-400">
                                            {{ $message->message }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center p-3 bg-slate-200">لا توجد رسائل مرسلة حتى الآن
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
            <div class="mt-6">
                {{ $messages_from_about->links() }}
            </div>
        </div>
    </div>
@endsection
