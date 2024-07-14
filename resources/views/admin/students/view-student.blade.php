@extends('layouts.admin')
@section('content')
    <div class="w-full md:py-2 py-4 space-y-1 font-judur">
        <h1
            class="text-4xl font-bold text-gradient-to-r bg-gradient-to-r from-pr-500 to-indigo-500 p-2 w-fit text-transparent bg-clip-text ">
            بيانات الطالب </h1>
        <div class="w-full h-1 rounded-lg bg-gradient-to-r from-pr-500 to-indigo-500"></div>
    </div>
    <div class="p-4 mt-4 bg-gray-200 rounded-lg border-2 border-white min-h-screen space-y-8 ">
        <div class="flex gap-6">
            <div class="bg-white rounded-lg w-1/4 flex justify-center items-center h-56 p-2">
                <img src="{{ asset($student->avatar) }}" alt="" class="w-56 h-56">
            </div>
            <div class="w-3/4 bg-white rounded-lg flex justify-between items-start gap-8   h-56 p-6 ">
                <div class="flex flex-col justify-center items-start h-full text-xl  gap-y-4 w-1/2">
                    <p class="text-2xl bg-gray-800 w-full p-1 rounded-lg text-white"><span class="font-hacen text-gray-400 text-lg">إسم الطالب (ة):</span>
                        {{ $student->name }}</p>
                    <p class="text-2xl bg-gray-800 w-full p-1 rounded-lg text-white"><span class="font-hacen text-gray-400 text-lg">البريد الإلكتروني :</span>
                        {{ $student->email }}</p>

                    <div class="flex gap-x-2 bg-gray-800 w-full p-1 rounded-lg text-white">
                        <p><span class="font-hacen text-gray-400 text-lg">تاريخ الإنضمام :</span>
                            {{ $student->created_at->format('d-m-Y') }}</p>
                        <p>( {{ $student->created_at->diffForHumans() }})</p>
                    </div>
                </div>
                <div class="w-1/2 grid grid-cols-2 gap-2">
                    <div
                        class="flex flex-col gap-2 justify-start items-center bg-indigo-800 rounded-lg px-4 py-2 text-white ">
                        <p class="">الدورات</p>
                        <p class="text-4xl">14</p>
                    </div>
                    <div
                        class="flex flex-col gap-2 justify-start items-center bg-indigo-800 rounded-lg px-4 py-2 text-white ">
                        <p>الشروحات</p>
                        <p class="text-4xl">5</p>
                    </div>
                    <div
                        class="flex flex-col gap-2 justify-start items-center bg-indigo-800 rounded-lg px-4 py-2 text-white ">
                        <p>الإختبارات</p>
                        <p class="text-4xl">8</p>
                    </div>
                    <div
                        class="flex flex-col gap-2 justify-start items-center bg-indigo-800 rounded-lg px-4 py-2 text-white ">
                        <p>الشواهد</p>
                        <p class="text-4xl">2</p>
                    </div>
                </div>

            </div>
        </div>

        <div class="w-full min-h-56">
            <div class="px-4 mb-8">
                <p class="text-3xl text-indigo-800 ms-6">دورات الطالب</p>
                <div class="w-full h-[2px] rounded-lg bg-indigo-500"></div>

                <div class="grid grid-cols-3 gap-6 p-6">
                    {{-- Course --}}
                    <x-card.student-course :title="'مقدمة يَعرُب في التأسيس للقدرات - اللفظي'" :progress="65" :course-id="1" />
                    <x-card.student-course :title="'مقدمة يَعرُب في التأسيس للقدرات - اللفظي'" :progress="35" :course-id="1" />
                    <x-card.student-course :title="'مقدمة يَعرُب في التأسيس للقدرات - اللفظي'" :progress="85" :course-id="1" />
                    <x-card.student-course :title="'مقدمة يَعرُب في التأسيس للقدرات - اللفظي'" :progress="85" :course-id="1" />


                </div>
            </div>
            <div class="px-4  mb-8">
                <p class="text-3xl text-indigo-800 ms-6">شروحات الطالب</p>
                <div class="w-full h-[2px] rounded-lg bg-indigo-500"></div>

                <div class="grid grid-cols-3 gap-6 p-6">
                    {{-- Course --}}
                    <x-card.student-lesson :title="'مقدمة يَعرُب في التأسيس للقدرات - اللفظي'" :progress="65" :lesson-id="1" />
                    <x-card.student-lesson :title="'مقدمة يَعرُب في التأسيس للقدرات - اللفظي'" :progress="35" :lesson-id="1" />
                    <x-card.student-lesson :title="'مقدمة يَعرُب في التأسيس للقدرات - اللفظي'" :progress="85" :lesson-id="1" />
                    <x-card.student-lesson :title="'مقدمة يَعرُب في التأسيس للقدرات - اللفظي'" :progress="85" :lesson-id="1" />


                </div>
            </div>
        </div>

    </div>
@endsection()
