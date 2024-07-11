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

</head>

<body dir="rtl" class="bg-gray-100">
    <nav
        class="fixed top-0 z-50 w-full bg-pr-500 text-white border-b border-gray-200 py-2 md:px-24 px-4 flex justify-between">
        {{-- Mobile Toggle button --}}
        <button id="toggleButton"
            class="sm:hidden p-2 rounded bg-gray-200 text-pr-500 hover:bg-gray-400 focus:outline-none order-1">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
            </svg>
        </button>

        {{-- Profile button --}}
        <div id="dropdownHoverButton" data-dropdown-toggle="dropdownHover" data-dropdown-trigger="hover"
            class="flex items-center gap-4 order-3 md:order-3">
            <button id="dropdownHoverButton" data-dropdown-toggle="dropdownHover" data-dropdown-trigger="hover"
                type="button">
                <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center text-pr-300 ">
                    <x-icons.user class="w-6 h-6" />
                </div>
            </button>

            <!-- Dropdown menu -->
            <div id="dropdownHover" class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-44  mr-32">
                <ul class="py-2 text-sm text-gray-700 " aria-labelledby="dropdownHoverButton">
                    <li class="font-hacen ">
                        <a href="/profile" class="flex justify-between items-center px-4 py-2 hover:bg-gray-100 ">
                            <p>حسابي الشخصي</p>
                            <x-icons.user class="w-4 h-4 text-pr-500" />
                        </a>
                    </li>
                    <li class="font-hacen ">
                        <form action="{{ route('logout') }}" method="POST" class="w-full">
                            @csrf
                            <button type="submit"
                                class="flex justify-between items-center w-full px-4 py-2 hover:bg-gray-100">
                                <p>تسجيل الخروج </p>
                                <x-icons.logout class="w-4 h-4 text-pr-500 scale-x-[-1]" />
                            </button>
                        </form>
                    </li>

                </ul>
            </div>



        </div>

        {{-- Yarub Logo --}}
        <div class="order-2 md:order-1">
            <a href="/" class="flex items-center space-x-3 ">
                <img src="./assets/logos/logo_light.png" class="w-16" alt="منصة يعرب">
            </a>
        </div>
    </nav>

    <aside id="sidebar"
        class="fixed z-[9999] top-0 right-0 mt-16 h-full w-60 bg-gray-300 transition-transform translate-x-full sm:translate-x-0 sm:block shadow-lg shadow-pr-500 ">
        <div class="h-full p-4 overflow-y-auto ">
            <ul class="space-y-4 font-medium">
                <x-btn.dashboard-item route='student.dashboard'>
                    <p class="ml-3">لوحة التحكم</p>
                    <x-icons.student-dashboard class="w-6 h-6 text-gray-100" />
                </x-btn.dashboard-item>
                <x-btn.dashboard-item route='student.courses'>
                    <p class="ml-3">دوراتي </p>
                    <x-icons.course class="w-6 h-6 text-gray-100" />
                </x-btn.dashboard-item>
                <x-btn.dashboard-item route=''>
                    <p class="ml-3">شروحاتي </p>
                    <x-icons.lesson class="w-6 h-6 text-gray-100" />
                </x-btn.dashboard-item>
                <x-btn.dashboard-item route=''>
                    <p class="ml-3">إختبارات الدورات
                    </p>
                    <x-icons.test class="w-6 h-6 text-gray-100" />
                </x-btn.dashboard-item>
                <x-btn.dashboard-item route=''>
                    <p class="ml-3">إختبارات الشروحات </p>
                    <x-icons.test class="w-6 h-6 text-gray-100" />
                </x-btn.dashboard-item>
                <x-btn.dashboard-item route=''>
                    <p class="ml-3">الشواهد </p>
                    <x-icons.certificate class="w-6 h-6 text-gray-100" />
                </x-btn.dashboard-item>
                <x-btn.dashboard-item route=''>
                    <p class="ml-3">الدعم
                    </p>
                    <x-icons.support class="w-6 h-6 text-gray-100" />
                </x-btn.dashboard-item>
                <x-btn.dashboard-item route=''>
                    <p class="ml-3">إعدادات الحساب
                    </p>
                    <x-icons.settings class="w-6 h-6 text-gray-100" />
                </x-btn.dashboard-item>


            </ul>
            <div
                class="w-full h-12 bottom-16 right-0 border-t text-gray-100 bg-pr-500 border-gray-100 absolute flex justify-between items-center text-sm font-bold">
                <a href="{{ route('home') }}"
                    class="flex justify-center items-center gap-2 border-l border-gray-100 pl-2 w-1/2 cursor-pointer hover:text-indigo-400">
                    <x-icons.home class="w-4 h-4  " />
                    <p> الرئيسية</p>
                </a>
                <form action="{{ route('logout') }}" method="POST" class="w-1/2 cursor-pointer hover:text-indigo-400">
                    @csrf
                    <button type="submit" class="w-full flex justify-center items-center gap-2 " >
                            <x-icons.logout class="w-4 h-4  scale-x-[-1] " />
                            <p> الخروج</p>
                    </button>
                </form>
            </div>
        </div>
    </aside>
    <main
        class="bg-gray-200 md:w-[calc(100%-15rem)] w-full min-h-screen md:ms-[15rem] ms-0 flex justify-center items-center">
        <div class="w-full h-full p-8">
            @yield('content')
        </div>
    </main>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleButton = document.getElementById('toggleButton');
            const sidebar = document.getElementById('sidebar');

            toggleButton.addEventListener('click', function() {
                sidebar.classList.toggle('translate-x-full');
            });

            window.addEventListener('resize', function() {
                if (window.innerWidth >= 640) { // sm breakpoint in Tailwind
                    sidebar.classList.remove('translate-x-full');
                } else {
                    sidebar.classList.add('translate-x-full');
                }
            });

            // Ensure sidebar visibility is correct on page load
            if (window.innerWidth >= 640) {
                sidebar.classList.remove('translate-x-full');
            } else {
                sidebar.classList.add('translate-x-full');
            }
        });
    </script>
</body>
@include('sweetalert::alert')

</html>
