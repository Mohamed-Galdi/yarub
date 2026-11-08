@extends('layouts.auth')
@section('title', 'ربط حساب Google')
@section('description', 'أدر طرق تسجيل الدخول إلى حساب الطالب.')

@section('content')
@if ($linked)
    <div class="auth-notice auth-notice--success">حساب Google مرتبط. يمكنك استخدامه لتسجيل الدخول إلى يعرب.</div>
@elseif (auth()->user()->has_local_password)
    <p class="auth-text">أكّد كلمة المرور، ثم اختر حساب Google الذي يستخدم البريد <bdi>{{ auth()->user()->email }}</bdi>. لن تُستبدل كلمة مرورك.</p>
    <form class="auth-form" data-auth-form method="POST" action="{{ route('google.link') }}">
        @csrf
        <x-auth.field name="current_password" type="password" label="كلمة المرور الحالية" autocomplete="current-password" />
        <x-auth.submit label="تأكيد وربط Google" />
    </form>
@endif
@if (! auth()->user()->has_local_password)
    <p class="auth-text">لا توجد كلمة مرور محلية لحسابك. أرسل رابطاً إلى بريدك لتعيين كلمة مرور اختيارية.</p>
@endif
<form class="auth-form auth-password-email" data-auth-form method="POST" action="{{ route('student.password.email') }}">
    @csrf
    <x-auth.submit label="إرسال رابط تعيين كلمة المرور" secondary />
</form>
<p class="auth-foot"><a href="{{ route('student.courses.index') }}">العودة إلى حسابي</a></p>
@endsection
