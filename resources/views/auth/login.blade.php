@extends('layouts.auth')
@section('title', 'أهلاً بعودتك')
@section('description', 'سجّل الدخول لمتابعة تعلّمك.')

@section('content')
<x-auth.google />
<form class="auth-form" data-auth-form method="POST" action="{{ route('student.login') }}">
    @csrf
    <x-auth.field name="email" type="email" label="البريد الإلكتروني" autocomplete="username" maxlength="255" />
    <x-auth.field name="password" type="password" label="كلمة المرور" autocomplete="current-password" />
    <div class="auth-options">
        <label class="auth-check"><input type="checkbox" name="remember" value="1" @checked(old('remember'))> تذكّرني</label>
        <a class="auth-link" href="{{ route('password.request') }}">نسيت كلمة المرور؟</a>
    </div>
    <x-auth.submit label="تسجيل الدخول" />
</form>
<p class="auth-foot">ليس لديك حساب؟ <a href="{{ route('register_page') }}">أنشئ حسابك</a></p>
<p class="auth-foot"><a href="{{ route('google.account') }}">ربط Google بحسابك الحالي</a></p>
@endsection
