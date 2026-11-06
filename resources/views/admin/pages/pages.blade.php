@extends('layouts.admin')
@section('content')
    <div class="grid lg:grid-cols-3 md:grid-cols-2 grid-cols-1 gap-3">
        <a href="{{ route('admin.pages.edit-home') }}"
            class="panel-surface panel-link-card flex items-center justify-between gap-3">
            <h2 class="panel-card-title">الصفحة الرئيسية</h2>
            <x-icons.home class="w-6 h-6 shrink-0 text-pr-500" />
        </a>
        <a href="{{ route('admin.pages.edit-about') }}"
            class="panel-surface panel-link-card flex items-center justify-between gap-3">
            <h2 class="panel-card-title">من نحن</h2>
            <x-icons.about class="w-6 h-6 shrink-0 text-pr-500" />
        </a>
        <a href="{{ route('admin.pages.edit-contact') }}"
            class="panel-surface panel-link-card flex items-center justify-between gap-3">
            <h2 class="panel-card-title">تواصل معنا</h2>
            <x-icons.contact class="w-6 h-6 shrink-0 text-pr-500" />
        </a>
    </div>
@endsection
