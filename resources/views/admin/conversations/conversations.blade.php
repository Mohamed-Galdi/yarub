@extends('layouts.admin')

@section('content')
    <div class="flex flex-col justify-start items-start min-h-screen space-y-6 w-full">
        <div>
            <a href="{{ route('admin.conversations.create') }}"
                class = "panel-button panel-button--primary">
                <p>إنشاء رسالة</p>
                <x-icons.send class="w-5 h-5 " />
            </a>
        </div>

        <div class="panel-table-region relative overflow-x-auto shadow-md sm:rounded-lg w-full" tabindex="0" role="region" aria-label="جدول البيانات">

            <table class="w-full text-left rtl:text-right text-gray-500 ">
                <thead class="text-lg  text-gray-700 uppercase bg-gray-200 w-full">
                    <tr>
                        <th scope="col" class="ps-8 py-3 w-2/12 ">
                            الطالب
                        </th>
                        <th scope="col" class="px-6 py-3 w-2/12">
                            الموضوع
                        </th>
                        <th scope="col" class="ps-8 py-3 w-4/12">
                            الرسالة
                        </th>
                        <th scope="col" class="px-6 py-3 w-1/12 text-nowrap truncate">
                            آخر رسالة
                        </th>
                        <th scope="col" class="px-6 py-3 w-1/12">

                        </th>
                        <th scope="col" class="px-6 py-3 w-1/12">
                            الإجراءات </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($conversations as $conversation)
                            <tr onclick="window.location.href='{{ route('admin.conversations.reply', $conversation) }}'"
                                class="border-b cursor-pointer  w-full text-slate-800 transition-all ease-in-out duration-00  {{ $conversation->admin_unread_count == 0 ? ' text-base bg-slate-100 hover:bg-slate-200 ' : ' text-base font-semibold bg-slate-300 hover:bg-[#bbc4d4] border-x-2 border-x-indigo-500 ' }}">
                                <th scope="row" class="flex items-center px-6 py-4 text-gray-900 whitespace-nowrap w-full  overflow-hidden   ">
                                    <img class="w-10 h-10 rounded-full" src="{{ asset($conversation->student->avatar ?: 'assets/images/panel-avatar.svg') }}"
                                        alt="student image">
                                    <div class="ps-3">
                                        <div class="text-base font-semibold text-nowrap truncate w-32">{{ $conversation->student->name }}</div>
                                        <div class="font-normal text-gray-500 text-nowrap truncate w-32">{{ $conversation->student->email }}</div>
                                    </div>
                                </th>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <p class=" text-nowrap truncate w-32">{{ $conversation->subject }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <p class="text-nowrap truncate">{{ Str::limit($conversation->lastMessage->content, 45) }}</p>
                                    </div>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($conversation->last_message_at)->diffForHumans() }}</td>
                                <td class="text-center">
                                    <p>({{ $conversation->admin_unread_count }})</p>
                                </td>
                                </td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('admin.conversations.reply', $conversation) }}"
                                        class="panel-button panel-button--secondary flex gap-2 items-center justify-center p-2 rounded-lg">
                                        <p>الرد</p>
                                        <x-icons.reply class="w-4 h-4 " />
                                    </a>
                                </td>
                            </tr>
                    @empty
                    @endforelse

                </tbody>
            </table>
        </div>
        <div dir="rtl" class="flex justify-center items-center gap-2 mt-6 w-full">
            {{ $conversations->links() }}
        </div>

    </div>
@endsection
