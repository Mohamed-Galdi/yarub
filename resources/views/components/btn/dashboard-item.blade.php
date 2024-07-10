@props(['route'])

<li class="{{ request()->routeIs($route) ? 'bg-pr-500 ' : 'bg-gray-500 hover:bg-pr-300 ' }} rounded-lg  text-start p-2 text-gray-100 ">
    <a href="{{ $route ? route($route) : '#' }}" class="flex items-center justify-between w-full px-3">
        {{ $slot }}
    </a>
</li>
