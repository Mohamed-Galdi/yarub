@extends('layouts.auth')
@section('title', 'تأكيد كلمة المرور')
@section('description', 'أكّد كلمة مرور حسابك للمتابعة بأمان.')

@section('content')
@if (! auth()->user()->has_local_password)
    <p class="auth-text">حسابك يستخدم Google. عيّن كلمة مرور أولاً عبر رابط الاستعادة، ثم عد لإكمال هذه الخطوة.</p>
    <a class="auth-secondary" href="{{ route('google.account') }}">إعدادات تسجيل الدخول</a>
@else
    <form class="auth-form" data-auth-form method="POST" action="{{ route('password.confirm') }}">
        @csrf
        <x-auth.field name="password" type="password" label="كلمة المرور" autocomplete="current-password" />
        <x-auth.submit label="تأكيد والمتابعة" />
    </form>
@endif
@endsection
