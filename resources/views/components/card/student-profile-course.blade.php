<div
    {{ $attributes->merge(['class' => 'w-1/2 h-fit bg-gradient-to-tr from-indigo-400 to-indigo-600 flex  justify-between items-center gap-4 rounded-xl p-2 overflow-hidden shadow-lg cursor-pointer hover:shadow-none hover:scale-[1.01] transition-all duration-300 ease-in-out']) }}>

    <div class="md:w-4/5 w-3/5  h-full flex flex-col justify-start items-start">
        <p class="text-white font-nitaqat text-2xl text-nowrap truncate">{{ $title }}</p>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 items-center mt-4 text-white text-center ms-4 w-full ">
            <div class="border-e-2 border-white p-2 md:w-full">
                <div class="flex w-full justify-center items-center gap-1 text-orange-200">
                    <p class="">تاريخ البدء</p>
                    <x-icons.start class="w-5 h-5 " />
                </div>
                <p> {{ $startDate }} </p>
            </div>
           
            
            
        </div>
    </div>
</div>
