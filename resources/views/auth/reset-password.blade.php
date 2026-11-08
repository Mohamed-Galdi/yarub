@extends('layouts.auth')
@section('title', 'كلمة مرور جديدة')
@section('description', 'اختر كلمة مرور لحسابك. يمكنك استخدامها أيضاً مع حساب Google المرتبط.')

@section('content')
<form class="auth-form" data-auth-form method="POST" action="{{ route('password.store') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $request->route('token') }}">
    <x-auth.field name="email" type="email" label="البريد الإلكتروني" autocomplete="username" :value="$request->email" maxlength="255" />
    <x-auth.field name="password" type="password" label="كلمة المرور الجديدة" autocomplete="new-password" minlength="8" hint="استخدم 8 أحرف على الأقل." />
    <x-auth.field name="password_confirmation" type="password" label="تأكيد كلمة المرور" autocomplete="new-password" minlength="8" />
    <x-auth.submit label="حفظ كلمة المرور" />
</form>
<p class="auth-foot">انتهت صلاحية الرابط؟ <a href="{{ route('password.request') }}">اطلب رابطاً جديداً</a></p>
@endsection
