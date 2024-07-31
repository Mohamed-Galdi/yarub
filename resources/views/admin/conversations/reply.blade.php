@extends('layouts.admin')

@section('content')
    <div class="container">
        <h1>الرد على المحادثة: {{ $conversation->subject }}</h1>
        <div class="mb-4 bg-white rounded-lg border border-gray-300 p-4">

            @foreach ($conversation->messages as $message)
                {{-- <div class="card mb-2">
                    <div class="card-body">
                        <h5 class="card-title">{{ $message->sender === 'admin' ? 'المسؤول' : 'الطالب' }}</h5>
                        <p class="card-text">{{ $message->content }}</p>
                        <p class="card-text"><small class="text-muted">{{ $message->created_at->diffForHumans() }}</small>
                        </p>
                    </div>
                </div> --}}
                <div class="w-full flex {{ $message->sender === 'admin' ? 'justify-start' : 'justify-end' }}">
                    <div class="flex items-start gap-2.5 mb-4 right-0">
                        <img class="w-8 h-8 rounded-full {{ $message->sender === 'admin' ? 'order-first' : 'order-last' }}" src="{{ asset($conversation->student->avatar) }}" alt="Jese image">
                        <div
                            class="flex flex-col w-full max-w-[320px] leading-1.5 p-4 border-gray-200   dark:bg-gray-700 {{ $message->sender === 'admin' ? 'bg-indigo-500 rounded-e-xl rounded-es-xl' : 'bg-gray-200  rounded-b-xl rounded-tr-xl' }}">
                            <div class="flex items-center space-x-2 rtl:space-x-reverse">
                                <span
                                    class="text-sm font-semibold {{ $message->sender === 'admin' ? 'text-gray-100' : 'text-gray-900' }} ">{{ $message->sender === 'admin' ? 'المسؤول' : $conversation->student->name }}</span>
                                <span
                                    class="text-sm font-normal {{ $message->sender === 'admin' ? 'text-gray-300' : 'text-gray-500' }}">{{ $message->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-xl  py-2.5   {{ $message->sender === 'admin' ? 'text-gray-200' : 'text-gray-500' }}">{{ $message->content }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <form action="{{ route('admin.conversations.send-reply', $conversation) }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="content">الرد</label>
                <textarea name="content" id="content" rows="5" class="form-control" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary mt-2">إرسال الرد</button>
        </form>
    </div>
@endsection
