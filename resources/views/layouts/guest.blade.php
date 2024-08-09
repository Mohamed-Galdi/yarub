<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head dir="rtl">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>منصة يعرب التعليمية</title>
    <link rel="icon" type="image/x-icon" href="/assets/favicon.ico">
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="//unpkg.com/alpinejs" defer></script>
    {{-- csrf token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Ajax cdn --}}
    <script script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
        integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>


    <style>
        :root {
            --dark-autofill-color: #ffffff;
            --light-autofill-color: #272626;
        }

        .dark-input:-webkit-autofill,
        .dark-input:-webkit-autofill:hover,
        .dark-input:-webkit-autofill:focus,
        .dark-input:-webkit-autofill:active,
        .light-input:-webkit-autofill,
        .light-input:-webkit-autofill:hover,
        .light-input:-webkit-autofill:focus,
        .light-input:-webkit-autofill:active {
            /* common properties */
            transition: background-color 5000s ease-in-out 0s;
        }

        .dark-input:-webkit-autofill,
        .dark-input:-webkit-autofill:hover,
        .dark-input:-webkit-autofill:focus,
        .dark-input:-webkit-autofill:active {
            -webkit-text-fill-color: var(--dark-autofill-color) !important;
        }

        .light-input:-webkit-autofill,
        .light-input:-webkit-autofill:hover,
        .light-input:-webkit-autofill:focus,
        .light-input:-webkit-autofill:active {
            -webkit-text-fill-color: var(--light-autofill-color) !important;
        }

        /* For Firefox */
        input:autofill {
            background-color: transparent !important;
            color: inherit !important;
        }
    </style>



</head>

<body class="font-hacen text-xl text-gray-900 antialiased">

    <header class="mx-auto max-w-screen-xl bg-white flex justify-between items-center ">

        <nav class="bg-white   w-full z-20 top-0 start-0 border-b border-gray-200 ">
            <div dir="rtl" class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto p-2">
                <a href="/" class="flex items-center space-x-3 ">
                    <img src="./assets/logos/logo_dark.png" class="w-24" alt="منصة يعرب">
                </a>
                <div class="flex md:order-2 space-x-0 md:space-x-2 items-center gap-3  ">

                    @auth
                        @if (Auth::user()->role === 'student')
                            <a href="{{ route('student.dashboard') }}">
                                <x-btn.slide-dark
                                    class="text-base px-4 text-nowrap truncate w-full flex gap-2 items-end justify-center">
                                    <p class="align-text-bottom">حسابي </p>
                                    <x-icons.user class="w-5 h-5"></x-icons.user>
                                </x-btn.slide-dark>
                            </a>
                        @elseif(Auth::user()->role === 'admin')
                            <a href="{{ route('admin.dashboard') }}">
                                <x-btn.slide-dark
                                    class="text-base px-4 text-nowrap truncate w-full flex gap-2 items-end justify-center">
                                    <p class="align-text-bottom">المشرف </p>
                                    <x-icons.admin class="w-5 h-5"></x-icons.admin>
                                </x-btn.slide-dark>
                            </a>
                        @endif
                    @endauth


                    @guest
                        <a href="/register">
                            <x-btn.slide-dark class="text-sm md:px-4 px-2 text-nowrap truncate ">انشئ
                                حسابك</x-btn.slide-dark>
                        </a>
                        <a href="/login">
                            <x-btn.slide-light class="text-sm md:px-8 p-4  "> دخول</x-btn.slide-light>
                        </a>
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
                            <a href="{{ route('cart.index') }}">
                                <x-icons.cart class="text-sm text-pr-500 w-full h-full "></x-icons.cart>
                                <div
                                    class="text-white bg-pr-500 rounded-full absolute top-0 right-0 w-4 h-4 flex justify-center items-center cart-count">
                                    {{ $cart ? count($cart) : 0 }}
                                </div>
                            </a>
                        </div>
                    @endif

                    <button data-collapse-toggle="navbar-sticky" type="button"
                        class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-gray-100 bg-primary rounded-lg md:hidden hover:bg-gray-200 hover:text-pr-500 focus:outline-none focus:ring-2 focus:ring-gray-200 "
                        aria-controls="navbar-sticky" aria-expanded="false">
                        <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 17 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M1 1h15M1 7h15M1 13h15" />
                        </svg>
                    </button>
                </div>
                <div class="items-center justify-between hidden w-full md:flex md:w-auto md:order-1" id="navbar-sticky">
                    <ul
                        class="flex flex-col p-4 md:p-0 mt-4 font-medium border border-gray-100 rounded-lg bg-gray-50 md:space-x-8 rtl:space-x-reverse md:flex-row md:mt-0 md:border-0 md:bg-white ">
                        <x-btn.nav-item :route="'home'" :title="'الرئيسية'" />
                        <x-btn.nav-item :route="'courses'" :title="'الدورات'" />
                        <x-btn.nav-item :route="'lessons'" :title="'الشروحات'" />
                        <x-btn.nav-item :route="'about'" :title="'من نحن'" />
                        <x-btn.nav-item :route="'contact'" :title="'تواصل معنا'" />
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
                <ul class="flex text-sm gap-2">
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
                        <a href="{{ url('/lessons') }}"
                            class="hover:underline md:me-1 {{ Request::is('lessons') ? 'text-gray-100 ' : 'text-gray-400' }}">الشروحات
                        </a>
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
                    <img src="./assets/logos/logo_white.png" class="w-24" alt="منصة يعرب">
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
    <script>
        // Add to Cart
        $('.add-to-cart').click(function() {
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

</body>

</html>
