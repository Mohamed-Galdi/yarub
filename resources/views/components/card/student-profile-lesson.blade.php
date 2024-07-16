<div
    {{ $attributes->merge(['class' => 'w-full h-fit bg-gradient-to-tr from-teal-400 to-teal-600 flex  justify-between items-center gap-4 rounded-xl p-2 overflow-hidden shadow-lg cursor-pointer hover:shadow-none hover:scale-[1.01] transition-all duration-300 ease-in-out']) }}>

    <div class="md:w-4/5 w-3/5  h-full flex flex-col justify-start items-start">
        <p class="text-white font-nitaqat text-2xl text-nowrap truncate">{{ $title }}</p>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 items-center mt-4 text-white text-center ms-4 w-full ">
            <div class="border-e-2 border-white p-2 w-full">
                <div class="flex w-full justify-center items-center gap-1 text-orange-200">
                    <p class="">مدة الإشتراك </p>
                    <x-icons.clock class="w-5 h-5 " />
                </div>
                <p> {{ $duration}} </p>
            </div>
            <div class="border-e-2 border-white p-2 w-full">
                <div class="flex w-full justify-center items-center gap-1 text-orange-200">
                    <p class="">تاريخ البدء</p>
                    <x-icons.start class="w-5 h-5 " />
                </div>
                <p> {{ $startDate }} </p>
            </div>
            <div class="border-e-2 border-white p-2 w-full">
                <div class="flex w-full justify-center items-center gap-1 text-orange-200">
                    <p class="">نهاية الإشتراك  </p>
                    <x-icons.ban class="w-5 h-5 " />
                </div>
                <p> {{ $endDate }} </p>
            </div>
            
            <div class="border-e-2 border-white p-2 w-full">
                <div class="flex w-full justify-center items-center gap-1 text-orange-200">
                    <p class="">الإختبارات </p>
                    <x-icons.test class="w-5 h-5 " />
                </div>
                <p> {{ $testCount }} </p>
            </div>
        </div>
    </div>
    <div class="md:w-1/5 w-2/5  grid place-items-center mt-6 md:mt-0">
        <x-card.radial-progress :progress="$progress" class="w-28 h-28 rounded-full" />
    </div>
</div>
