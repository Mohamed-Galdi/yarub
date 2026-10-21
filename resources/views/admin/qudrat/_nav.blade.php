<nav class="qudrat-tabs" aria-label="إدارة القدرات">
    <a href="{{ route('admin.qudrat.index') }}" @if(request()->routeIs('admin.qudrat.index')) aria-current="page" @endif>الأقسام</a>
    <a href="{{ route('admin.qudrat.questions.index') }}" @if(request()->routeIs('admin.qudrat.questions.*')) aria-current="page" @endif>الأسئلة</a>
    <a href="{{ route('admin.qudrat.passages.index') }}" @if(request()->routeIs('admin.qudrat.passages.*')) aria-current="page" @endif>النصوص القرائية</a>
</nav>
@if(session('qudrat_status'))
    <p class="qudrat-notice" role="status">{{ session('qudrat_status') }}</p>
@endif
@if($errors->any())
    <div class="qudrat-errors" role="alert" tabindex="-1">
        <p>تعذّر الحفظ. راجع الحقول التالية:</p>
        <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif
