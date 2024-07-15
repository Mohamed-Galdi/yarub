@extends('layouts.admin')
@section('content')
    <div class="flex flex-col justify-start items-start min-h-screen space-y-6">
        
        <div class="bg-white rounded-lg border border-gray-300 p-4 w-full">
            <livewire:students-table />
        </div>
    </div>
@endsection()
