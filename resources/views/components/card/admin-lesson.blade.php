 <div  {{$attributes->merge(['class' => 'w-full h-fit bg-gradient-to-tr from-teal-800 to-teal-400 rounded-xl shadow-lg p-4 space-y-4'])}}>
     <div class="w-full h-44 rounded-xl overflow-hidden">
         <x-card.course-img title="'{{$title}}'" class />
     </div>
     <div class="">
         <p class="text-white">{{$title}}</p>
     </div>
     <div class="flex text-white justify-between items-start">
         <div class=" flex flex-col justify-center items-center w-1/3 ">
             <p class="text-yellow-300">الدروس</p>
             <div class="flex gap-2 items-center">
                 <x-icons.video class="w-5 h-5 " />
                 <p>{{$numberOfLessons}}</p>
             </div>
         </div>
          <div class=" flex flex-col justify-center items-center w-1/3 border-x-2 border-white  ">
             <p class="text-yellow-300">الإشتراك</p>
             <div class=" text-sm font-judur">
                 <p>{{$monthlyPrice}}  رس/ شهريا</p>
                 <p>{{$annualPrice}}  رس/ سنويا</p>
             </div>
         </div>
         <div class=" flex flex-col justify-center items-center w-1/3 ">
             <p class="text-yellow-300">المشتركين</p>
             <div class="flex gap-2 items-center justify-center">
                 <x-icons.user class="w-5 h-5 " />
                 <p>{{$numberOfStudents}}</p>
             </div>
         </div>
        

     </div>
     <div class="">
         <x-btn.edit class="" route="admin.lessons.edit" id="{{$lessonId}}">تعديل</x-btn.edit>
     </div>
 </div>
