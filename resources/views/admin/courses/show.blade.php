@extends('layouts.admin')

@section('content')
    <div>
        <div class="flex justify-between">
            <h1 class="lg:text-4xl text-2xl text-nowrap truncate text-gray-500 mb-6">{{ $course->title }}</h1>
            </h1>
            {{-- // back button --}}
            <x-btn.back route="admin.courses" />
        </div>
        <div class="flex lg:flex-row flex-col justify-center items-center gap-6">
            <div
                class="lg:w-1/2 w-full flex px-8 py-3 justify-between items-center bg-gray-800 text-gray-200 text-2xl rounded-xl border border-indigo-500 ">
                <div>
                    <x-icons.students class="w-8 h-8 text-gray-50" />
                    <p>مجموع المشتركين</p>
                </div>
                <div>
                    <p class="font-hacen text-3xl">{{ $course->students->count() }}</p>
                </div>
            </div>
            <div
                class="lg:w-1/2 w-full flex px-8 py-3 justify-between items-center bg-gray-800 text-gray-200 text-2xl rounded-xl border border-indigo-500 ">
                <div>
                    <x-icons.payments class="w-8 h-8 text-gray-50" />
                    <p>مجموع المداخيل</p>
                </div>
                <div class="">
                    <p class="font-hacen text-3xl">{{ $course->students()->sum('student_course_sub.cost') }} </p>
                    <p class="font-judur text-geay-700 text-sm">ريال سعودي</p>
                </div>
            </div>
        </div>
        @if ($course->liveSession)
            <div
                class="my-4 w-full flex lg:flex-row flex-col justify-start items-center lg:gap-8 gap-3 px-2 py-4 border-slate-800 border-2 bg-green-800 text-white rounded-lg">
                <p>رابط الإنضمام للبث</p>
                <p onclick="coupyLink('{{ $course->liveSession->join_url }}')"
                    class="p-2 rounded-lg bg-slate-50 text-slate-900 cursor-pointer hover:bg-slate-300 transition-all duration-200 ease-in-out">
                    {{ $course->liveSession->join_url }}</p>
                <div id="copied-popup"
                    class=" left-1/2 -translate-x-1/2 -bottom-8 bg-green-500 text-white px-2 py-1 rounded text-sm hidden">
                    تم النسخ!
                </div>
            </div>
        @endif
        {{-- students table --}}
        <div class="mt-8">
            <livewire:course-students-table :courseId="$course->id" />
        </div>
    </div>
    <script>
        function coupyLink(link) {
            navigator.clipboard.writeText(link).then(() => {
                const popup = document.getElementById('copied-popup');
                popup.classList.remove('hidden');
                setTimeout(() => {
                    popup.classList.add('hidden');
                }, 2000);
            }).catch(err => {
                console.error('Failed to copy: ', err);
            });
        }
    </script>
@endsection
