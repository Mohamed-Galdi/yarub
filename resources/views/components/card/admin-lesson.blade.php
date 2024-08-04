 <div
     {{ $attributes->merge(['class' => 'w-full h-fit bg-gradient-to-tr rounded-xl shadow-lg p-4 space-y-4' . ($published ? ' from-teal-800 to-teal-400' : ' from-gray-800 to-gray-400')]) }}>
     <div class="w-full h-44 rounded-xl overflow-hidden">
         <x-card.course-img title="'{{ $title }}'" class />
     </div>
     <div>
         <div class="flex justify-start items-center gap-2">
             @if ($published)
                 <x-card.live />
             @else
                 <x-card.not-live />
             @endif
             <p class="text-white text-nowrap truncate">{{ $title }}</p>
         </div>
         <p class=" ms-4 text-slate-900 px-1 bg-slate-300 rounded-xl w-fit mt-1">{{ Str::words($type, 3)  }}</p>
     </div>
     <div class="flex text-white justify-between items-start">
         <div class=" flex flex-col justify-center items-center w-1/3 ">
             <p class="text-yellow-300">الدروس</p>
             <div class="flex gap-2 items-center">
                 <x-icons.video class="w-5 h-5 " />
                 <p>{{ $numberOfLessons }}</p>
             </div>
         </div>
         <div class=" flex flex-col justify-center items-center w-1/3 border-x-2 border-white  overflow-hidden ">
             <p class="text-yellow-300">الإشتراك</p>
             <div class=" text-sm font-judur w-fit">
                 <p class="text-xs text-nowrap truncate">{{ $monthlyPrice }} ر.س/ شهريا</p>
                 <p class="text-xs text-nowrap truncate">{{ $annualPrice }} ر,س/ سنويا</p>
             </div>
         </div>
         <div class=" flex flex-col justify-center items-center w-1/3 ">
             <p class="text-yellow-300">المشتركين</p>
             <div class="flex gap-2 items-center justify-center">
                 <x-icons.user class="w-5 h-5 " />
                 <p>{{ $numberOfStudents }}</p>
             </div>
         </div>


     </div>
     <div class="pt-3 flex justify-center items-center gap-2">
         <x-btn.view class="w-1/2 text-nowrap truncate" route="admin.lessons.view" id="{{ $lessonId }}">تصفح
             البيانات</x-btn.view>
         <x-btn.edit class="w-1/2" route="admin.lessons.edit" id="{{ $lessonId }}">تعديل</x-btn.edit>
     </div>
 </div>
