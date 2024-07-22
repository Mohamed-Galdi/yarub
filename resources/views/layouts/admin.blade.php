<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head dir="rtl">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>يعرب - شاشة المشرف </title>
    <link rel="icon" type="image/x-icon" href="/assets/favicon.ico">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <!-- Alpine Plugins -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/focus@3.x.x/dist/cdn.min.js"></script>
    <!-- Alpine Core -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

</head>

<body dir="rtl" class="bg-gray-100 font-hacen">

    {{-- ////////////////// sidebar mobile toggle button ////////////////// --}}
    <button id="openButton"
        class="absolute sm:hidden p-2 rounded-bl-lg  bg-gray-200 text-pr-500 hover:bg-pr-800 focus:outline-none order-1">
        <x-icons.bars class="w-6 h-6" />
    </button>


    <aside id="sidebar"
        class="fixed z-[999999] top-0 right-0 h-full w-60 bg-indigo-800 transition-transform translate-x-full sm:translate-x-0 sm:block  ">
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
        {{-- <div class="w-full h-[2px] bg-gray-100"></div> --}}
        <div class="h-full p-4 overflow-y-auto">
            <ul class="space-y-4 font-medium">
                {{-- ///////////////////////////////////// --}}
                <x-btn.admin-dashboard-item route='admin.dashboard' path='admin-dashboard'>
                    <p class="ml-3">لوحة التحكم</p>
                    <x-icons.circle-chart
                        class="w-6 h-6 {{ request()->routeIs('admin.dashboard') ? 'text-gray-200' : 'text-gray-800' }}" />
                </x-btn.admin-dashboard-item>
                {{-- ///////////////////////////////////// --}}
                <x-btn.admin-dashboard-item route='admin.students' path='admin-dashboard/students*'>
                    <p class="ml-3">الطلاب </p>
                    <x-icons.students
                        class="w-6 h-6 {{ request()->is('admin-dashboard/students*') ? 'text-gray-200' : 'text-gray-800' }}" />
                </x-btn.admin-dashboard-item>
                {{-- ///////////////////////////////////// --}}
                <x-btn.admin-dashboard-item route='admin.courses' path='admin-dashboard/courses*'>
                    <p class="ml-3">الدورات </p>
                    <x-icons.course
                        class="w-6 h-6 {{ request()->is('admin-dashboard/courses*') ? 'text-gray-200' : ' text-gray-800' }}" />
                </x-btn.admin-dashboard-item>
                {{-- ///////////////////////////////////// --}}
                <x-btn.admin-dashboard-item route='admin.lessons' path='admin-dashboard/lessons*'>
                    <p class="ml-3">الشروحات </p>
                    <x-icons.lesson
                        class="w-6 h-6 {{ request()->is('admin-dashboard/lessons*') ? 'text-gray-200' : ' text-gray-800' }}" />
                </x-btn.admin-dashboard-item>
                {{-- ///////////////////////////////////// --}}
                <x-btn.admin-dashboard-item route='admin.tests' path='admin-dashboard/test*'>
                    <p class="ml-3">الإختبارات </p>
                    <x-icons.test
                        class="w-6 h-6 {{ request()->is('admin-dashboard/tests*') ? 'text-gray-200' : ' text-gray-800' }}" />
                </x-btn.admin-dashboard-item>
                {{-- ///////////////////////////////////// --}}
                <x-btn.admin-dashboard-item route='admin.certificates' path='admin-dashboard/certificates*'>
                    <p class="ml-3"> الشواهد </p>
                    <x-icons.certificate
                        class="w-6 h-6 {{ request()->is('admin-dashboard/certificates*') ? 'text-gray-200' : ' text-gray-800' }}" />
                </x-btn.admin-dashboard-item>
                {{-- ///////////////////////////////////// --}}
                <x-btn.admin-dashboard-item route='admin.payments' path='admin-dashboard/payments*'>
                    <p class="ml-3"> المدفوعات </p>
                    <x-icons.payments
                        class="w-6 h-6 {{ request()->is('admin-dashboard/payments*') ? 'text-gray-200' : ' text-gray-800' }}" />
                </x-btn.admin-dashboard-item>
                {{-- ///////////////////////////////////// --}}
                <x-btn.admin-dashboard-item route='admin.support' path='admin-dashboard/support*'>
                    <p class="ml-3"> الدعم </p>
                    <x-icons.support
                        class="w-6 h-6 {{ request()->is('admin-dashboard/support*') ? 'text-gray-200' : ' text-gray-800' }}" />
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
                    <button type="submit" class="w-full flex justify-center items-center gap-2 ">
                        <x-icons.logout class="w-4 h-4  scale-x-[-1] " />
                        <p> الخروج</p>
                    </button>
                </form>
            </div>
        </div>

    </aside>

    <main
        class="bg-indigo-800 md:w-[calc(100%-15rem)] w-full min-h-screen md:ms-[15rem] ms-0 flex flex-col justify-center items-center  overflow-hidden">
        {{-- //////////////////// Title //////////////////// --}}
        <div class="w-full space-y-1 font-judur pt-3 mb-4 ps-16">
            <h1 class="text-4xl font-bold text-gradient-to-r  p-2 w-fit text-gray-100 ">
                {{ \App\Enums\PageTitles::getTitle(request()->path()) }}
            </h1>

        </div>

        <div class="w-full h-full bg-indigo-800  overflow-hidden rounded-tr-[3rem] ">
            <div class="w-full h-full px-8 py-12 bg-gray-100 min-h-screen">
                @yield('content')
            </div>
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
    @include('sweetalert::alert')
</body>
@stack('scripts')

</html>
