@extends('layouts.auth')
@section('title', 'رابط التحقق غير صالح')
@section('description', 'قد يكون الرابط منتهي الصلاحية أو غير مكتمل. اطلب رسالة تحقق جديدة للمتابعة.')
@section('content')
<form class="auth-form" data-auth-form method="POST" action="{{ route('verification.send') }}">
    @csrf
    <x-auth.submit label="إرسال رابط تحقق جديد" />
</form>
@endsection
