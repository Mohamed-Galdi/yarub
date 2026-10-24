<div class="qudrat-heading">
    <div><h1 tabindex="-1" data-arabic-heading>{{ $heading }}</h1>@isset($description)<p>{{ $description }}</p>@endisset</div>
    @unless(request()->routeIs('student.arabic-skills.index'))<a href="{{ route('student.arabic-skills.index') }}" class="qudrat-button qudrat-secondary">مهارات اللغة العربية</a>@endunless
</div>
@if($errors->any())<div class="qudrat-errors" role="alert" tabindex="-1"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@if(session('notice'))<p class="qudrat-notice" role="status">{{ session('notice') }}</p>@endif
