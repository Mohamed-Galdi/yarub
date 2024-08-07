@extends('layouts.admin')
@section('content')
    <div class="grid lg:grid-cols-3 md:grid-cols-2 grid-cols-1 gap-3">
        <a href="{{ route('admin.pages.edit-home') }}"
            class=" h-36 bg-indigo-400 rounded-lg py-2 px-8 border-2 shadow-md shadow-blue-300 hover:shadow-none border-white cursor-pointer flex justify-between items-center hover:bg-indigo-500 group transition-all duration-300 ease-in-out">
            <h1 class="lg:text-3xl text-2xl text-slate-700 group-hover:text-white text-nowrap truncate ">الصفحة الرئيسية</h1>
            <x-icons.home class="w-14 h-14 text-slate-700 group-hover:text-white" />
        </a>
        <a href="{{ route('admin.pages.edit-about') }}"
            class=" h-36 bg-indigo-400 rounded-lg py-2 px-8 border-2 shadow-md shadow-blue-300 hover:shadow-none border-white cursor-pointer flex justify-between items-center hover:bg-indigo-500 group transition-all duration-300 ease-in-out">
            <h1 class="lg:text-3xl text-2xl text-slate-700 group-hover:text-white text-nowrap truncate ">من نحن</h1>
            <x-icons.about class="w-14 h-14 text-slate-700 group-hover:text-white" />
        </a>
        <a href="{{ route('admin.pages.edit-contact') }}"
            class=" h-36 bg-indigo-400 rounded-lg py-2 px-8 border-2 shadow-md shadow-blue-300 hover:shadow-none border-white cursor-pointer flex justify-between items-center hover:bg-indigo-500 group transition-all duration-300 ease-in-out">
            <h1 class="lg:text-3xl text-2xl text-slate-700 group-hover:text-white text-nowrap truncate ">تواصل معنا</h1>
            <x-icons.contact class="w-14 h-14 text-slate-700 group-hover:text-white" />
        </a>
    </div>
@endsection
