@extends('layouts.admin')
@section('content')
    <div class="flex justify-between">
        <h1 class="lg:text-4xl text-2xl text-nowrap truncate text-indigo-700 mb-4">المشتركين المحظورين</h1>
        {{-- // back button --}}
        <x-btn.back route="admin.students" />
    </div>
    <div class="flex flex-col justify-start items-start min-h-screen space-y-4">
        <div class="bg-white rounded-lg border border-gray-300 p-4 w-full">
            <livewire:deleted-students-table />
        </div>
    </div>
@endsection()
