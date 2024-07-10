@extends('layouts.student')
@section('content')
    <div class="w-full h-full">
        <h1>لوحة التحكم</h1>
        <div>
            <x-card.course title="555" description="الدورة الاولى" price="1000" discount="500" class="w-32"  />
        </div>
    </div>
@endsection()
