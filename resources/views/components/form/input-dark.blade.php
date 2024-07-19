
<div  {{ $attributes->merge(['class' => 'p-0 bg-transparent border-none relative ']) }}>
    <input type='{{ $type }}' name='{{ $name }}' placeholder='{{ $placeholder }}'
        class="bg-gray-800 px-4 py-3 outline-none text-white rounded-lg border-2 transition-colors duration-100 border-solid border-gray-500 focus:border-gray-400  focus:ring-0 w-full ">
    @error($name)
        <div class="text-red-500 text-sm -bottom-5 right-4 absolute">{{ $message }}</div>
    @enderror
</div>
