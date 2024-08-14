@extends('layouts.student')
@section('content')
    <div class="max-w-screen-xl mx-auto  p-4 space-y-8">
        <h1 class="text-4xl mt-4 font-bold text-indigo-600">دوراتي</h1>
        <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($courses as $course)
            <div class="lg:px-8">
                <x-card.student-course :course="$course" />
            </div>
            @endforeach
        </div>
    </div>
@endsection()