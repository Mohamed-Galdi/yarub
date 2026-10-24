<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head dir="rtl">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'منصة يعرب التعليمية')</title>
    @hasSection('description')<meta name="description" content="@yield('description')">@endif
    <link rel="icon" type="image/x-icon" href="/assets/favicon.ico">
    <!-- Scripts -->
    @vite(['resources/css/app.css', request()->routeIs('qudrat', 'arabic-skills') ? 'resources/js/learning-public.js' : 'resources/js/app.js'])
    {{-- csrf token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @unless(request()->routeIs('qudrat', 'arabic-skills'))
    <!-- Moyasar Styles -->
    <link rel="stylesheet" href="https://cdn.moyasar.com/mpf/1.14.0/moyasar.css" />

    <!-- Moyasar Scripts -->
    <script src="https://cdnjs.cloudflare.com/polyfill/v3/polyfill.min.js?version=4.8.0&features=fetch"></script>
    <script src="https://cdn.moyasar.com/mpf/1.14.0/moyasar.js"></script>
    @endunless


</head>

<body class="font-hacen text-xl text-gray-900 antialiased">

    <header class="mx-auto max-w-screen-xl bg-white flex justify-between items-center ">

        <nav class="bg-white   w-full z-20 top-0 start-0 border-b border-gray-200 ">
            <div dir="rtl" class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto p-2">
                <a href="/" class="flex items-center space-x-3 ">
                    <img src="{{ asset('assets/logos/logo_dark.png') }}" class="w-24" alt="منصة يعرب">
                </a>
                <div class="flex xl:order-2 space-x-0 xl:space-x-2 items-center gap-3  ">

                    @auth
                        @if (Auth::user()->role === 'student')
                            <a href="{{ route('student.courses.index') }}" class="guest-account-link guest-account-solid">
                                    <span>حسابي</span>
                                    <x-icons.user class="w-5 h-5"></x-icons.user>
                            </a>
                        @elseif(Auth::user()->role === 'admin')
                            <a href="{{ route('admin.dashboard') }}" class="guest-account-link guest-account-solid">
                                    <span>المشرف</span>
                                    <x-icons.admin class="w-5 h-5"></x-icons.admin>
                            </a>
                        @endif
                    @endauth


                    @guest
                        <a href="{{ route('register') }}" class="guest-account-link guest-account-solid">أنشئ حسابك</a>
                        <a href="{{ route('login') }}" class="guest-account-link">دخول</a>
                    @endguest


                    @if (Auth::check() && Auth::user()->role === 'admin')
                        <span></span>
                    @else()
                        {{-- <div class="w-8 h-8 relative">
                            <x-icons.cart class="text-sm text-pr-500 w-full h-full "></x-icons.cart>
                            <div
                                class="text-white bg-pr-500 rounded-full absolute top-0 right-0 w-4 h-4 flex justify-center items-center">
                                0</div>
                        </div> --}}
                        <div class="w-8 h-8 relative cursor-pointer">
                            <a href="{{ route('cart.index') }}" aria-label="سلة المشتريات">
                                <x-icons.cart class="text-sm text-pr-500 w-full h-full "></x-icons.cart>
                                <div
                                    class="text-white bg-pr-500 rounded-full absolute top-0 right-0 w-4 h-4 flex justify-center items-center cart-count">
                                    {{ $cart ? count($cart) : 0 }}
                                </div>
                            </a>
                        </div>
                    @endif

                    <button data-collapse-toggle="navbar-sticky" type="button"
                        class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-gray-100 bg-primary rounded-lg xl:hidden hover:bg-gray-200 hover:text-pr-500 focus:outline-none focus:ring-2 focus:ring-gray-200 "
                        aria-controls="navbar-sticky" aria-expanded="false" aria-label="القائمة الرئيسية">
                        <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 17 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M1 1h15M1 7h15M1 13h15" />
                        </svg>
                    </button>
                </div>
                <div class="items-center justify-between hidden w-full xl:flex xl:w-auto xl:order-1" id="navbar-sticky">
                    <ul
                        class="flex flex-col p-4 xl:p-0 mt-4 font-medium border border-gray-100 rounded-lg bg-gray-50 xl:space-x-8 rtl:space-x-reverse xl:flex-row xl:mt-0 xl:border-0 xl:bg-white ">
                        <x-btn.guest-nav-item :route="'home'" :title="'الرئيسية'" />
                        <x-btn.guest-nav-item :route="'courses'" :title="'الدورات'" />
                        <x-btn.guest-nav-item :route="'qudrat'" :title="'القدرات'" />
                        <x-btn.guest-nav-item :route="'arabic-skills'" :title="'مهارات العربية'" />
                        <x-btn.guest-nav-item :route="'about'" :title="'من نحن'" />
                        <x-btn.guest-nav-item :route="'contact'" :title="'تواصل معنا'" />
                        <li>
                            <a href="https://arub.yarub.org/" target="_blank"
                                class="block py-2 px-3 xl:p-0 text-slate-600 rounded hover:bg-gray-100 xl:hover:bg-transparent xl:hover:text-primary  ">عَرُوب</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

    </header>


    <main dir="rtl">
        @yield('content')
    </main>
    <footer dir="rtl" class=" text-gray-200 bg-primary  ">
        <div class="mx-auto max-w-screen-xl flex md:flex-row flex-col justify-around items-center p-3 ">
            <div class="flex flex-col md:items-start items-center gap-2 py-2 md:order-1 order-1">
                <p>روابط سريعة: </p>
                <ul class="flex flex-wrap justify-center text-sm gap-2">
                    <li><a href="{{ url('/') }}"
                            class="hover:underline md:me-1 {{ Request::is('/') ? 'text-gray-100 ' : 'text-gray-400' }}">الرئيسية
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/courses') }}"
                            class="hover:underline md:me-1 {{ Request::is('courses') ? 'text-gray-100 ' : 'text-gray-400' }}">الدورات
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('qudrat') }}" class="hover:underline md:me-1 {{ request()->routeIs('qudrat') ? 'text-gray-100' : 'text-gray-400' }}">القدرات</a>
                    </li>
                    <li>
                        <a href="{{ route('arabic-skills') }}" class="hover:underline md:me-1 {{ request()->routeIs('arabic-skills') ? 'text-gray-100' : 'text-gray-400' }}">مهارات العربية</a>
                    </li>
                    <li>
                        <a href="{{ url('/about') }}"
                            class="hover:underline md:me-1 {{ Request::is('about') ? 'text-gray-100 ' : 'text-gray-400' }}">من
                            نحن
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/contact') }}"
                            class="hover:underline md:me-1 {{ Request::is('contact') ? 'text-gray-100 ' : 'text-gray-400' }}">تواصل
                            معنا
                        </a>
                    </li>
                    <li>
                        <a href="https://arub.yarub.org/" target="_blank"
                            class="hover:underline md:me-1 {{ Request::is('contact') ? 'text-gray-100 ' : 'text-gray-400' }}">عَرُوب

                        </a>
                    </li>


                </ul>
            </div>

            <div class="text-center flex flex-col items-center gap-2 py-2 md:order-2 order-3">
                <p class="text-gray-200 text-base">رقم السجل التجاري : <span
                        class=" font-bold underline">{{ $commercial_registration_no }} </span>
                <p> منصة يعرب | جميع الحقوق محفوظة © 2024.</p>

                </p>
            </div>
            <div class="flex flex-col items-center gap-4 py-2 md:order-3 order-2">
                <a href="/">
                    <img src="{{ asset('assets/logos/logo_white.png') }}" class="w-24" alt="منصة يعرب">
                </a>
                <div class="flex gap-3 mb-2 sm:justify-center sm:mt-0">
                    {{-- <a href="https://youtube.com" target='_blank'
                        class="text-gray-200 hover:scale-110 hover:text-white transition-all">
                        <x-icons.youtube class="w-6 h-6" />
                    </a> --}}
                    <a href="{{ $whatsapp_number }}" target='_blank'
                        class="text-gray-200 hover:scale-110 hover:text-white transition-all">
                        <x-icons.whatsapp class="w-6 h-6" />
                    </a>
                    <a href="{{ $instagram }}" target='_blank'
                        class="text-gray-200 hover:scale-110 hover:text-white transition-all">
                        <x-icons.instagram class="w-6 h-6" />
                    </a>
                    <a href="{{ $tiktok }}" target='_blank'
                        class="text-gray-200 hover:scale-110 hover:text-white transition-all">
                        <x-icons.tiktok class="w-6 h-6" />
                    </a>
                    <a href="{{ $snapchat }}" target='_blank'
                        class="text-gray-200 hover:scale-110 hover:text-white transition-all">
                        <x-icons.snapchat class="w-6 h-6" />
                    </a>
                </div>
            </div>
        </div>
    </footer>
    @include('sweetalert::alert')
    @stack('scripts')
    {{-- Cart Script --}}
    {{-- Ajax cdn --}}
    <script script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
        integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        // Add to Cart
        $('.add-to-cart').click(function(event) {
            event.preventDefault();
            let itemId = $(this).data('item-id');
            let type = $(this).data('type');
            let title = $(this).data('title');
            let description = $(this).data('description');
            let price = $(this).data('price');
            let monthlyPrice = $(this).data('monthly-price');
            let annualPrice = $(this).data('annual-price');

            $.ajax({
                url: '{{ route('cart.add') }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    id: itemId,
                    type: type,
                    title: title,
                    description: description,
                    price: price,
                    monthly_price: monthlyPrice,
                    annual_price: annualPrice
                },
                success: function(response) {
                    // Update cart count in the header
                    $('.cart-count').text(response.count);
                    showAlert(response.success, 'success');
                },
                error: function(xhr) {
                    showAlert(xhr.responseJSON.error, 'error');
                }
            });
        });

        // Remove from Cart
        $('.remove-from-cart').click(function() {
            let itemId = $(this).data('item-id');
            let type = $(this).data('type');

            $.ajax({
                url: '{{ route('cart.remove') }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    itemId: itemId,
                    type: type
                },
                success: function(response) {
                    // Update cart count in the header
                    $('.cart-count').text(response.count);
                    showAlert(response.success, 'success');
                },
                error: function(xhr) {
                    showAlert(xhr.responseJSON.error, 'error');
                }
            });
            location.reload();
        });

        // Update Cart Plan
        $('.plan-selector').change(function() {
            let itemId = $(this).data('item-id');
            let type = $(this).data('type');
            let newPlan = $(this).val();
            console.log(newPlan);

            $.ajax({
                url: '{{ route('cart.update') }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    itemId: itemId,
                    type: type,
                    newPlan: newPlan
                },
                success: function(response) {
                    // Refresh the cart view
                    location.reload();
                    showAlert(response.success, 'success');
                },
                error: function(xhr) {
                    showAlert(xhr.responseJSON.error, 'error');
                }
            });
            location.reload();
        });

        // SweetAlert Functions
        function showAlert(message, type) {

            Swal.fire({
                title: message,
                // text: message,
                icon: type === 'success' ? 'success' : 'info',
                timer: 1500,
                showConfirmButton: false
            });
        }
    </script>
    {{-- Alpine JS --}}
    @unless(request()->routeIs('qudrat', 'arabic-skills'))<script src="//unpkg.com/alpinejs" defer></script>@endunless
    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



</body>

</html>
