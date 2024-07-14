<a href="{{ route($route, ['id' => $id]) }}"
    {{ $attributes->merge(['class' => 'flex justify-center items-center text-white gap-2 bg-gradient-to-tr  p-2 rounded-xl border-2 border-white overflow-hidden hover:scale-[0.97] transition-all duration-300 ease-in-out cursor-pointer ']) }}>
    <p>{{ $slot }}</p>
    <x-icons.hand-pointer class="w-5 h-5 " />
</a>

