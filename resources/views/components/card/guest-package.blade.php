 <a href="{{ route('package', $id) }}"
     {{ $attributes->merge(['class' => ' h-fit bg-gradient-to-tr from-red-800 to-red-400 rounded-xl shadow-lg p-4 space-y-4   cursor-pointer shadow-teal-600 shadow-lg hover:shadow-none hover:scale-[0.98] transition-all duration-300 ease-in-out overflow-hidden']) }}>
     <div class="w-full h-44 rounded-xl overflow-hidden">
         <x-card.course-img title="'{{ $title }}'" />
     </div>
     <div class="">
         <p class="text-white text-nowarp truncate">{{ $title }}</p>
         {{-- <p class="font-nitaqat font-bold text-sm truncate text-gray-200 ">{{ $description }}</p> --}}
         <p class="  text-slate-800 px-1 bg-slate-100 rounded-xl w-fit mt-2 text-base">{{ $type }}</p>

     </div>
     <div class="flex gap-3 justify-between items-start">
         <div class="w-1/2 text-center bg-slate-300 text-slate-800 rounded-lg">
             <p>عدد الدورات</p>
             <p>{{ $coursesCount }}</p>
         </div>
         <div class="w-1/2 text-center bg-slate-300 text-slate-800 rounded-lg">
             <p>عدد الشروحات</p>
             <p>{{ $lessonsCount }}</p>
         </div>

     </div>
     <div class="w-full">
         <div class="flex w-full justify-between items-start gap-x-4">
             @if ($typeValue === 'courses')
                 <div class="bg-slate-700 px-2 pb-2 rounded-2xl w-1/3 text-center">
                     <p class="font-nitaqat text-warning-500 font-bold text-xl">{{ $price }} ر.س </p>
                 </div>
             @else
                 <div class="bg-slate-700 px-2 pb-2 rounded-2xl w-1/2">
                     <p class="font-nitaqat text-warning-500 font-bold text-xl">{{ $monthlyPrice }} ر.س<span
                             class="text-white "> /
                             شهريا</span></p>
                 </div>
                 <div class="bg-slate-700 px-2 pb-2 rounded-2xl w-1/2">
                     <p class="font-nitaqat text-warning-500 font-bold text-xl">{{ $annualPrice }} ر.س <span
                             class="text-white "> /
                             سنويا</span></p>
                 </div>
             @endif
         </div>

     </div>
     <div class="">
         <div class="">
             <button
                 class="bg-red-500 w-full text-white border-2 border-gray-100 text-xl py-2 px-4 rounded-2xl flex gap-4 justify-center items-center 
            hover:border-gray-800 hover:bg-gray-100 hover:text-pr-500 hover:border group transition-all duration-300 ease-in-out add-to-cart "
                 data-item-id="{{ $id }}" data-type="lesson" data-title="{{ $title }}"
                 data-description="{{ $description }}" data-annual-price="{{ $annualPrice }}"
                 data-monthly-price="{{ $monthlyPrice }}">
                 <p> أضف الى السلة</p>

                 <x-icons.cart class="w-6 h-6" />
             </button>
         </div>
     </div>
 </a>
