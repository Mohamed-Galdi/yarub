<div {{ $attributes->merge(['class' => 'p-0 bg-transparent border-none relative ']) }}>
    @if ($label)
    <label for="{{ $name }}" class="text-gray-800 font-judur ms-3 mb-1 font-semibold">{{ $label }} <span
            class="text-xs font-nitaqat">
            @isset($currency)
                ({{ $currency }})
            @endisset
        </span>
     </label>
    @endif
    <input type='{{ $type }}' name='{{ $name }}' placeholder='{{ $placeholder ?? '' }}' {{$required ? 'required' : ''}}
        value='{{ $value ?? '' }}'
        class="light-input bg-white px-4 py-3 outline-none text-gray-800 rounded-lg border-2 transition-colors duration-100 border-solid border-gray-300 focus:border-gray-900  focus:ring-0 w-full ">
    @error($name)
        <div class="text-red-500 text-sm -bottom-5 right-4 absolute">{{ $message }}</div>
    @enderror
</div>
