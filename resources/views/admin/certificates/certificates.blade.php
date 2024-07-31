@extends('layouts.admin')
@section('content')
    <div class="flex flex-col justify-start items-start min-h-screen space-y-6">
        <div>
            <x-btn.add route="admin.certificates.create"> منح شهادة جديدة </x-btn.add>
        </div>

        <div class="w-full">
            <livewire:certificates-table/>
        </div>

    </div>
@endsection()
