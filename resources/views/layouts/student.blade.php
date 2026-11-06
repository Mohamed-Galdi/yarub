<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head dir="rtl">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>منصة يعرب التعليمية</title>
    <link rel="icon" type="image/x-icon" href="/assets/favicon.ico">
    <!-- Scripts -->
    @vite(['resources/css/app.css', request()->routeIs('student.videos.*') ? 'resources/js/videos-student.js' : (request()->routeIs('student.arabic-skills.*') ? 'resources/js/arabic-skills-student.js' : (request()->routeIs('student.qudrat.*') ? 'resources/js/qudrat-student.js' : 'resources/js/app.js'))])
    @if(request()->routeIs('student.videos.*')) @vite('resources/css/videos-student.css') @endif
    @if(request()->routeIs('student.videos.*')) @vite('resources/js/videos-player.js') @endif
    @vite('resources/css/panels.css')
    {{-- csrf token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Preline -->
    <script src="https://cdn.jsdelivr.net/npm/preline@2.4.1/dist/preline.min.js"></script>

    {{-- plyr styles cdn --}}
    <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />

    <style>
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

        /* ///////////////// show content loader ///////////////// */
        .loader {
            width: 48px;
            height: 48px;
            border: 10px solid #FFF;
            border-radius: 50%;
            position: relative;
            transform: rotate(45deg);
            box-sizing: border-box;
        }

        .loader::before {
            content: "";
            position: absolute;
            box-sizing: border-box;
            inset: -10px;
            border-radius: 50%;
            border: 10px solid #6875f5;
            animation: prixClipFix 2s infinite linear;
        }

        @keyframes prixClipFix {
            0% {
                clip-path: polygon(50% 50%, 0 0, 0 0, 0 0, 0 0, 0 0)
            }

            25% {
                clip-path: polygon(50% 50%, 0 0, 100% 0, 100% 0, 100% 0, 100% 0)
            }

            50% {
                clip-path: polygon(50% 50%, 0 0, 100% 0, 100% 100%, 100% 100%, 100% 100%)
            }

            75% {
                clip-path: polygon(50% 50%, 0 0, 100% 0, 100% 100%, 0 100%, 0 100%)
            }

            100% {
                clip-path: polygon(50% 50%, 0 0, 100% 0, 100% 100%, 0 100%, 0 0)
            }
        }
    </style>


</head>

<body class="yarub-panel panel-student antialiased">

    <header class="panel-topbar" data-student-navigation dir="rtl">
        <nav aria-label="التنقل في لوحة الطالب" class="panel-topbar-inner">
            <a class="panel-wordmark" href="{{ route('student.dashboard') }}">يعرب</a>
            <button type="button" class="panel-button panel-topbar-toggle hs-collapse-toggle"
                id="hs-base-header-collapse" aria-expanded="false" aria-controls="hs-base-header"
                data-hs-collapse="#hs-base-header">
                <x-icons.bars aria-hidden="true" />
                القائمة
            </button>
            <div id="hs-base-header" class="hs-collapse hidden overflow-hidden xl:block">
                <div class="panel-topbar-content">
                    <div class="panel-topbar-links">
                        <x-btn.student-nav-item :route="'student.courses.index'" :item="'courses'" :title="'الدورات'">
                            <x-icons.course class="w-5 h-5" />
                        </x-btn.student-nav-item>
                        <x-btn.student-nav-item :route="'student.lessons.index'" :item="'lessons'" :title="'الشروحات'">
                            <x-icons.lesson class="w-5 h-5" />
                        </x-btn.student-nav-item>
                        <x-btn.student-nav-item :route="'student.tests.index'" :item="'tests'" :title="'الإختبارات'">
                            <x-icons.test class="w-5 h-5" />
                        </x-btn.student-nav-item>
                        <x-btn.student-nav-item :route="'student.certificates.index'" :item="'certificates'" :title="'الشهادات'">
                            <x-icons.certificate class="w-5 h-5" />
                        </x-btn.student-nav-item>
                        <x-btn.student-nav-item :route="'student.qudrat.index'" :item="'qudrat'" :title="'القدرات'">
                            <x-icons.square-check class="w-5 h-5" />
                        </x-btn.student-nav-item>
                        <x-btn.student-nav-item :route="'student.arabic-skills.index'" :item="'arabic-skills'" :title="'مهارات اللغة العربية'">
                            <x-icons.text class="w-5 h-5" />
                        </x-btn.student-nav-item>
                        <x-btn.student-nav-item :route="'student.videos.index'" :item="'videos'" :title="'الفيديوهات'">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="m10 8 6 4-6 4Z"/></svg>
                        </x-btn.student-nav-item>
                    </div>
                    <div class="panel-topbar-tools">
                        <a class="panel-topbar-account" href="{{ route('student.account.index') }}"
                            @if(request()->routeIs('student.account.index')) aria-current="page" @endif>
                            @if(Auth::user()->avatar)
                                <img src="{{ asset(Auth::user()->avatar) }}" alt="" />
                            @else
                                <x-icons.user aria-hidden="true" />
                            @endif
                            <span class="panel-account-text">
                                <span dir="auto">{{ Auth::user()->name }}</span>
                                <small dir="ltr">{{ Auth::user()->email }}</small>
                            </span>
                        </a>
                        <a class="panel-topbar-icon" aria-label="الدعم" href="{{ route('student.support.index') }}"
                            @if(request()->routeIs('student.support.*', 'student.conversations.*')) aria-current="page" @endif>
                            <x-icons.email aria-hidden="true" />
                        </a>
                        <a class="panel-topbar-icon" aria-label="الرئيسية" href="{{ route('home') }}">
                            <x-icons.home aria-hidden="true" />
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="panel-topbar-icon" type="submit" aria-label="تسجيل الخروج">
                                <x-icons.logout aria-hidden="true" />
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <main id="panel-content" dir="rtl" @class([
        'panel-legacy' => !request()->routeIs('student.qudrat.*', 'student.arabic-skills.*', 'student.videos.*'),
    ])>
        @yield('content')
    </main>
    <footer dir="rtl" class="panel-student-footer">
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
                        <a href="{{ route('qudrat') }}" class="hover:underline">
                            القدرات
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('arabic-skills') }}" class="hover:underline">
                            مهارات اللغة العربية
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
    @unless(request()->routeIs('student.qudrat.*', 'student.arabic-skills.*', 'student.videos.*'))<script src="//unpkg.com/alpinejs" defer></script>@endunless
    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>
