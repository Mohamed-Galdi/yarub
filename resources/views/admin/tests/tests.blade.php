@extends('layouts.admin')
@section('content')
    <div class="flex flex-col justify-start items-start min-h-screen space-y-6">
        <div>
            <x-btn.add route="admin.courses.create">إضافة إختبار</x-btn.add>
        </div>
        <div class="w-full">
            <x-dynamic-tab-navigation :tabs="[
                [
                    'id' => 'courses_tests',
                    'label' => 'الدورات',
                    'icon' => 'icons.course',
                    'count' => false,
                ],
                [
                    'id' => 'lessons_test',
                    'label' => 'الشروحات',
                    'icon' => 'icons.lesson',
                    'count' => false,
                ],
                [
                    'id' => 'lessons_exams',
                    'label' => 'الاستجابات',
                    'icon' => 'icons.course',
                    'count' => false,
                ]
            ]">
                <x-slot name="courses_tests">
                    <p>This is the content for the courses tab.</p>
                </x-slot>

                <x-slot name="lessons_test">
                    <p>This is the content for the lessons tab.</p>
                </x-slot>

                <x-slot name="lessons_exams">
                    <p>This is the content for the lessons exams tab.</p>
                </x-slot>

            </x-dynamic-tab-navigation>
        </div>
    </div>
@endsection()
