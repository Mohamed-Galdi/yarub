<div {{ $attributes->merge(['class' => 'p-0 bg-transparent border-none relative ']) }}>
    <label for="{{ $name }}" class="text-gray-800 font-judur ms-3 mb-1 font-semibold">{{ $label }}</label>
    <textarea name='{{ $name }}' rows="5"
        class="bg-white px-4 py-3 outline-none text-gray-600 rounded-lg border-2 transition-colors duration-100 border-solid border-gray-300 focus:border-gray-900  focus:ring-0 w-full " >
    {{$placeholder}}
    </textarea>
    @error($name)
    <div class="text-red-500 text-sm -bottom-5 absolute">{{ $message }}</div>
@enderror
</div>
