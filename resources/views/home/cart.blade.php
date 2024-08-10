@extends('layouts.guest')
@section('content')
    <div class="bg-slate-300">
        <div class="py-8  mx-auto max-w-screen-xl space-y-6 min-h-screen">
            <h1 class="text-4xl text-slate-800 lg:ms-0 ms-6">سلة التسوق</h1>
            <div class="flex lg:flex-row flex-col h-full gap-8 lg:px-0 px-4">
                {{-- Forms --}}
                <div class="bg-gray-100 text-slate-700 rounded-lg shadow-lg lg:w-1/3 w-full lg:min-h-screen h-fit flex flex-col justify-start p-3 lg:order-1 order-2">
                    {{-- User Acount --}}
                    <div>
                        <form action="{{ route('student.login') }}" method="POST" class="px-4 space-y-2">
                            @csrf
                            <h2 class="">حساب المستخدم</h2>
                            @guest
                                <input type="hidden" name="cart" value="true">
                                <x-form.input-light type="text" label="" name="email" placeholder="البريد الالكتروني"
                                    value="{{ old('email') }}" class="mb-8" />
                                <x-form.input-light type="password" label="" name="password" placeholder="كلمة المرور"
                                    value="{{ old('password') }}" />
                                <button type="submit"
                                    class="w-full p-1 text-white bg-slate-700 hover:bg-slate-800 rounded-lg shadow-lg text-nowrap truncate">تسجيل
                                    الدخول</button>
                                <p class="w-full text-center text-base">ليس لديك حساب ؟ <a href="{{ route('register_page') }}"
                                        class="text-indigo-500">أنشئه من هنا</a> 👉</p>
                            @endguest
                            @auth
                                <div
                                    class="flex justify-start gap-4 items-center bg-indigo-500 rounded-lg p-2 shadow-lg shadow-blue-400 border border-white hover:shadow-none hover:scale-[0.99] transition-all duration-300 ease-in-out cursor-pointer">
                                    <div>
                                        <img src="{{ Auth::user()->avatar }}" alt="avatar" class="w-16 h-16 rounded-full" />
                                    </div>
                                    <div class="flex flex-col items-start justify-start">
                                        <h1 class="text-2xl text-center text-slate-100">{{ Auth::user()->name }}</h1>
                                        <p class="text-center text-slate-200">{{ Auth::user()->email }}</p>
                                    </div>
                                </div>
                            @endauth
                        </form>
                        <div class="h-[2px] w-[90%] bg-indigo-500 rounded-lg mt-6 mx-auto "></div>
                    </div>
                    {{-- Coupon Code --}}
                    <form action="{{ route('cart.apply-coupon') }}" method="POST" class="p-4 space-y-2">
                        @csrf
                        <h2 class="text-nowrap truncate">لديك قسيمة (كوبون) ؟</h2>
                        <x-form.input-light type="text" label="" name="coupon" placeholder="الكوبون"
                            value="{{ old('coupon') }}" />
                        <button type="submit"
                            class="w-full p-1 text-white bg-slate-700 hover:bg-slate-800 rounded-lg shadow-lg">
                            تطبيق</button>
                    </form>

                    {{-- Total Price --}}
                    <div class="p-4">
                        <div class="p-2 border-2 border-white rounded-lg bg-indigo-500 text-slate-100">
                            <h2 class="">مجموع السلة</h2>
                            <div class="px-6 mt-4 font-nitaqat space-y-2">
                                <div class=" flex justify-between items-center">
                                    <p class="text-nowrap truncate">المبلغ الأصلي</p>
                                    <p class="text-nowrap truncate">{{ number_format($totalBeforeDiscount, 2) }} ر.س</p>
                                </div>
                                {{-- @if (session('discount')) --}}
                                    <div class="text-base font-bold flex justify-between items-center text-warning-500">
                                        <p class="text-nowrap truncate">خصم القسيمة</p>
                                        <p class="text-nowrap truncate">{{ number_format(session('discount'), 2) }} ر.س</p>
                                    </div>
                                {{-- @endif --}}
                                <div class=" flex justify-between items-center font-bold">
                                    <p class="text-nowrap truncate">المبلغ النهائي</p>
                                    <p class="text-nowrap truncate">{{ number_format($totalAfterDiscount, 2) > 0 ? number_format($totalAfterDiscount, 2) : '0' }} ر.س</p>
                                </div>
                            </div>
                            <button
                                class="bg-gray-50 mt-4 text-slate-700 rounded-lg shadow-lg w-full text-nowrap truncate p-1 hover:bg-warning-200">
                                إتمام عملية الشراء
                            </button>
                        </div>
                    </div>
                </div>
                {{-- Cart Items --}}
                <div class="bg-gray-100 rounded-lg shadow-lg lg:w-2/3 w-full lg:min-h-screen h-fit  p-4 lg:order-2 order-1">
                    <div class="w-full  flex justify-center items-center gap-4">
                        <a href="{{ route('courses') }}" >
                            <button class="bg-indigo-500 hover:bg-indigo-700 text-white  py-1 px-2 rounded">
                                <p class="ml-2 lg:text-xl text-base text-nowrap truncate lg:w-32 w-24 text-center">صفحة الدورات</p>
                            </button>
                        </a>
                        <a href="{{ route('lessons') }}" >
                            <button class="bg-teal-500 hover:bg-teal-700 text-white  py-1 px-2 rounded">
                                <p class="ml-2 lg:text-xl text-base text-nowrap truncate lg:w-32 w-24 text-center">صفحة الشروحات</p>
                            </button>
                        </a>
                    </div>
                    <div class="w-full h-full flex flex-col justify-start items-start gap-4 mt-4 ">
                        @forelse ($cart as $item)
                            <div
                                class="relative bg-gradient-to-tr min-h-32  rounded-lg shadow-lg p-4 w-full flex md:flex-row flex-col justify-between md:items-center overflow-hidden {{ $item['type'] == 'course' ? 'from-indigo-800 to-indigo-400' : 'from-teal-800 to-teal-400' }}">
                                <div class="flex flex-col justify-start items-start w-3/4">
                                    <div class="w-full flex items-start gap-2">
                                        <h2 class="text-xl mb-2 text-slate-100 text-nowrap truncate items-end">
                                            {{ $item['title'] }}
                                        </h2>
                                        <p class="  text-slate-800 px-1 bg-slate-100 rounded-xl w-fit h-fit text-base">
                                            {{ $item['type'] == 'course' ? 'دورة' : 'شرح' }}</p>
                                    </div>
                                    <p class="text-gray-300 text-sm font-judur text-nowrap truncate w-full ">
                                        {{ $item['description'] }}
                                    </p>
                                    <div class="flex w-full">
                                        <x-card.rating />
                                    </div>
                                </div>
                                <div class="w-1/4 flex flex-col justify-center items-center gap-2">
                                    @if ($item['type'] == 'course')
                                        <p class="text-slate-500 text-2xl p-2 bg-warning-200 rounded-xl text-nowrap truncate">
                                            {{ $item['price'] }}
                                            ر.س</p>
                                    @else
                                        <p class="text-slate-500 text-2xl p-2 bg-warning-200 rounded-xl text-nowrap truncate">
                                            {{ $item['plan'] == 'annual' ? $item['annual_price'] : $item['monthly_price'] }}
                                            ر.س</p>
                                        <select
                                            class="plan-selector block w-20 h-[2.5rem] rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                            data-item-id="{{ $item['id'] }}" data-type="{{ $item['type'] }}">
                                            <option value="monthly" {{ $item['plan'] == 'monthly' ? 'selected' : '' }}>
                                                شهريا</option>
                                            <option value="annual" {{ $item['plan'] == 'annual' ? 'selected' : '' }}>سنويا
                                            </option>
                                        </select>
                                    @endif
                                </div>
                                <div class="absolute top-3 left-3 group">
                                    <button data-item-id="{{ $item['id'] }}" data-type="{{ $item['type'] }}"
                                        class="remove-from-cart group">
                                        <x-icons.x
                                            class="text-white w-4 h-4 group-hover:text-red-500 group-hover:w-5 group-hover:h-5 transition-all duration-300 ease-in-out" />
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="w-full h-full flex flex-col lg:mt-20 mt-4 justify-center items-center">
                                <h2 class="lg:text-3xl text-xl text-nowrap truncate w-full text-center">السلة الخاصة بك فارغة</h2>
                                <img src="{{ asset('assets/images/empty_cart.png') }}" alt="cart" class="w-1/2" />
                            </div>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
