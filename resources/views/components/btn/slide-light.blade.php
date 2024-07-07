
     {{-- <button
            class="relative px-8 py-2 rounded-md bg-white hover:text-white isolation-auto z-10 border-2 border-primary before:absolute before:w-full before:transition-all before:duration-700 before:hover:w-full before:-left-full before:hover:left-0 before:rounded-full before:bg-pr-500 before:-z-10 before:aspect-square before:hover:scale-150 overflow-hidden before:hover:duration-700 transition-all duration-700">
         {{ $text }}
        </button> --}}


<button {{ $attributes->merge(['class' => 'relative px-8 py-2 rounded-md bg-white hover:text-white isolation-auto z-10 border-2 border-primary before:absolute before:w-full before:transition-all before:duration-700 before:hover:w-full before:-left-full before:hover:left-0 before:rounded-full before:bg-pr-500 before:-z-10 before:aspect-square before:hover:scale-150 overflow-hidden before:hover:duration-700 transition-all duration-700']) }}>
    @if ($slot->isEmpty())
        This is default content if the slot is empty.
    @else
        {{ $slot}}
    @endif
</button>   
