 <div  {{$attributes->merge(['class' => 'w-full h-fit bg-gradient-to-tr from-indigo-800 to-indigo-400 rounded-xl shadow-lg p-4 space-y-4'])}}>
     <div class="w-full h-44 rounded-xl overflow-hidden">
         <x-card.course-img title="'{{$title}}'" />
     </div>
     <div class="">
         <p class="text-white">{{$title}}</p>
     </div>
     <div class="flex text-white justify-between items-center">
         <div class="border-l-2 border-white flex flex-col justify-center items-center w-1/3">
             <p class="text-yellow-300">الدروس</p>
             <div class="flex gap-2 items-center">
                 <x-icons.video class="w-5 h-5 " />
                 <p>{{$numberOfLessons}}</p>
             </div>
         </div>
         <div class="border-l-2 border-white flex flex-col justify-center items-center w-1/3">
             <p class="text-yellow-300">المشتركين</p>
             <div class="flex gap-2 items-center justify-center">
                 <x-icons.user class="w-5 h-5 " />
                 <p>{{$numberOfStudents}}</p>
             </div>
         </div>
         <div class=" flex flex-col justify-center items-center w-1/3">
             <p class="text-yellow-300">المبلغ</p>
             <div class="flex gap-2 items-center justify-center">
                 <x-icons.money-bag class="w-5 h-5 " />
                 <p class="text-no-wrap truncate">{{$price}} رس</p>
             </div>
         </div>

     </div>
     <div class="pt-3">
         <x-btn.edit class="" route="admin.courses.edit" id="{{$courseId}}">تعديل</x-btn.edit>
     </div>
 </div>
