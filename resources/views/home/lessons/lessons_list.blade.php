@extends('layouts.guest')
@section('content')
    <div class="mx-auto max-w-screen-xl py-6 px-2 space-y-12 text-primary pb-12">
        {{-- ////////////////////////////// Title ////////////////////////////// --}}
        <h1 class="text-center lg:text-7xl md:text-5xl text-4xl ">شروحات منصة يعرب</h1>

        {{-- ////////////////////////////// Search form ////////////////////////////// --}}
        <form class="mx-8 md:mx-4 lg:mx-3 ">
            <div class="relative mb-6 w-full flex  items-center justify-start rounded-md">
                <svg class="absolute left-2 block h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" width="24"
                    height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8" class=""></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65" class=""></line>
                </svg>
                <input type="name" name="donationName"
                    class="h-12 w-full cursor-text rounded-md border border-gray-100 bg-gray-100 py-4 pr-8 pl-12 shadow-sm outline-none focus:border-pr-500 focus:ring focus:ring-pr-200 focus:ring-opacity-50"
                    placeholder="إبحث عن الشرح التي تريد ..." />
            </div>
            <div class="mt-2">
                <x-btn.scale-light class="w-24">بحث</x-btn.scale-light>
            </div>
        </form>

        {{-- ////////////////////////////// Courses list ////////////////////////////// --}}
        <div class="grid grid-cols-1  md:grid-cols-2 lg:grid-cols-3  gap-12  px-8 place-items-center">
            <x-card.guest-lesson class="w-[22rem] " :title="'مقدمة يَعرُب في التأسيس للقدرات - اللفظي'" :description="'شرح تفصيلي لقسم المفردة الشاذة ( الارتباط والاختلاف ) مع تدريبات شاملة'" :monthly-price="30"
                :yearly-price="300" />
            <x-card.guest-lesson class=" w-[22rem]" :title="' التوابع'" :description="'شرح درس التوابع من منهج الكفاية النحوية من الصف الثاني الثانوي'" :monthly-price="30"
                :yearly-price="300" />
            <x-card.guest-lesson class=" w-[22rem]" :title="' همزة  الوصل '" :description="'شرح درس همزة الوصل وكيفية كتابتها من منهج كفايات لغوية ٢ للصف الأول الثانوي'" :monthly-price="30"
                :yearly-price="300" />
            <x-card.guest-lesson class="w-[22rem] " :title="'مقدمة يَعرُب في التأسيس للقدرات - اللفظي'" :description="'شرح تفصيلي لقسم المفردة الشاذة ( الارتباط والاختلاف ) مع تدريبات شاملة'" :monthly-price="30"
                :yearly-price="300" />
            <x-card.guest-lesson class=" w-[22rem]" :title="' التوابع'" :description="'شرح درس التوابع من منهج الكفاية النحوية من الصف الثاني الثانوي'" :monthly-price="30"
                :yearly-price="300" />
            <x-card.guest-lesson class=" w-[22rem]" :title="' همزة  الوصل '" :description="'شرح درس همزة الوصل وكيفية كتابتها من منهج كفايات لغوية ٢ للصف الأول الثانوي'" :monthly-price="30"
                :yearly-price="300" />
        </div>


    </div>
@endsection()
