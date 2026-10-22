<div class="qudrat-heading">
    <div><h1>{{ $heading }}</h1>@isset($description)<p>{{ $description }}</p>@endisset</div>
    @unless(request()->routeIs('student.qudrat.index'))<a href="{{ route('student.qudrat.index') }}" class="qudrat-button qudrat-secondary">أقسام القدرات</a>@endunless
</div>
@if($errors->any())<div class="qudrat-errors" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
