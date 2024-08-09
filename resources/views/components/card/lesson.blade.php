<div
    {{ $attributes->merge([
        'class' => 'bg-gray-200 flex flex-col items-start justify-start border-2 border-pr-500 overflow-hidden
                rounded-xl shadow-lg shadow-pr-400 hover:shadow-sm cursor-pointer transition-all duration-300 ease-in-out hover:scale-[0.99]
                relative  h-96',
    ]) }}>
    <div class="w-full h-1/2">
        <x-card.course-img :title="$title" />
    </div>
    <div class="px-6 pt-2 w-full">
        <p class="text-xl font-bold text-pr-500 mb-1 truncate">{{ $title }}</p>
        <p class="font-nitaqat font-bold text-sm truncate ">{{ $description }}</p>
        <div class="mt-2 mb-2 flex justify-between items-center ">
            <div class="bg-orange-400 px-2 pb-2 rounded-2xl w-fit">
                <p class="font-nitaqat font-bold text-xl">{{ $monthlyPrice }} ر.س شهريا</p>
            </div>
            <div class="bg-orange-300 px-2 pb-2 rounded-2xl w-fit">
                <p class="font-nitaqat font-bold text-xl">{{ $annualPrice }} ر.س سنويا</p>
            </div>
        </div>
        <x-card.rating />
        <a href="/" class="w-full">
            <button
                class="bg-primary w-full text-white text-xl py-2 px-4 rounded-2xl flex gap-4 justify-center items-center 
            hover:border-gray-800 hover:bg-gray-100 hover:text-pr-500 hover:border group transition-all duration-300 ease-in-out ">
                <p> أضف الى السلة</p>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" class="w-6 ">
                    <path fill="" class="fill-current text-white group-hover:text-pr-500"
                        d="M0 24C0 10.7 10.7 0 24 0H69.5c22 0 41.5 12.8 50.6 32h411c26.3 0 45.5 25 38.6 50.4l-41 152.3c-8.5 31.4-37 53.3-69.5 53.3H170.7l5.4 28.5c2.2 11.3 12.1 19.5 23.6 19.5H488c13.3 0 24 10.7 24 24s-10.7 24-24 24H199.7c-34.6 0-64.3-24.6-70.7-58.5L77.4 54.5c-.7-3.8-4-6.5-7.9-6.5H24C10.7 48 0 37.3 0 24zM128 464a48 48 0 1 1 96 0 48 48 0 1 1 -96 0zm336-48a48 48 0 1 1 0 96 48 48 0 1 1 0-96z" />
                </svg>
            </button>
        </a>
    </div>

</div>
