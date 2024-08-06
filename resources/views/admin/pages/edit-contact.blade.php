@extends('layouts.admin')
@section('content')
    <div class="flex justify-between">
        <h1 class="lg:text-4xl text-2xl text-nowrap truncate text-indigo-700 mb-4">تعديل الصفحة الرئيسية</h1>
        </h1>
        {{-- // back button --}}
        <x-btn.back route="admin.pages" />
    </div>
@endsection
