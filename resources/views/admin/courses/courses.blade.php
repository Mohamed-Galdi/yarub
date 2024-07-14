@extends('layouts.admin')
@section('content')
    <div class="flex flex-col justify-start items-start min-h-screen space-y-6">
        <div class="w-full md:py-2 py-4 space-y-1 font-judur">
            <h1
                class="text-4xl font-bold text-gradient-to-r bg-gradient-to-r from-pr-500 to-indigo-500 p-2 w-fit text-transparent bg-clip-text ">
                الدورات</h1>
            <div class="w-full h-1 rounded-lg bg-gradient-to-r from-pr-500 to-indigo-500"></div>
        </div>
        <div>

            <x-btn.add route="admin.courses.create">إضافة دورة</x-btn.add>
        </div>
        <div class="bg-white rounded-lg border border-gray-300 p-4 w-full grid grid-cols-3 gap-6">
            
           <x-card.admin-course :title="'مقدمة يَعرُب في التأسيس للقدرات - اللفظي'" :number_of_lessons="6" :number_of_students="23" :price="30" :course_id="1"/>
           <x-card.admin-course :title="'التعريف بأقسام اختبار القدرات -اللفظي'" :number_of_lessons="8" :number_of_students="41" :price="30" :course_id="2"/>
           <x-card.admin-course :title="'المفردة الشاذة ( الارتباط والاختلاف )'" :number_of_lessons="1" :number_of_students="16" :price="30" :course_id="3"/>
           <x-card.admin-course :title="'مقدمة يَعرُب في التأسيس للقدرات - اللفظي'" :number_of_lessons="4" :number_of_students="22" :price="30" :course_id="4"/>
           <x-card.admin-course :title="'التعريف بأقسام اختبار القدرات -اللفظي'" :number_of_lessons="5" :number_of_students="32" :price="30" :course_id="5"/>
           <x-card.admin-course :title="'المفردة الشاذة ( الارتباط والاختلاف )'" :number_of_lessons="6" :number_of_students="15" :price="30" :course_id="6"/>
           
            
        </div>
    </div>
@endsection()
