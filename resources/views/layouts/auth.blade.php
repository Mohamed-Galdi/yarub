<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') | منصة يعرب التعليمية</title>
    <link rel="icon" href="{{ asset('assets/favicon.ico') }}">
    @vite(['resources/css/auth.css', 'resources/js/auth.js'])
</head>
<body>
    <a class="auth-skip" href="#auth-content">انتقل إلى النموذج</a>
    <div class="auth-shell">
        <aside class="auth-brand" aria-label="منصة يعرب">
            <a class="auth-logo" href="{{ route('home') }}" aria-label="يعرب — الرئيسية">
                <img src="{{ asset('assets/logos/logo_white.png') }}" width="140" height="80" alt="يعرب">
            </a>
            <div class="auth-brand-copy">
                <h2>@yield('brand-title', 'العربية تجمعنا.')</h2>
                <p>@yield('brand-description', 'دورات، قدرات، ومهارات العربية. أكمل رحلتك التعليمية مع يعرب.')</p>
            </div>
            <a class="auth-home" href="{{ route('home') }}">العودة إلى الرئيسية <span aria-hidden="true">←</span></a>
        </aside>
        <main id="auth-content" class="auth-main" tabindex="-1">
            <div class="auth-content">
                <header class="auth-heading">
                    <h1>@yield('title')</h1>
                    <p>@yield('description')</p>
                </header>
                @if (session('status'))
                    <div class="auth-notice auth-notice--success" role="status">
                        {{ session('status') === 'verification-link-sent' ? 'تم إرسال رابط التحقق. تفقّد بريدك الإلكتروني والرسائل غير المرغوب فيها.' : session('status') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="auth-notice auth-notice--error" role="alert" tabindex="-1" data-error-summary>
                        <p>تعذّر إكمال الطلب. راجع التالي:</p>
                        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif
                @yield('content')
                @if (auth()->check() && auth()->user()->role === 'student' && ! request()->routeIs('google.account'))
                    <p class="auth-foot"><a href="{{ route('google.account') }}">ربط Google وإعدادات الدخول</a></p>
                @endif
            </div>
        </main>
    </div>
</body>
</html>
