@extends('layouts.guest')
@section('content')
    <div class="bg-slate-300">
        <div class="py-8  mx-auto max-w-screen-xl space-y-6 min-h-screen">
            <h1 class="text-4xl text-slate-800 lg:ms-0 ms-6">سلة التسوق</h1>
            <div class="flex lg:flex-row flex-col h-full gap-8 lg:px-0 px-4">
                {{-- Forms --}}
                <div
                    class="bg-gray-100 text-slate-700 rounded-lg shadow-lg lg:w-1/3 w-full lg:min-h-screen h-fit flex flex-col justify-start p-3 lg:order-1 order-2">
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
                                    <p class="text-nowrap truncate">
                                        {{ number_format($totalAfterDiscount, 2) > 0 ? number_format($totalAfterDiscount, 2) : '0' }}
                                        ر.س</p>
                                </div>
                            </div>
                            <button id="payButton" data-modal-target="payment-modal" data-modal-toggle="payment-modal"
                                class="pay-button bg-gray-50 mt-4 text-slate-700 rounded-lg shadow-lg w-full text-nowrap truncate p-1 hover:bg-warning-200">
                                إتمام عملية الشراء
                            </button>
                        </div>
                    </div>
                </div>
                {{-- Cart Items --}}
                <div class="bg-gray-100 rounded-lg shadow-lg lg:w-2/3 w-full lg:min-h-screen h-fit  p-4 lg:order-2 order-1">
                    <div class="w-full  flex justify-center items-center gap-4">
                        <a href="{{ route('courses') }}">
                            <button class="bg-indigo-500 hover:bg-indigo-700 text-white  py-1 px-2 rounded">
                                <p class="ml-2 lg:text-xl text-base text-nowrap truncate lg:w-32 w-24 text-center">صفحة
                                    الدورات</p>
                            </button>
                        </a>
                        <a href="{{ route('lessons') }}">
                            <button class="bg-teal-500 hover:bg-teal-700 text-white  py-1 px-2 rounded">
                                <p class="ml-2 lg:text-xl text-base text-nowrap truncate lg:w-32 w-24 text-center">صفحة
                                    الشروحات</p>
                            </button>
                        </a>
                        <a href="{{ route('packages') }}">
                            <button class="bg-red-500 hover:bg-red-600 text-white  py-1 px-2 rounded">
                                <p class="ml-2 lg:text-xl text-base text-nowrap truncate lg:w-32 w-24 text-center">صفحة
                                    الحقائب</p>
                            </button>
                        </a>
                    </div>
                    <div class="w-full h-full flex flex-col justify-start items-start gap-4 mt-4 ">
                        @forelse ($cart as $item)
                            @php
                                $colorClasses = match ($item['type']) {
                                    'course' => 'from-indigo-800 to-indigo-400',
                                    'lesson' => 'from-teal-800 to-teal-400',
                                    'package' => 'from-red-800 to-red-400',
                                    default => 'from-gray-800 to-gray-400',
                                };
                                $typeLabel = match ($item['type']) {
                                    'course' => 'دورة',
                                    'lesson' => 'شرح',
                                    'package' => 'حقيبة',
                                    default => 'غير معروف',
                                };
                            @endphp
                            <div
                                class="relative bg-gradient-to-tr min-h-32  rounded-lg shadow-lg p-4 w-full flex md:flex-row flex-col justify-between md:items-center overflow-hidden {{ $colorClasses }}">
                                <div class="flex flex-col justify-start items-start w-3/4">
                                    <div class="w-full flex items-start gap-2">
                                        <h2 class="text-xl mb-2 text-slate-100 text-nowrap truncate items-end">
                                            {{ $item['title'] }}
                                        </h2>
                                        <p class="  text-slate-800 px-1 bg-slate-100 rounded-xl w-fit h-fit text-base">
                                            {{ $typeLabel }}</p>
                                    </div>
                                    <p class="text-gray-300 text-sm font-judur text-nowrap truncate w-full ">
                                        {{ $item['description'] }}
                                    </p>
                                    <div class="flex w-full">
                                        {{-- <x-card.rating /> --}}
                                    </div>
                                </div>
                                <div class="w-1/4 flex flex-col justify-center items-center gap-2">
                                    @if ($item['type'] == 'course')
                                        <p
                                            class="text-slate-500 text-2xl p-2 bg-warning-200 rounded-xl text-nowrap truncate">
                                            {{ $item['price'] }}
                                            ر.س</p>
                                    @elseif($item['type'] == 'lesson')
                                        <p
                                            class="text-slate-500 text-2xl p-2 bg-warning-200 rounded-xl text-nowrap truncate">
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
                                    @elseif($item['type'] == 'package')
                                        @if ($item['price'])
                                            <p
                                                class="text-slate-500 text-2xl p-2 bg-warning-200 rounded-xl text-nowrap truncate">
                                                {{ $item['price'] }}
                                                ر.س</p>
                                        @else
                                            <p
                                                class="text-slate-500 text-2xl p-2 bg-warning-200 rounded-xl text-nowrap truncate">
                                                {{ $item['plan'] == 'annual' ? $item['annual_price'] : $item['monthly_price'] }}
                                                ر.س</p>
                                            <select
                                                class="plan-selector block w-20 h-[2.5rem] rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                data-item-id="{{ $item['id'] }}" data-type="{{ $item['type'] }}">
                                                <option value="monthly" {{ $item['plan'] == 'monthly' ? 'selected' : '' }}>
                                                    شهريا</option>
                                                <option value="annual" {{ $item['plan'] == 'annual' ? 'selected' : '' }}>
                                                    سنويا
                                                </option>
                                            </select>
                                        @endif
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
                                <h2 class="lg:text-3xl text-xl text-nowrap truncate w-full text-center">السلة الخاصة بك
                                    فارغة</h2>
                                <img src="{{ asset('assets/images/empty_cart.png') }}" alt="cart" class="w-1/2" />
                            </div>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    <!-- Payment modal -->
    <div id="payment-modal" tabindex="-1" aria-hidden="true"
        class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 max-h-full bg-gray-800/70">
        <div class="relative p-4 w-full max-w-md max-h-full">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow  ">
                <!-- Modal header -->
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t ">
                    <h3 class="text-lg font-semibold text-gray-900 ">
                        إتمام عملية الدفع
                    </h3>
                    <button type="button" id="closeModalButton"
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center "
                        data-modal-toggle="payment-modal">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="py-4">
                    <div class="mysr-form"></div>
                </div>

            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const user = @json(Auth::check() ? Auth::user() : null);
            const activeCourses = @json(collect(Auth::user()?->getAccessibleCourses()->pluck('id')->toArray()));
            const activeLessons = @json(collect(Auth::user()?->getAccessibleLessons()->pluck('id')->toArray()));
            const cart = @json(collect($cart));
            const amount = {{ $totalAfterDiscount }};
            const cartCount = {{ count($cart) }};
            const moyasarKey = '{{ config('services.moyasar.test_key') }}';
            const callBackUrl = '{{ route('subscription') }}';

            function checkActiveSubscriptions() {
                for (const item of Object.values(cart)) {
                    if (item.type === 'course' && activeCourses.includes(parseInt(item.id))) {
                        return {
                            hasActive: true,
                            type: 'course',
                            title: item.title
                        };
                    }
                    if (item.type === 'lesson' && activeLessons.includes(parseInt(item.id))) {
                        return {
                            hasActive: true,
                            type: 'lesson',
                            title: item.title
                        };
                    }
                }
                return {
                    hasActive: false
                };
            }

            document.getElementById('payButton').onclick = function() {
                if (!user) {
                    Swal.fire({
                        title: 'المرجوا تسجيل الدخول أولا',
                        icon: 'warning',
                        showConfirmButton: false,
                        timer: 2500,
                        timerProgressBar: true,
                    });
                    document.getElementById('closeModalButton').click();
                } else if (cartCount === 0) {
                    Swal.fire({
                        title: ' السلة الخاصة بك فارغة',
                        icon: 'warning',
                        showConfirmButton: false,
                        timer: 2500,
                        timerProgressBar: true,
                    });
                    document.getElementById('closeModalButton').click();
                } else if (amount <= 0) {
                    Swal.fire({
                        title: 'لا يمكن إتمام العملية بمبلغ اقل من 0',
                        icon: 'warning',
                        showConfirmButton: false,
                        timer: 2500,
                        timerProgressBar: true,
                    });
                    document.getElementById('closeModalButton').click();

                } else {
                    // check if there is an active subscription for the current user
                    const activeSubscription = checkActiveSubscriptions();
                    if (activeSubscription.hasActive) {
                        const itemType = activeSubscription.type === 'course' ? 'الدورة' : 'الشرح';
                        const shortTitle = activeSubscription.title.split(' ').slice(0, 5).join(' ');
                        Swal.fire({
                            title: `لديك بالفعل اشتراك نشط في ${itemType}`,
                            text: `${shortTitle} `,
                            icon: 'warning',
                            showConfirmButton: false,
                            timer: 2500,
                            timerProgressBar: true,
                        });
                        document.getElementById('closeModalButton').click();
                    } else {
                        // All conditions are met, initialize Moyasar
                        Moyasar.init({
                            element: '.mysr-form',
                            amount: amount * 100,
                            currency: 'SAR',
                            description: 'order for ' + user.name,
                            publishable_api_key: moyasarKey,
                            callback_url: callBackUrl,
                            methods: ['creditcard'],
                        });
                    }
                }
            };
        });
    </script>
@endsection
