<div class=" col-span-3 flex flex-col items-center justify-center space-y-2">
    <p class="text-center text-4xl  text-indigo-500">{{$title }}</p>
    <p class="font-judur text-lg text-gray-500">{{$message ?? ''}}</p>
    @if ($image)
    <img src="{{ asset('assets/images/empty.png') }}" alt="لم يتم العثور على أي دورة" class="w-1/2 h-auto" />
    @endif
</div>
