<div
    {{ $attributes->merge(['class' => 'border hover:scale-95 duration-300 relative group cursor-pointer text-white  overflow-hidden  rounded-md bg-pr-200 p-2 flex justify-center items-center font-extrabold']) }}>
    <div
        class="absolute right-32 -top-4  group-hover:top-1 group-hover:right-2 z-10 w-40 h-40 rounded-full group-hover:scale-150 duration-500 bg-pr-600">
    </div>
    <div
        class="absolute right-2 -top-4  group-hover:top-1 group-hover:right-2 z-10 w-32 h-32 rounded-full group-hover:scale-150  duration-500 bg-pr-400">
    </div>
    <div
        class="absolute -right-12 top-4 group-hover:top-1 group-hover:right-2 z-10 w-24 h-24 rounded-full group-hover:scale-150  duration-500 bg-gray-400">
    </div>
    <div
        class="absolute right-20 -top-4 group-hover:top-1 group-hover:right-2 z-10 w-16 h-16 rounded-full group-hover:scale-150  duration-500 bg-pr-100">
    </div>
    <p class="z-10">
        @if ($slot->isEmpty())
            This is default content if the slot is empty.
        @else
            {{ $slot }}
        @endif
    </p>


</div>
