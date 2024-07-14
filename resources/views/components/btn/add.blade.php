<a href="{{ route($route) }}"
    {{ $attributes->merge(['class' => 'flex justify-center items-center text-white gap-2 bg-gradient-to-tr from-green-600 to-green-400 p-2 rounded-xl border-2 border-white overflow-hidden hover:scale-[1.03] transition-all duration-300 ease-in-out ']) }}>
    <p>{{ $slot }}</p>
    <x-icons.plus class="w-5 h-5 " />
</a>
