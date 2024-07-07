<div
    {{ $attributes->merge(['class' => 'bg-pr-500 text-white     cursor-pointer    rounded-md  p-2 flex justify-center items-center font-extrabold border-2 border-pr-900 shadow-xl hover:bg-pr-800 hover:scale-95 hover:shadow-none duration-300 relative ']) }}>
    @if ($slot->isEmpty())
        Click ME
    @else
        {{ $slot }}
    @endif
</div>
