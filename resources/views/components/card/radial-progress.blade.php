<div {{ $attributes->merge(['class' => 'relative  -rotate-[90deg]']) }}>
    <svg class="w-full h-full" viewBox="0 0 100 100">
        <!-- Background circle -->
        <circle class="text-gray-800 stroke-current" stroke-width="10" cx="50" cy="50" r="40"
            fill="transparent"></circle>
        <!-- Progress circle -->
        <circle class=" text-green-400  progress-ring__circle stroke-current" stroke-width="10" stroke-linecap="round"
            cx="50" cy="50" r="40" fill="transparent" stroke-dasharray="251.2"
            stroke-dashoffset="calc(251.2 - (251.2 * {{$progress}}) / 100)"></circle>

        <!-- Center text -->
<p class="absolute top-1/2 right-1/2 translate-x-1/2 -translate-y-1/2 text-white text-2xl font-bold rotate-90 " x="50" y="50">{{$progress}}%</p>
    </svg>
</div>
