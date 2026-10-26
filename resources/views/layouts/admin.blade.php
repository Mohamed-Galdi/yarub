@php($usersRuntime = request()->routeIs('admin.students', 'admin.students.deleted', 'admin.view_student', 'admin.students.tracking.review'))
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
    @if($usersRuntime)
        @vite(['resources/css/app.css', 'resources/css/admin-users.css', 'resources/js/users-admin.js'])
        @livewireStyles
    @elseif(request()->routeIs('admin.qudrat.*', 'admin.arabic-skills.*'))
        @vite(['resources/css/app.css', 'resources/js/qudrat-admin.js'])
        @livewireStyles
    @else
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @include('flatpickr::components.style')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <!-- Alpine Plugins -->
    @unless($usersRuntime || request()->routeIs('admin.qudrat.*', 'admin.arabic-skills.*'))
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/focus@3.x.x/dist/cdn.min.js"></script>
    <!-- Alpine Core -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @endunless

    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Ajax cdn --}}
    <script script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
        integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    {{-- plyr styles cdn --}}
    <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />

    <style>
        /* Styles */
        @stack('styles');


        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background-color: #ebebeb;
            -webkit-border-radius: 10px;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            -webkit-border-radius: 10px;
            border-radius: 10px;
            background: #bdc4d5;
        }

        .sidebar::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar::-webkit-scrollbar-track {
            background-color: #ebebeb;
            -webkit-border-radius: 10px;
            border-radius: 10px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            -webkit-border-radius: 10px;
            border-radius: 10px;
            background: #25282e;
        }

        /* Style the autofill inputes */
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus,
        input:-webkit-autofill:active {
            /* text color */
            transition: background-color 5000s ease-in-out 0s;
            -webkit-text-fill-color: #6a6a6a !important;
        }

        /* Style the autofill text */
        input:-webkit-autofill {
            -webkit-text-fill-color: #6a6a6a !important;
        }

        /* For Firefox */
        input:autofill {
            background-color: transparent !important;
            color: inherit !important;
        }

        .vjs-big-play-button {
            background-color: rgba(222, 0, 0, 0.45);
            border: none;
            border-radius: 50%;
            height: 80px;
            width: 80px;
        }

        /* Custom progress bar */
        .vjs-play-progress {
            background-color: #ff0000;
        }

        /* Custom control bar */
        .vjs-control-bar {
            background-color: rgba(248, 0, 0, 0.7);
        }

        /* ////////////// plyr main color ////////////// */
        :root {
            --plyr-color-main: #6875f5;
        }
    </style>

</head>

