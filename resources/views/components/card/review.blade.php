<div
    class="bg-gray-200 rounded-xl h-80 min-w-72 p-6 flex flex-col justify-between items-between gap-4 shadow-lg shadow-blue-400 md:m-8 me-6">
    <div class="space-y-6">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16" viewBox="0 0 448 512">
            <path fill="#396dc6"
                d="M448 296c0 66.3-53.7 120-120 120h-8c-17.7 0-32-14.3-32-32s14.3-32 32-32h8c30.9 0 56-25.1 56-56v-8H320c-35.3 0-64-28.7-64-64V160c0-35.3 28.7-64 64-64h64c35.3 0 64 28.7 64 64v32 32 72zm-256 0c0 66.3-53.7 120-120 120H64c-17.7 0-32-14.3-32-32s14.3-32 32-32h8c30.9 0 56-25.1 56-56v-8H64c-35.3 0-64-28.7-64-64V160c0-35.3 28.7-64 64-64h64c35.3 0 64 28.7 64 64v32 32 72z" />
        </svg>
        <p class="font-judur text-start text-lg">
            {{ $review }}
        </p>
    </div>
    <div class=" flex gap-2 justify-start items-start mt-8">
        <img src="{{ $userImg }}" alt="" class="w-12 h-12 rounded-full">
        <div>
            <p>{{ $userName }}</p>
            <div class="flex gap-2 justify-start items-center overflow-hidden ">
                @foreach (range(1, $stars) as $star)
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 512 512">
                        <path fill="#396dc6"
                            d="M259.3 17.8L194 150.2 47.9 171.5c-26.2 3.8-36.7 36.1-17.7 54.6l105.7 103-25 145.5c-4.5 26.3 23.2 46 46.4 33.7L288 439.6l130.7 68.7c23.2 12.2 50.9-7.4 46.4-33.7l-25-145.5 105.7-103c19-18.5 8.5-50.8-17.7-54.6L382 150.2 316.7 17.8c-11.7-23.6-45.6-23.9-57.4 0z" />
                    </svg>
                @endforeach

            </div>
        </div>
    </div>
</div>
