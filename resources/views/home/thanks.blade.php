@extends('layouts.guest')
@section('content')
    <div class="w-screen h-screen bg-gradient-to-b from-pr-400 to-pr-800">
        <div class="flex flex-col items-center justify-center pt-20">
            <div class="success-animation">
                <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                    <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none" />
                    <path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8" />
                </svg>
            </div>
            <div class="w-full flex flex-col items-center justify-center mt-8 space-y-3">
                <h1 class="md:text-5xl text-2xl text-center font-bold font-judur text-white">شكرا على ثقتكم !</h1>
                <p class="md:text-2xl text-xl text-center funt-nitaqat text-white">ثمت عملية الشراء بنجاح، المرجوا الان التوجه لحسابكم </p>
                <a href="{{ route('student.dashboard')}}">
                    <x-btn.scale-light>
                        <p>حسابي</p>
                        <x-icons.user class="w-5 h-5 text-primary" />
                    </x-btn.scale-light>
                </a>
            </div>
        </div>
        <div class="firework"></div>
        <div class="firework"></div>
        <div class="firework"></div>
    </div>
@endsection()