<body dir="rtl" class="bg-gray-100 font-hacen">

    {{-- ////////////////// sidebar mobile toggle button ////////////////// --}}
    <button id="openButton" @if($usersRuntime) aria-label="فتح قائمة الإدارة" aria-controls="sidebar" aria-expanded="false" @endif
        class="absolute {{ $usersRuntime ? 'md:hidden' : 'sm:hidden' }} p-2 rounded-bl-lg bg-gray-200 text-pr-500 hover:bg-pr-800 order-1">
        <x-icons.bars class="w-6 h-6" />
    </button>


    <aside id="sidebar"
        class="fixed z-[999999] top-0 right-0 h-full w-60 bg-indigo-800 transition-transform translate-x-full {{ $usersRuntime ? 'md:translate-x-0 md:block' : 'sm:translate-x-0 sm:block' }}">
        <button id="closeButton" @if($usersRuntime) aria-label="إغلاق قائمة الإدارة" @endif
            class="{{ $usersRuntime ? 'md:hidden' : 'sm:hidden' }} p-2 rounded-bl-lg bg-gray-100 text-pr-500 hover:bg-gray-400 order-1">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
            </svg>
        </button>
        <div class="p-4 font-judur  ">
            <a href="{{ route('admin.account') }}"
                class="flex justify-start items-center gap-4 bg-gray-100 p-2 rounded-lg shadow-lg shadow-blue-500 cursor-pointer hover:shadow-none hover:scale-[0.99] transition-all duration-300 ease-in-out">
                <div class="rounded-full flex items-center justify-center text-pr-300 bg-indigo-400 w-12 h-12 ">
                    <x-icons.admin class="w-6 h-6 text-gray-100" />
                </div>
                <div>
                    <p class=" font-bold">المشرف</p>
                    <p class="text-sm font-hacen">{{ Auth::user()->email }}</p>
                </div>
            </a>
        </div>
        {{-- <div class="w-full h-[2px] bg-gray-100"></div> --}}
        <div class="h-full p-4 overflow-y-auto sidebar" dir="ltr">
            <ul class="space-y-4 font-medium pb-36 " dir="rtl">
                {{-- ///////////////////////////////////// --}}
                <x-btn.admin-dashboard-item route='admin.dashboard' path='admin-dashboard'>
                    <p class="ml-3">لوحة التحكم</p>
                    <x-icons.circle-chart
                        class="w-6 h-6 {{ request()->routeIs('admin.dashboard') ? 'text-gray-200' : 'text-gray-800' }}" />
                </x-btn.admin-dashboard-item>
                {{-- ///////////////////////////////////// --}}
                <x-btn.admin-dashboard-item route='admin.students' path='admin-dashboard/students*'>
                    <p class="ml-3">المشتركين </p>
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
                <x-btn.admin-dashboard-item route='admin.qudrat.index' path='admin-dashboard/qudrat*'>
                    <p class="ml-3">القدرات</p>
                    <x-icons.square-check class="w-6 h-6" />
                </x-btn.admin-dashboard-item>
                <x-btn.admin-dashboard-item route='admin.arabic-skills.index' path='admin-dashboard/arabic-skills*'>
                    <p class="ml-3">مهارات اللغة العربية</p>
                    <x-icons.square-check class="w-6 h-6" />
                </x-btn.admin-dashboard-item>
                {{-- ///////////////////////////////////// --}}
                <x-btn.admin-dashboard-item route='admin.certificates' path='admin-dashboard/certificates*'>
                    <p class="ml-3"> الشهادات </p>
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
                <x-btn.admin-dashboard-item route='admin.coupons' path='admin-dashboard/coupons*' class="w-full">
                    <p class="ml-3"> القسائم </p>
                    <x-icons.coupon
                        class="w-6 h-6 {{ request()->is('admin-dashboard/coupons*') ? 'text-gray-200' : ' text-gray-800' }}" />
                </x-btn.admin-dashboard-item>
                {{-- ///////////////////////////////////// --}}
                <x-btn.admin-dashboard-item route='admin.support' path='admin-dashboard/support*'>
                    <p class="ml-3"> الدعم </p>
                    <x-icons.support
                        class="w-6 h-6 {{ request()->is('admin-dashboard/support*') ? 'text-gray-200' : ' text-gray-800' }}" />
                </x-btn.admin-dashboard-item>
                {{-- ///////////////////////////////////// --}}
                {{-- messages --}}
                <x-btn.admin-dashboard-item route='messages_from_about' path='admin-dashboard/guest-messages*'>
                    <p class="ml-3"> الرسائل </p>
                    <x-icons.email
                        class="w-6 h-6 {{ request()->is('admin-dashboard/guest-messages*') ? 'text-gray-200' : ' text-gray-800' }}" />
                </x-btn.admin-dashboard-item>
                <x-btn.admin-dashboard-item route='admin.pages' path='admin-dashboard/pages*'>
                    <p class="ml-3"> محتوى الواجهات </p>
                    <x-icons.desktop
                        class="w-6 h-6 {{ request()->is('admin-dashboard/pages*') ? 'text-gray-200' : ' text-gray-800' }}" />
                </x-btn.admin-dashboard-item>
            </ul>
            {{-- side footer buttons --}}
            <div dir="rtl"
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

    <main @hasSection('compact-admin') data-users-shell @endif
        class="bg-indigo-800 md:w-[calc(100%-15rem)] w-full min-h-screen md:ms-[15rem] ms-0 flex flex-col justify-center items-center  overflow-hidden">
        {{-- //////////////////// Title //////////////////// --}}
        @unless($__env->hasSection('compact-admin'))
        <div class="w-full space-y-1 font-judur pt-3 mb-4 ps-16">
            <h1 class="text-4xl font-bold text-gradient-to-r  p-2 w-fit text-gray-100 ">
                {{ \App\Enums\PageTitles::getTitle(request()->path()) }}
            </h1>

        </div>
        @endunless

        <div class="{{ $__env->hasSection('compact-admin') ? 'w-full h-full' : 'w-full h-full bg-indigo-800 overflow-hidden rounded-tr-[3rem]' }}">
            <div class="{{ $__env->hasSection('compact-admin') ? 'users-shell-content w-full bg-gray-100 min-h-screen' : 'w-full h-full px-8 py-12 bg-gray-100 min-h-screen' }}">
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
            const sidebarBreakpoint = {{ $usersRuntime ? 768 : 640 }};
            function syncUsersSidebar() {
                @if($usersRuntime)
                const closed = window.innerWidth < sidebarBreakpoint && sidebar.classList.contains('translate-x-full');
                sidebar.inert = closed;
                openButton.setAttribute('aria-expanded', String(!closed));
                @endif
            }

            closeButton.addEventListener('click', function() {
                sidebar.classList.toggle('translate-x-full');
                syncUsersSidebar();
                @if($usersRuntime) openButton.focus(); @endif
            });
            openButton.addEventListener('click', function() {
                sidebar.classList.toggle('translate-x-full');
                syncUsersSidebar();
                @if($usersRuntime) closeButton.focus(); @endif
            });

            window.addEventListener('resize', function() {
                if (window.innerWidth >= sidebarBreakpoint) {
                    sidebar.classList.remove('translate-x-full');
                } else {
                    sidebar.classList.add('translate-x-full');
                }
                syncUsersSidebar();
            });

            // Ensure sidebar visibility is correct on page load
            if (window.innerWidth >= sidebarBreakpoint) {
                sidebar.classList.remove('translate-x-full');
            } else {
                sidebar.classList.add('translate-x-full');
            }
            syncUsersSidebar();
        });
    </script>
    @include('sweetalert::alert')
    @include('flatpickr::components.script')
    @if($usersRuntime || request()->routeIs('admin.qudrat.*', 'admin.arabic-skills.*'))
        @livewireScriptConfig
    @else
    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.4.1/dist/flowbite.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/puppeteer@22.14.0/lib/cjs/puppeteer/puppeteer.min.js"></script>
    @endif

</body>
@stack('scripts')

</html>
