 <div
     {{ $attributes->merge(['class' => 'w-full h-fit bg-gradient-to-tr rounded-xl shadow-lg p-4 space-y-4' . ($active ? ' from-red-800 to-red-400' : ' from-gray-800 to-gray-400')]) }}>
     <div class="w-full h-44 rounded-xl overflow-hidden">
         <x-card.course-img title="'{{ $title }}'" class />
     </div>
     <div>
         <div class="flex justify-start items-center gap-2">
             @if ($active)
                 <x-card.live />
             @else
                 <x-card.not-live />
             @endif
             <p class="text-white text-nowrap truncate">{{ $title }}</p>
         </div>
         <p class="ms-4 text-slate-900 px-1 bg-slate-300 rounded-xl w-fit mt-1">
             @switch($type)
                 @case('courses')
                     حقيبة دورات
                 @break

                 @case('lessons')
                     حقيبة شروحات
                 @break

                 @case('mixed')
                     حقيبة مختلطة
                 @break

                 @default
                     حقيبة
             @endswitch
         </p>

     </div>
     <div class="flex text-white justify-between items-start">
         <div class=" flex flex-col justify-center items-center w-1/3 ">
             <p class="text-yellow-300">الدورات</p>
             <div class="flex gap-2 items-center">
                 <x-icons.course class="w-5 h
                 -5 " />
                 <p>{{ $coursesCount }}</p>
             </div>
         </div>
         <div class=" flex flex-col justify-center items-center w-1/3 border-x-2 border-white ">
             <p class="text-yellow-300">الشروحات</p>
             <div class="flex gap-2 items-center">
                 <x-icons.lesson class="w-5 h
                 -5 " />
                 <p>{{ $lessonsCount }}</p>
             </div>
         </div>
         <div class=" flex flex-col justify-center items-center w-1/3 ">
             <p class="text-yellow-300">المبلغ</p>
             <div class="flex gap-2 items-center">
                 @if ($type === 'courses')
                     <p>{{ $price }} ريال</p>
                 @else
                     {{-- <p>{{ $monthlyPrice }} ريال شهرية و {{ $annualPrice }} ريال سنوية</p> --}}
                     <p>{{ $monthlyPrice }} / {{ $annualPrice }} ريال</p>
                 @endif
             </div>
         </div>




     </div>
     <div class="pt-3 flex justify-center items-center gap-2">
         {{-- <x-btn.view class="w-1/2 text-nowrap truncate" route="admin.lessons.view" id="{{ $lessonId }}">تصفح
             البيانات</x-btn.view> --}}
         <x-btn.edit class="w-full" route="admin.packages.edit" id="{{ $packageId }}">تعديل</x-btn.edit>
     </div>
 </div>
