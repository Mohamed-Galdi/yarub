<li>
    <a href="{{ route($route) }}"
        class="block py-2 px-3 md:p-0  {{ Request::is($route == 'home' ? '/' : $route) ? 'md:text-primary text-white bg-primary rounded md:bg-transparent ' : 'text-gray-400 rounded hover:bg-gray-100 md:hover:bg-transparent md:hover:text-primary ' }} ">{{ $title }}</a>
    </a>
</li>
