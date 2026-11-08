@extends('layouts.auth')
@section('title', 'نسيت كلمة المرور؟')
@section('description', 'أدخل بريد حسابك لنرسل لك رابط تعيين كلمة مرور جديدة.')

@section('content')
<form class="auth-form" data-auth-form method="POST" action="{{ route('password.email') }}">
    @csrf
    <x-auth.field name="email" type="email" label="البريد الإلكتروني" autocomplete="email" maxlength="255" />
    <x-auth.submit label="إرسال رابط الاستعادة" />
</form>
<p class="auth-foot"><a href="{{ route('login') }}">العودة إلى تسجيل الدخول</a></p>
@endsection
