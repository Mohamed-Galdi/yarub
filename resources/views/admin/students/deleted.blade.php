@extends('layouts.admin')
@section('panel-own-title', true)
@section('content')
    <div class="flex justify-between">
        <h1 class="panel-page-title">المشتركين المحظورين</h1>
        {{-- // back button --}}
        <x-btn.back route="admin.students" />
    </div>
    <div class="flex flex-col justify-start items-start min-h-screen space-y-4">
        <div class="panel-surface">
            <livewire:deleted-students-table />
        </div>
    </div>
@endsection()
