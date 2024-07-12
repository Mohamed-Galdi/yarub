<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head dir="rtl">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>يعرب - شاشة المشرف </title>
    <link rel="icon" type="image/x-icon" href="/assets/favicon.ico">
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

</head>

<body dir="rtl" class="bg-gray-100 font-hacen">
    
    {{-- ////////////////// sidebar mobile toggle button ////////////////// --}}
    <button id="openButton"
        class="sm:hidden p-2 rounded-bl-lg  bg-pr-500 text-gray-200 hover:bg-pr-800 focus:outline-none order-1">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
        </svg>
    </button>


    <aside id="sidebar"
        class="fixed top-0 right-0 h-full w-60 bg-indigo-800 transition-transform translate-x-full sm:translate-x-0 sm:block shadow-lg shadow-pr-500 ">
        <button id="closeButton"
            class="sm:hidden p-2 rounded-bl-lg bg-gray-100 text-pr-500 hover:bg-gray-400 focus:outline-none order-1">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
            </svg>
        </button>
        <div class="p-4 font-judur  ">
            <div
                class="flex justify-start items-center gap-4 bg-gray-100 p-2 rounded-lg shadow-lg shadow-blue-500 cursor-pointer hover:shadow-none hover:scale-[0.99] transition-all duration-300 ease-in-out">
                <div class="rounded-full flex items-center justify-center text-pr-300 bg-indigo-400 w-12 h-12 ">
                    <x-icons.admin class="w-6 h-6 text-gray-100" />
                </div>
                <div>
                    <p class=" font-bold">المشرف</p>
                    <p class="text-sm font-hacen">{{ Auth::user()->email }}</p>
                </div>
            </div>
        </div>
        <div class="w-full h-[2px] bg-gray-100"></div>
        <div class="h-full p-4 overflow-y-auto">
            <ul class="space-y-4 font-medium">
                {{-- ///////////////////////////////////// --}}
                <x-btn.admin-dashboard-item route='admin.dashboard'>
                    <p class="ml-3">لوحة التحكم</p>
                    <x-icons.circle-chart
                        class="w-6 h-6 {{ request()->routeIs('admin.dashboard') ? 'text-gray-200' : ' text-gray-800' }}" />
                </x-btn.admin-dashboard-item>
                {{-- ///////////////////////////////////// --}}
                <x-btn.admin-dashboard-item route='admin.students'>
                    <p class="ml-3">الطلاب </p>
                    <x-icons.students
                        class="w-6 h-6 {{ request()->routeIs('admin.students') ? 'text-gray-200' : ' text-gray-800' }}" />
                </x-btn.admin-dashboard-item>
                {{-- ///////////////////////////////////// --}}
                <x-btn.admin-dashboard-item route='admin.courses'>
                    <p class="ml-3">الدورات </p>
                    <x-icons.course
                        class="w-6 h-6 {{ request()->routeIs('admin.courses') ? 'text-gray-200' : ' text-gray-800' }}" />
                </x-btn.admin-dashboard-item>
                {{-- ///////////////////////////////////// --}}
                <x-btn.admin-dashboard-item route='admin.lessons'>
                    <p class="ml-3">الشروحات </p>
                    <x-icons.lesson
                        class="w-6 h-6 {{ request()->routeIs('admin.lessons') ? 'text-gray-200' : ' text-gray-800' }}" />
                </x-btn.admin-dashboard-item>
                {{-- ///////////////////////////////////// --}}
                <x-btn.admin-dashboard-item route='admin.tests'>
                    <p class="ml-3">الإختبارات </p>
                    <x-icons.test
                        class="w-6 h-6 {{ request()->routeIs('admin.tests') ? 'text-gray-200' : ' text-gray-800' }}" />
                </x-btn.admin-dashboard-item>
                {{-- ///////////////////////////////////// --}}
                <x-btn.admin-dashboard-item route='admin.certificates'>
                    <p class="ml-3"> الشواهد </p>
                    <x-icons.certificate
                        class="w-6 h-6 {{ request()->routeIs('admin.certificates') ? 'text-gray-200' : ' text-gray-800' }}" />
                </x-btn.admin-dashboard-item>
                {{-- ///////////////////////////////////// --}}
                <x-btn.admin-dashboard-item route='admin.payments'>
                    <p class="ml-3"> المدفوعات </p>
                    <x-icons.payments
                        class="w-6 h-6 {{ request()->routeIs('admin.payments') ? 'text-gray-200' : ' text-gray-800' }}" />
                </x-btn.admin-dashboard-item>
                {{-- ///////////////////////////////////// --}}
                <x-btn.admin-dashboard-item route='admin.support'>
                    <p class="ml-3"> الدعم </p>
                    <x-icons.support
                        class="w-6 h-6 {{ request()->routeIs('admin.support') ? 'text-gray-200' : ' text-gray-800' }}" />
                </x-btn.admin-dashboard-item>
                {{-- ///////////////////////////////////// --}}

            </ul>
            <div
                class="w-full h-12 right-0 border-t text-gray-100 bg-indigo-800 border-gray-100 bottom-0 absolute flex justify-between items-center text-sm font-bold">
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
        class="bg-gray-100 md:w-[calc(100%-15rem)] w-full min-h-screen md:ms-[15rem] ms-0 flex justify-center items-center">
        <div class="w-full h-full p-8">
            @yield('content')
        </div>
    </main>

    {{-- ////////////////// sidebar mobile toggle button ////////////////// --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const openButton = document.getElementById('openButton');
            const closeButton = document.getElementById('closeButton');
            const sidebar = document.getElementById('sidebar');

            closeButton.addEventListener('click', function() {
                sidebar.classList.toggle('translate-x-full');
            });
            openButton.addEventListener('click', function() {
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
