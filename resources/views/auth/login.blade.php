    @extends('layouts.guest')
    @section('content')
        <div class="w-full md:h-[90vh] h-fit bg-gradient-to-tr from-pr-400 to-pr-700 pb-8 md:pb-0 ">
            <div class="max-w-screen-xl mx-auto flex md:flex-row flex-col  justify-between items-center h-full md:px-12 px-3">
                <div class="md:w-1/2 w-full flex flex-col items-center md:justify-center gap-2 md:h-[32rem] my-8 md:my-0  ">
                    <h1 class=" text-gray-200 text-5xl text-center order-2 md:order-1">تسجيل الدخول</h1>
                    <img src="./assets/illustrations/login.png" alt="" class="md:w-3/5 w-1/3 h-fit order-1 md:order-2   object-cover ">
                </div>
                <div
                    class="md:w-1/2 w-full bg-white/20 md:h-[22rem] rounded-lg border-2 border-pr-100 overflow-hidden backdrop-blur-xl p-8">
                    <form action=" " class="py-8 space-y-8">
                        <x-form.input type="text" name="eamil" placeholder=" البريد الإلكتروني " class="w-full" />
                        <x-form.input type="password" name="password" placeholder=" كلمة المرور " class="w-full" />
                        <x-btn.scale-light> انشاء حساب </x-btn.scale-light>
                        <p class="text-center text-gray-200"> ليس لديك حساب بعد، قم<span
                                class="text-blue-400 underline hover:text-blue-500">
                                <a href="/register"> بإنشاءه من هنا </a>
                            </span></p>
                    </form>
                </div>
            </div>
        </div>
    @endsection()
