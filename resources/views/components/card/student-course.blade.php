 <div  {{$attributes->merge(['class' => 'w-full h-96 bg-gradient-to-tr from-indigo-800 to-indigo-400 rounded-xl shadow-lg p-4 space-y-4 overflow-hidden'])}}>
     <div class="w-full h-1/2 rounded-xl overflow-hidden">
         <x-card.course-img title="'{{$title}}'" />
     </div>
     <div class="">
         <p class="text-white text-2xl truncate text-nowrap">{{$title}}</p>
     </div>
     <div class=" text-white justify-between items-center">
        <p>التقدم: <span class="text-2xl text-gray-100">{{$progress}}%</span></p>
         <div dir="ltr" class="w-full h-4 bg-gray-200 rounded-xl p-[2px] float-end ">
            <div class="h-full  bg-gradient-to-l rounded-md to-sky-900 from-green-400" style="width: {{$progress}}%">
            </div>

         </div>

     </div>
     <div class="pt-3">
         <x-btn.enrol class="" route="admin.courses.edit" id="{{$courseId}}">إستكمال</x-btn.enrol>
     </div>
 </div>
