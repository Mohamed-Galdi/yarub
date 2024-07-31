@extends('layouts.admin')

@section('content')
    <div class="container">
        <h1 class="mb-4">المحادثات</h1>

        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">

            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">
                            الطالب
                        </th>
                        <th scope="col" class="px-6 py-3">
                            الموضوع
                        </th>
                        <th scope="col" class="px-6 py-3">
                            آخر رسالة
                        </th>
                        <th scope="col" class="px-6 py-3">
                            الرسائل غير المقروءة
                        </th>
                        <th scope="col" class="px-6 py-3">
                            الإجراءات </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($conversations as $conversation)
                        <tr
                            class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <th scope="row"
                                class="flex items-center px-6 py-4 text-gray-900 whitespace-nowrap dark:text-white">
                                <img class="w-10 h-10 rounded-full" src="{{ asset($conversation->student->avatar) }}"
                                    alt="student image">
                                <div class="ps-3">
                                    <div class="text-base font-semibold">{{ $conversation->student->name }}</div>
                                    <div class="font-normal text-gray-500">{{ $conversation->student->email }}</div>
                                </div>
                            </th>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <p>{{ $conversation->subject }}</p>
                                </div>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($conversation->last_message_at)->diffForHumans() }}</td>
                            <td>
                                @if ($conversation->admin_unread_count > 0)
                                    <span class="badge bg-danger">{{ $conversation->admin_unread_count }} غير مقروءة</span>
                                @else
                                    <span class="badge bg-success">مقروءة</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.conversations.reply', $conversation) }}"
                                    class="flex gap-2 items-center justify-center p-2 bg-indigo-500 hover:bg-indigo-600 rounded-lg text-gray-100">
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

        {{ $conversations->links() }}
    </div>
@endsection
