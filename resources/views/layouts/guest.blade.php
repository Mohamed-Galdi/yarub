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
                        <div class="w-8 h-8 relative">
                            <x-icons.cart class="text-sm text-pr-500 w-full h-full "></x-icons.cart>
                            <div
                                class="text-white bg-pr-500 rounded-full absolute top-0 right-0 w-4 h-4 flex justify-center items-center">
                                2</div>
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
                        class=" font-bold underline">4030408810</span>
                <p> منصة يعرب | جميع الحقوق محفوظة © 2024.</p>

                </p>
            </div>
            <div class="flex flex-col items-center gap-4 py-2 md:order-3 order-2">
                <a href="/">
                    <img src="./assets/logos/logo_white.png" class="w-24" alt="منصة يعرب">
                </a>
                <div class="flex gap-3 mb-2 sm:justify-center sm:mt-0">
                    <a href="https://youtube.com" target='_blank'
                        class="text-gray-200 hover:scale-110 hover:text-white transition-all">
                        <svg class="w-8 h-8" fill="currentColor" xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 576 512">
                            <path
                                d="M549.7 124.1c-6.3-23.7-24.8-42.3-48.3-48.6C458.8 64 288 64 288 64S117.2 64 74.6 75.5c-23.5 6.3-42 24.9-48.3 48.6-11.4 42.9-11.4 132.3-11.4 132.3s0 89.4 11.4 132.3c6.3 23.7 24.8 41.5 48.3 47.8C117.2 448 288 448 288 448s170.8 0 213.4-11.5c23.5-6.3 42-24.2 48.3-47.8 11.4-42.9 11.4-132.3 11.4-132.3s0-89.4-11.4-132.3zm-317.5 213.5V175.2l142.7 81.2-142.7 81.2z" />
                        </svg>
                    </a>
                    <a href="https://twitter.com" target='_blank'
                        class="text-gray-200 hover:scale-110 hover:text-white transition-all">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path
                                d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84" />
                        </svg>
                    </a>
                    <a href="https://www.instagram.com" target='_blank'
                        class="text-gray-200 hover:scale-110 hover:text-white transition-all">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path fill-rule="evenodd"
                                d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z"
                                clip-rule="evenodd" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </footer>
    @include('sweetalert::alert')
    @stack('scripts')


</body>

</html>
