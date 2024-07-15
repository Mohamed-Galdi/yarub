@props(['route', 'path'])
<li class="{{  request()->is($path) ? 'bg-gray-900 text-gray-100 border-2 border-white overflow-hidden hover:bg-gray-950' : 'bg-gray-300 hover:bg-white text-gray-800 ' }} rounded-lg  text-start p-2  ">
    <a href="{{ $route ? route($route) : '#' }}" class="flex items-center justify-between w-full px-3">
        {{ $slot }}
    </a>
</li>
