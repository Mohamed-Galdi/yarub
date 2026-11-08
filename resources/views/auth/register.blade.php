@extends('layouts.auth')
@section('title', 'ابدأ رحلتك مع يعرب')
@section('description', 'أنشئ حساب طالب وابدأ التعلّم.')

@section('content')
<x-auth.google />
<form class="auth-form" data-auth-form method="POST" action="{{ route('register') }}">
    @csrf
    <div class="auth-pair">
        <x-auth.field name="first_name" label="الاسم الأول" autocomplete="given-name" maxlength="120" />
        <x-auth.field name="last_name" label="اسم العائلة" autocomplete="family-name" maxlength="120" />
    </div>
    <x-auth.field name="email" type="email" label="البريد الإلكتروني" autocomplete="email" maxlength="255" />
    <x-auth.field name="password" type="password" label="كلمة المرور" autocomplete="new-password" minlength="8" hint="استخدم 8 أحرف على الأقل." />
    <x-auth.field name="password_confirmation" type="password" label="تأكيد كلمة المرور" autocomplete="new-password" minlength="8" />
    <x-auth.submit label="إنشاء حساب" />
</form>
<p class="auth-foot">لديك حساب بالفعل؟ <a href="{{ route('login') }}">تسجيل الدخول</a></p>
@endsection
