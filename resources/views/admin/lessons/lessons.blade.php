@extends('layouts.admin')
@section('content')
    <div class="flex flex-col justify-start items-start min-h-screen space-y-6">
        <div class="w-full md:py-2 py-4 space-y-1 font-judur">
            <h1
                class="text-4xl font-bold text-gradient-to-r bg-gradient-to-r from-pr-500 to-indigo-500 p-2 w-fit text-transparent bg-clip-text ">
                الشروحات</h1>
            <div class="w-full h-1 rounded-lg bg-gradient-to-r from-pr-500 to-indigo-500"></div>
        </div>
        <div>

            <x-btn.add route="admin.courses.create">إضافة شرح</x-btn.add>
        </div>
        <div class="bg-white rounded-lg border border-gray-300 p-4 w-full grid grid-cols-3 gap-6">
            <x-card.admin-lesson :title="'الخيل والليل لامروء القيس'" :numberOfLessons="6" :numberOfStudents="23" :price="30" :courseId="1"/>
            <x-card.admin-lesson :title="'التوابع'" :numberOfLessons="10" :numberOfStudents="54" :price="30" :courseId="1"/>
            <x-card.admin-lesson :title="'همزة الوصل'" :numberOfLessons="4" :numberOfStudents="31" :price="30" :courseId="1"/>    
            <x-card.admin-lesson :title="'الخيل والليل لامروء القيس'" :numberOfLessons="6" :numberOfStudents="16" :price="30" :courseId="1"/>
            <x-card.admin-lesson :title="'التوابع'" :numberOfLessons="3" :numberOfStudents="20" :price="30" :courseId="1"/>
            <x-card.admin-lesson :title="'همزة الوصل'" :numberOfLessons="9" :numberOfStudents="38" :price="30" :courseId="1"/>
                
        

        </div>
    </div>
@endsection()
