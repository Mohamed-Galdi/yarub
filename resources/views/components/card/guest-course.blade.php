 <a href="{{ route('course', $id) }}"
     {{ $attributes->merge(['class' => ' h-fit bg-gradient-to-tr from-indigo-800 to-indigo-400 rounded-xl shadow-lg p-4 space-y-4   cursor-pointer shadow-indigo-600 shadow-lg hover:shadow-none hover:scale-[0.98] transition-all duration-300 ease-in-out overflow-hidden']) }}>
     <div class="w-full h-44 rounded-xl overflow-hidden  ">
         <x-card.course-img title="'{{ $title }}'" />
     </div>
     <div class="">
         <p class="text-white text-nowarp truncate">{{ $title }}</p>
         {{-- <p class="font-nitaqat font-bold text-sm truncate text-gray-200 ">{{ $description }}</p> --}}
         @if ($contentType === 'live_session')
             <div class="flex justify-start items-center gap-2 text-slate-100 px-1 py-[2px]  bg-red-500 rounded-xl w-fit mt-2 text-base">
                <p>حصة مباشرة</p>
                <x-icons.live class="w-5 h-5 ml-2" />
             </div>
         @else
             <p class="  text-slate-800 px-1 bg-slate-100 rounded-xl w-fit mt-2 text-base">{{ $type }}</p>
         @endif

     </div>
     <div class="flex w-full">
         <div class="bg-orange-400 px-2 pb-2 rounded-2xl w-fit">
             <p class="font-nitaqat font-bold text-xl">{{ $price }} ر.س</p>
         </div>
         <x-card.rating :averagerating="$averagerating" :totalreviews="$totalreviews" />
     </div>
     <div class="">
         <button
             class="bg-indigo-500 w-full text-white border-2 border-gray-100 text-xl py-2 px-4 rounded-2xl flex gap-4 justify-center items-center 
            hover:border-gray-800 hover:bg-gray-100 hover:text-pr-500 hover:border group transition-all duration-300 ease-in-out add-to-cart"
             data-item-id="{{ $id }}" data-type="course" data-title="{{ $title }}"
             data-description="{{ $description }}" data-price="{{ $price }}">
             <p> أضف الى السلة</p>
             <x-icons.cart class="w-6 h-6" />

         </button>

     </div>
 </a>
