    @extends('layouts.guest')
    @section('content')
        <div class="w-full md:h-[90vh] bg-gradient-to-tr from-pr-400 to-pr-700 ">
            <div
                class="max-w-screen-xl mx-auto flex md:flex-row flex-col  justify-between items-center h-full px-3 md:px-12 md:py-0 py-8 md:gap-0 gap-12">
                <div class="md:w-1/2 w-full flex flex-col items-center md:justify-end justify-center  md:h-[38rem]">
                    <h1 class=" text-gray-200 md:text-5xl text-4xl text-center md:order-1 order-2">قم بإنشاء حسابك الان</h1>
                    <img src="./assets/illustrations/regester.svg" alt=""
                        class="md:w-full w-2/3 h-fit order-1 md:order-2  object-cover">
                </div>
                <div
                    class="md:w-1/2 w-full bg-white/20 md:h-[32rem] rounded-lg bm-12 border-2 border-pr-100 overflow-hidden backdrop-blur-xl px-8 md:pb-0 pb-6">
                    <form action="{{ route('register') }}" method="POST" class=" space-y-8">
                        @csrf
                        <div class=" flex gap-3 w-full">
                            <x-form.input-dark type="text" name="first_name" placeholder="الإسم الشخصي" class="w-1/2" />
                            <x-form.input-dark type="text" name="last_name" placeholder="الإسم العائلي" class="w-1/2" />

                        </div>
                        <x-form.input-dark type="text" name="email" placeholder=" البريد الإلكتروني " class="w-full" />


                        <x-form.input-dark type="password" name="password" placeholder=" كلمة المرور " class="w-full" />


                        <x-form.input-dark type="password" name="password_confirmation" placeholder=" تأكيد كلمة المرور "
                            class="w-full" />


                        <x-btn.scale-light type="submit" class="w-full"> انشاء حساب </x-btn.scale-light>

                        <p class="text-center text-gray-200">لديك حساب بالفعل، قم بتسجيل <span
                                class="text-blue-400 underline hover:text-blue-500">
                                <a href="/login">الدخول من هنا</a>
                            </span></p>

                    </form>
                </div>



            </div>

        </div>
    @endsection()
