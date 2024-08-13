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
    {{-- csrf token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Preline -->
    <script src="https://cdn.jsdelivr.net/npm/preline@2.4.1/dist/preline.min.js"></script>


</head>

<body class="font-hacen antialiased">

    <header class="flex flex-wrap lg:justify-start lg:flex-nowrap z-50 w-full bg-pr-600 h-16 ">
        <nav dir="rtl"
            class="relative h-full max-w-[85rem] w-full lg:flex lg:items-center lg:justify-between lg:gap-3 mx-auto px-4 sm:px-6 lg:px-8 ">
            <!-- Logo w/ Collapse Button -->
            <div class="flex items-center justify-between h-full my-auto">
                <!-- Collapse Button -->
                <div class="lg:hidden">
                    <button type="button"
                        class="hs-collapse-toggle relative size-9 flex justify-center items-center text-sm font-semibold rounded-lg border border-white/50 text-white hover:bg-white/10 focus:outline-none focus:bg-white/10 disabled:opacity-50 disabled:pointer-events-none"
                        id="hs-base-header-collapse" aria-expanded="false" aria-controls="hs-base-header"
                        aria-label="Toggle navigation" data-hs-collapse="#hs-base-header">
                        <svg class="hs-collapse-open:hidden size-4" xmlns="http://www.w3.org/2000/svg" width="24"
                            height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <line x1="3" x2="21" y1="6" y2="6" />
                            <line x1="3" x2="21" y1="12" y2="12" />
                            <line x1="3" x2="21" y1="18" y2="18" />
                        </svg>
                        <svg class="hs-collapse-open:block shrink-0 hidden size-4" xmlns="http://www.w3.org/2000/svg"
                            width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18" />
                            <path d="m6 6 12 12" />
                        </svg>
                        <span class="sr-only">Toggle navigation</span>
                    </button>
                </div>
                <!-- End Collapse Button -->
            </div>
            <!-- End Logo w/ Collapse Button -->

            <!-- Collapse -->
            <div id="hs-base-header"
                class="hs-collapse hidden overflow-hidden transition-all duration-300 basis-full grow lg:block"
                aria-labelledby="hs-base-header-collapse">
                <div
                    class="overflow-hidden overflow-y-auto  flex lg:flex-row flex-col  lg:h-16 justify-between lg:gap-0 gap-4 lg:rounded-none rounded-md lg:p-0 p-2 lg:bg-pr-600 bg-pr-300 items-stretch [&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-track]:bg-gray-100 [&::-webkit-scrollbar-thumb]:bg-gray-300">
                    <div
                        class=" lg:py-0 h-full flex flex-col lg:flex-row lg:items-center lg:justify-end gap-0.5 lg:gap-1">
                        <x-btn.student-nav-item :route="'student.courses.index'" :title="'الدروس'">
                            <x-icons.course class="w-5 h-5 text-white" />
                            <!-- Replace with your actual icon component -->
                        </x-btn.student-nav-item>
                        <x-btn.student-nav-item :route="'student.lessons.index'" :title="'الشروحات'">
                            <x-icons.lesson class="w-5 h-5 text-white" />
                            <!-- Replace with your actual icon component -->
                        </x-btn.student-nav-item>
                        <x-btn.student-nav-item :route="'student.tests.index'" :title="'الإختبارات'">
                            <x-icons.test class="w-5 h-5 text-white" /> <!-- Replace with your actual icon component -->
                        </x-btn.student-nav-item>
                        <x-btn.student-nav-item :route="'student.certificates.index'" :title="'الشواهد'">
                            <x-icons.certificate class="w-5 h-5 text-white" />
                            <!-- Replace with your actual icon component -->
                        </x-btn.student-nav-item>
                    </div>
                    <div class="my-auto">
                        <a href="{{ route('student.account.index') }}"
                            class="flex justify-start items-center gap-2 bg-slate-200 rounded-xl px-3 shadow-md shadow-blue-500 hover:shadow-none hover:scale-[0.99] transition-all duration-300 ease-in-out ">
                            <img src="{{ asset(Auth::user()->avatar) }}" class="w-10 h-10 rounded-full" />
                            <div>
                                <p>{{ Auth::user()->name }}</p>
                                <p>{{ Auth::user()->email }}</p>
                            </div>
                        </a>
                    </div>

                    <div
                        class="flex px-2 items-center lg:w-fit lg:mb-0 mb-4 lg:mt-0 mt-2 w-full justify-center gap-2 lg:gap-4 me-8">
                        <a href="{{ route('student.support.index') }}"
                            class="hover:-translate-y-1 hover:scale-[1.2]  group transition-all duration-300 ease-in-out ">
                            <x-icons.email
                                class="w-6 h-6 text-white group-hover:text-indigo-400 transition-all duration-300 ease-in-out border-b-2 border-white group-hover:pb-2 pb-1" />
                        </a>
                        <a href="{{ route('home') }}"
                            class="hover:-translate-y-1 hover:scale-[1.2]  group transition-all duration-300 ease-in-out ">
                            <x-icons.home
                                class="w-6 h-6 text-white group-hover:text-indigo-400 transition-all duration-300 ease-in-out border-b-2 border-white group-hover:pb-2 pb-1 " />
                        </a>
                        <form method="POST" action="{{ route('logout') }}"
                            class="hover:-translate-y-1 hover:scale-[1.2] group transition-all duration-300 ease-in-out mt-[0.4rem]">
                            @csrf
                            <button type="submit">
                                <x-icons.logout
                                    class="w-6 h-6 text-white group-hover:text-indigo-400 transition-all duration-300 ease-in-out border-b-2 border-white group-hover:pb-2 pb-1 scale-x-[-1]" />
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <!-- End Collapse -->
        </nav>
    </header>


    <main dir="rtl" class="min-h-[calc(100vh-6rem)] bg-slate-200">
        @yield('content')
    </main>
    <footer class="bg-pr-800 text-white h-40 w-full">
        <div class="mx-auto max-w-screen-xl flex flex-col items-center justify-center h-full py-4 px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl mb-4">
                روابط سريعة
            </h2>
            <div dir="rtl">
                <ul class="flex flex-wrap gap-4">
                    <li>
                        <a href="{{ route('student.account.index') }}" class="hover:underline">
                            حسابي
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('home') }}" class="hover:underline">
                            الرئيسية
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('courses') }}" class="hover:underline">
                            الدورات
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('lessons') }}" class="hover:underline">
                            الشروحات
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('student.support.index') }}" class="hover:underline">
                            الدعم
                        </a>
                    </li>
                </ul>
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
    {{-- Alpine JS --}}
    <script src="//unpkg.com/alpinejs" defer></script>
    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>




</body>

</html>
