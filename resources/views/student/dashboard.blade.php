<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Studnets Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-700  ">
    <div class="w-screen h-screen flex flex-col justify-end items-center gap-4  ">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button>
                <x-btn.scale-light class="font-hacen space-x-3">
                    <x-icons.logout class="w-4 h-4 scale-x-[-1]" />
                    <p>تسجيل الخروج</p>
                </x-btn.scale-light>
            </button>
        </form>

        <h1 class="font-hacen text-6xl text-white"> ! ...لوحة تحكم الطلاب قيد التطوير ⌛</h1>
        <img src="{{ asset('assets/images/dev.svg') }}" alt="logo" class="w-1/2">
    </div>
</body>

</html>
