@extends('layouts.admin')
@section('content')
    <div class="flex flex-col justify-start items-start min-h-screen space-y-6">
        <div>
            <x-btn.add route="admin.tests.create">إضافة إختبار</x-btn.add>
        </div>
        <div class="w-full">
            <x-dynamic-tab-navigation :tabs="[
                [
                    'id' => 'courses_tests',
                    'label' => 'إختبارات الدورات',
                    'icon' => 'icons.course',
                    'count' => false,
                ],
                [
                    'id' => 'lessons_test',
                    'label' => 'إختبارات الشروحات',
                    'icon' => 'icons.lesson',
                    'count' => false,
                ],
            ]">
                <x-slot name="courses_tests">
                    <div class="grid lg:grid-cols-3 gap-4 grid-cols-1 md:grid-cols-2">
                        @forelse ($courses_tests as $test)
                            <x-card.admin-test published="{{$test->is_published}}" testId="{{$test->id}}" title="{{$test->title}}" attemptsCount="{{$test->attempts->count()}}" type="{{$test->type}}" courseTitle="{{$test->course->title}}" />
                        @empty
                            <x-card.empty-state title="لا توجد إختبارات بعد للدورات" message="" :image="true"
                                class="w-1/2 h-auto mx-auto" />
                        @endforelse

                    </div>
                </x-slot>

                <x-slot name="lessons_test">
                    <div class="grid lg:grid-cols-3 gap-4 grid-cols-1 md:grid-cols-2">
                        @forelse ($lessons_tests as $test)
                            <x-card.admin-test published="{{$test->is_published}}" testId="{{$test->id}}" title="{{$test->title}}" attemptsCount="{{$test->attempts->count()}}" type="{{$test->type}}" courseTitle="{{$test->lesson->title}}" />
                        @empty
                            <x-card.empty-state title="لا توجد إختبارات بعد للدورات" message="" :image="true"
                                class="w-1/2 h-auto mx-auto" />
                        @endforelse
                </x-slot>


            </x-dynamic-tab-navigation>
        </div>
    </div>
@endsection()
