<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>منصة يعرب التعليمية</title>
    <link rel="icon" type="image/x-icon" href="/assets/favicon.ico">
    @vite('resources/css/app.css')

</head>

<body>

    {{-- <div
        class="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-dots-darker bg-center bg-gray-100 dark:bg-dots-lighter dark:bg-gray-900 selection:bg-red-500 selection:text-white">
        @if (Route::has('login'))
            <div class="sm:fixed sm:top-0 sm:right-0 p-6 text-right z-10">
                @auth
                    <a href="{{ url('/dashboard') }}"
                        class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Dashboard</a>
                @else
                    <a href="{{ route('login') }}"
                        class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Log
                        in</a>

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                            class="ml-4 font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Register</a>
                    @endif
                @endauth
            </div>
        @endif
    </div> --}}
    <div class="flex justify-center items-center gap-8 m-12 text-right flex-col bg-pr-900">
        <img src="./assets/logos/logo_dark.png" alt="" class="w-32" width="100px">
        <h1 class=" text-5xl m-8 p-4 font-hacen font-bold text-primary">
            خط نطاقات تحميل </h1>
        <h1 class=" text-xl m-8 p-4 font-nitaqat font-bold">
            خط نطاقات تحميل </h1>
        {{-- <h1 class=" text-5xl m-8 p-4 ">
            خط نطاقات تحميل </h1> --}}
        <h1 class=" text-xl m-8 p-4 font-nitaqat ">
            خط نطاقات تحمي خط نطاقات تحمي خط نطاقات تحمي خط نطاقات تحمي خط نطاقات تحميل </h1>
    </div>

</body>
@include('sweetalert::alert')

</html>
