@props(['averagerating', 'totalreviews'])

<div class="bg-gray-600 w-fit px-2 m-2 rounded-lg bottom-6 right-2 flex items-center gap-1 text-xs">
    <div>
        @for ($i = 1; $i <= 5; $i++)
            <span class="{{ $i <= round($averagerating) ? 'text-yellow-500' : 'text-gray-200' }}">★</span>
        @endfor
    </div>
    <p class="text-white">({{ $totalreviews }} تقييمات)</p>
</div>
