<div class="bg-gray-600  w-fit px-2 m-2 rounded-lg  bottom-6 right-2 flex items-center gap-1">
    {{-- @if ($trip->ratings->count() != 0) --}}
        <div>
            @for ($i = 1; $i <= 5; $i++)
                @if ($i <= 5)
                    <span class="text-yellow-500 text-base">★</span>
                @else
                    <span class="text-gray-200 text-xs">★</span>
                @endif
            @endfor
        </div>
    {{-- @endif --}}
    <p class="text-white text-xs">({{ 8 }} تقييمات)</p>
</div>
