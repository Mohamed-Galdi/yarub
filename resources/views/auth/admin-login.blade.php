    @extends('layouts.guest')
    @section('content')
        <div class="w-full md:h-[88vh] bg-gradient-to-tr from-pr-400 to-pr-700 py-12 md:py-0 ">
            <div class="max-w-screen-xl mx-auto flex flex-col  justify-center items-center h-full px-12">
                <div class="w-full flex  items-center justify-center gap-6 h-fit py-8">
                    <h1 class=" text-gray-200 md:text-5xl text-2xl text-center text-nowrap">لوحة تحكم المشرفين </h1>
                    <img src="./assets/illustrations/lock.svg" alt="" class="md:w-24 w-12  object-cover">
                </div>
                <div class="md:w-1/2 w-full bg-white/20 h-fit rounded-lg border-2 border-pr-100 overflow-hidden backdrop-blur-xl p-8">
                    <form action="{{ route('admin.login') }}" method="POST"  class="py-2 space-y-8">
                        @csrf
                        <x-form.input type="text" name="email" placeholder=" البريد الإلكتروني " class="w-full" />
                        <x-form.input type="password" name="password" placeholder=" كلمة المرور " class="w-full" />
                        <x-btn.scale-light type="submit" class="w-full"> الدخول </x-btn.scale-light>
                    </form>
                </div>
            </div>
        </div>
    @endsection()
