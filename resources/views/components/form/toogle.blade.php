<label for="toggleSuccess" {{ $attributes->merge(['class' => 'flex flex-col cursor-pointer ']) }}>
    <input name='{{ $name }}' id="toggleSuccess" type="checkbox" class="peer sr-only" role="switch" vlaue="{{ $value }}"
        @if ($value) checked @endif  />
    <span
        class="trancking-wide text-lg font-hacen  peer-checked:text-black peer-disabled:cursor-not-allowed peer-disabled:opacity-70 ">{{ $label }}</span>
    <div class="relative h-6 w-11 after:h-5 after:w-5 peer-checked:after:translate-x-5 rounded-full border border-slate-300 bg-slate-100 after:absolute after:bottom-0 after:left-[0.0625rem] after:top-0 after:my-auto after:rounded-full after:bg-slate-700 after:transition-all after:content-[''] peer-checked:bg-green-600 peer-checked:after:bg-white peer-focus:outline peer-focus:outline-2 peer-focus:outline-offset-2 peer-focus:outline-slate-800 peer-focus:peer-checked:outline-green-600 peer-active:outline-offset-0 peer-disabled:cursor-not-allowed peer-disabled:opacity-70 "
        aria-hidden="true"></div>
</label>
