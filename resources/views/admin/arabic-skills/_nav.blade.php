<nav class="qudrat-tabs" aria-label="إدارة مهارات اللغة العربية">
    <a href="{{ route('admin.arabic-skills.index') }}" @if(request()->routeIs('admin.arabic-skills.index')) aria-current="page" @endif>البنوك والنماذج</a>
    <a href="{{ route('admin.arabic-skills.questions.index') }}" @if(request()->routeIs('admin.arabic-skills.questions.*')) aria-current="page" @endif>الأسئلة</a>
    <a href="{{ route('admin.arabic-skills.sessions.index') }}" @if(request()->routeIs('admin.arabic-skills.sessions.*')) aria-current="page" @endif>تدريبات الطلاب</a>
</nav>
@if(session('arabic_status'))<p class="qudrat-notice" role="status">{{ session('arabic_status') }}</p>@endif
@if($errors->any())
    <div class="qudrat-errors" role="alert" tabindex="-1"><p>تعذّر الحفظ أو البحث. راجع الحقول التالية:</p><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
