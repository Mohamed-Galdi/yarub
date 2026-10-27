@extends('layouts.guest')
@section('title', 'الفيديوهات | منصة يعرب')
@section('description', 'شروحات قصيرة في القدرات ومهارات اللغة العربية. شاهد مثالًا، ثم سجّل الدخول لاستكشاف بقية الفيديوهات.')
@section('content')
<section class="video-showcase {{ $exampleTitle === null ? 'video-showcase-empty' : '' }}" dir="rtl" lang="ar" aria-labelledby="showcase-heading">
    <div class="video-showcase-copy"><h1 id="showcase-heading">تعلّم بطريقة أوضح<br>مع فيديوهات يعرب</h1><p>شروحات قصيرة في القدرات ومهارات اللغة العربية. شاهد مثالًا، ثم سجّل الدخول لاستكشاف بقية الفيديوهات.</p>
        @if(auth()->user()?->role === 'student')<a class="vs-cta" href="{{ route('student.videos.index') }}">انتقل إلى مكتبة الفيديوهات</a>
        @elseif(auth()->user()?->role === 'admin')<a class="vs-cta" href="{{ route('admin.videos.index') }}">إدارة الفيديوهات</a><p class="vs-note">استخدم حساب طالب لاستكشاف المكتبة.</p>
        @else<a class="vs-cta" href="{{ route('student.videos.index') }}">سجّل الدخول لمشاهدة جميع الفيديوهات</a><a class="vs-register" href="{{ route('register_page') }}">إنشاء حساب</a>@endif
    </div>
    @if($exampleTitle !== null)
    <figure class="vs-example" data-public-video><div class="vs-screen"><video controls playsinline preload="none" poster="{{ route('videos.example.asset', 'cover') }}" src="{{ route('videos.example.asset', 'media') }}" aria-label="{{ $exampleTitle }}"></video></div><figcaption>{{ $exampleTitle }}</figcaption><p role="status" data-public-status></p><div role="alert" data-public-error hidden><p>تعذّر تشغيل المثال. أعد تحميل الصفحة للمحاولة مجددًا.</p><a href="{{ route('videos') }}">إعادة المحاولة</a></div></figure>
    @endif
</section>
@endsection
