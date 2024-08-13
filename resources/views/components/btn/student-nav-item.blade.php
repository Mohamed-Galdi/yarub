@php
    $routeName = Route::currentRouteName();
    $isActive = str_starts_with($routeName, $route) || $routeName === $route;
@endphp

<a href="{{ route($route) }}" 
   class="p-2 xl:w-44 lg:w-36 w-full flex items-center gap-2 justify-center lg:rounded-none rounded-md  h-full text-lg text-slate-100 focus:outline-none focus:text-white transition-all duration-300 ease-in-out group {{ $isActive ? 'bg-indigo-600' : 'bg-pr-600 hover:bg-pr-400 '}}" 
   aria-current="page">
    <p class="group-hover:-translate-y-2 transition-all duration-300 ease-in-out ">{{ $title }}</p>
    <div class="group-hover:-translate-y-2 transition-all duration-300 ease-in-out ">
        {{ $slot }}
    </div>
</a>