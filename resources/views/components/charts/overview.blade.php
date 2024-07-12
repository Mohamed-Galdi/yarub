<div class="bg-white rounded-lg  md:w-1/4 w-full h-[12rem] py-3  ">
    <div class="flex justify-between items-end px-4">
        <div class="ms-4">
            <x-dynamic-component :component="$icon" class="w-6 h-6 {{ $color }}" />
            <p class="text-2xl   font-normal {{$color}}">{{ $title }}</p>
        </div>
        <div class="flex flex-col items-end gap-0 justify-center">
            <h5 class="leading-none text-3xl font-bold  w-full {{$color}}">{{ $value }}</h5>
            <div class="flex font-judur text-xs  {{$color}}">
                <x-icons.increase class="w-4 h-4  {{$color}}" />
                <p>{{ $trend }}</p>
            </div>
            <div>

            </div>
        </div>
    </div>
    <div id="{{ $id }}"></div>
</div>
