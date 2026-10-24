<li>
    <a href="{{ route($route) }}" @if(request()->routeIs($route)) aria-current="page" @endif
        class="block py-2 px-3 xl:p-0 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-primary {{ request()->routeIs($route) ? 'xl:text-primary text-white bg-primary rounded xl:bg-transparent' : 'text-slate-600 rounded hover:bg-gray-100 xl:hover:bg-transparent xl:hover:text-primary' }}">{{ $title }}</a>
</li>
