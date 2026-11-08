@extends('layouts.auth')
@section('title', 'تحقّق من بريدك الإلكتروني')
@section('description', 'افتح رسالة يعرب واضغط على رابط التحقق لتأكيد بريد حسابك.')

@section('content')
<p class="auth-text">البريد المسجّل: <bdi>{{ auth()->user()->email }}</bdi></p>
<form class="auth-form" data-auth-form method="POST" action="{{ route('verification.send') }}">
    @csrf
    <x-auth.submit label="إرسال رابط التحقق مجدداً" />
</form>
<p class="auth-foot">لم تصلك الرسالة؟ تفقّد الرسائل غير المرغوب فيها.</p>
<form class="auth-form" data-auth-form method="POST" action="{{ route('logout') }}">
    @csrf
    <x-auth.submit label="تسجيل الخروج" secondary />
</form>
@endsection
