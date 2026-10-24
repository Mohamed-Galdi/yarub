@php
    $routeName = Route::currentRouteName();
    $isActive = str_contains($routeName, $item);
@endphp
<a data-student-nav-item href="{{ route($route) }}"
   class="p-2 2xl:w-44 xl:w-36 w-full flex items-center gap-2 justify-center xl:rounded-none rounded-md  h-full text-lg text-slate-100 focus:outline-none focus:text-white transition-all duration-300 ease-in-out group {{ $isActive ? 'bg-indigo-600' : 'bg-pr-600 hover:bg-pr-400 '}}"
   @if($isActive) aria-current="page" @endif>
    <p class="group-hover:-translate-y-2 transition-all duration-300 ease-in-out ">{{ $title }}</p>
    <div class="group-hover:-translate-y-2 transition-all duration-300 ease-in-out ">
        {{ $slot }}
    </div>
</a>