@extends('layouts.admin')
@section('content')
    <div class="flex flex-col justify-start items-start min-h-screen space-y-6">

        <div>

            <x-btn.add route="admin.lessons.create">إضافة شرح</x-btn.add>
        </div>
        <div class="bg-white rounded-lg border border-gray-300 p-4 w-full grid lg:grid-cols-3 grid-cols-1 place-items-center gap-6">
            <x-card.admin-lesson :title="'الخيل والليل لامروء القيس'" :numberOfLessons="6" :numberOfStudents="23" :price="30"
                :courseId="1" />
            <x-card.admin-lesson :title="'التوابع'" :numberOfLessons="10" :numberOfStudents="54" :price="30"
                :courseId="1" />
            <x-card.admin-lesson :title="'همزة الوصل'" :numberOfLessons="4" :numberOfStudents="31" :price="30"
                :courseId="1" />
            <x-card.admin-lesson :title="'الخيل والليل لامروء القيس'" :numberOfLessons="6" :numberOfStudents="16" :price="30"
                :courseId="1" />
            <x-card.admin-lesson :title="'التوابع'" :numberOfLessons="3" :numberOfStudents="20" :price="30"
                :courseId="1" />
            <x-card.admin-lesson :title="'همزة الوصل'" :numberOfLessons="9" :numberOfStudents="38" :price="30"
                :courseId="1" />



        </div>
    </div>
@endsection()
