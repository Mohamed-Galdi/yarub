<a href="{{ route($route) }}"
    {{ $attributes->merge(['class' => 'flex text-lg justify-center items-center gap-2 bg-blue-500 px-3 py-1 rounded-lg text-white hover:scale-[0.99] hover:bg-blue-600 transition-all duration-200 ease-in-out h-10']) }}>
    <p>العودة</p>
    <x-icons.arrow-down class="w-5 h-5 rotate-90" />
</a>
