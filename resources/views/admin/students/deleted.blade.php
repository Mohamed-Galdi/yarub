@extends('layouts.admin')
@section('content')
    <div class="flex flex-col justify-start items-start min-h-screen space-y-4">
        <div class="bg-white rounded-lg border border-gray-300 p-4 w-full">
            <livewire:deleted-students-table />
        </div>
    </div>
@endsection()