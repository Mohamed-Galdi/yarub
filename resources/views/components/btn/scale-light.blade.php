<button 
    {{ $attributes->merge(['class' => 'bg-gray-200 text-primary     cursor-pointer    rounded-md  p-2 flex justify-center items-center font-extrabold border-2 border-pr-900 shadow-xl hover:bg-gray-300 hover:scale-95 hover:shadow-none duration-300 relative ']) }}>
    @if ($slot->isEmpty())
        Click ME
    @else
        {{ $slot }}
    @endif
</button>