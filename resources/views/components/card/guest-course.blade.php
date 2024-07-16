 <div
     {{ $attributes->merge(['class' => ' h-[26rem] bg-gradient-to-tr from-indigo-800 to-indigo-400 rounded-xl shadow-lg p-4 space-y-4   cursor-pointer shadow-indigo-600 shadow-lg hover:shadow-none hover:scale-[0.98] transition-all duration-300 ease-in-out overflow-hidden']) }}>
     <div class="w-full h-1/2 rounded-xl overflow-hidden  ">
         <x-card.course-img title="'{{ $title }}'" />
     </div>
     <div class="">
         <p class="text-white text-nowarp truncate">{{ $title }}</p>
         <p class="font-nitaqat font-bold text-sm truncate text-gray-200 ">{{ $description }}</p>
     </div>
     <div class="flex w-full">
         <div class="bg-orange-400 px-2 pb-2 rounded-2xl w-fit">
             <p class="font-nitaqat font-bold text-xl">{{ $price }} ر.س</p>
         </div>
         <x-card.rating />
     </div>
     <div class="">
         <a href="/" class="w-full">
             <button
                 class="bg-indigo-500 w-full text-white border-2 border-gray-100 text-xl py-2 px-4 rounded-2xl flex gap-4 justify-center items-center 
            hover:border-gray-800 hover:bg-gray-100 hover:text-pr-500 hover:border group transition-all duration-300 ease-in-out ">
                 <p> أضف الى السلة</p>
                 <x-icons.cart class="w-6 h-6" />

             </button>
         </a>
     </div>
 </div>