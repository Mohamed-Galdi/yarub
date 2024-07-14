@extends('layouts.admin')
@section('content')
    <div class="flex flex-col justify-start items-start min-h-screen space-y-6">
        <div class="w-full md:py-2 py-4 space-y-1 font-judur">
            <h1
                class="text-4xl font-bold text-gradient-to-r bg-gradient-to-r from-pr-500 to-indigo-500 p-2 w-fit text-transparent bg-clip-text ">
                الطلاب</h1>
            <div class="w-full h-1 rounded-lg bg-gradient-to-r from-pr-500 to-indigo-500"></div>
        </div>
        <div class="bg-white rounded-lg border border-gray-300 p-4 w-full">
            <livewire:students-table />
        </div>
    </div>
@endsection()
