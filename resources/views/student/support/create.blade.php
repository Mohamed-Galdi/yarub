@extends('layouts.student')

@section('content')
    <div>
        <div class="container max-w-screen-xl mx-auto mt-12  p-4 min-h-screen">
        {{-- <x-form.errors :errors="$errors" /> --}}
        <div class="flex justify-between">
            <h1 class="panel-page-title">إنشاء محادثة جديدة</h1>
            {{-- // back button --}}
            <x-btn.back route="student.support.index" />
        </div>
        <form action="{{ route('student.conversations.store') }}" method="POST" class="panel-surface space-y-4">
            @csrf
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
            <button type="submit" class="panel-button panel-button--primary w-full flex justify-center items-center gap-2 my-4 p-3 rounded-lg transition-all duration-200 ease-in-out">
                <p>إرسال</p>
                <x-icons.send class="w-5 h-5 ml-2 text-white" />
            </button>
        </form>
    </div>
    </div>
@endsection
