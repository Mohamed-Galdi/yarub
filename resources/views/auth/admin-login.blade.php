@extends('layouts.auth')
@section('title', 'دخول المشرفين')
@section('description', 'سجّل الدخول إلى لوحة إدارة يعرب.')
@section('brand-title', 'إدارة يعرب')
@section('brand-description', 'مساحة المشرفين لإدارة المحتوى ومتابعة الطلاب.')
@section('content')
<form class="auth-form" data-auth-form method="POST" action="{{ route('admin.login') }}">
    @csrf
    <x-auth.field name="email" type="email" label="البريد الإلكتروني" autocomplete="username" maxlength="255" />
    <x-auth.field name="password" type="password" label="كلمة المرور" autocomplete="current-password" />
    <x-auth.submit label="الدخول إلى لوحة التحكم" />
</form>
@endsection
