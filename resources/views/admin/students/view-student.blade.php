@extends('layouts.admin')
@section('content')
    <div class=" flex gap-8">

        <div class="w-2/3 bg-gray-200 rounded-xl min-h-screen px-6 py-4">

            <div class=" ">
                {{-- /////////////////////////////////////////////////////// --}}
                <div x-data="{ selectedTab: 'courses' }" class="w-full">
                    <div @keydown.right.prevent="$focus.wrap().next()" @keydown.left.prevent="$focus.wrap().previous()"
                        class="flex gap-2 overflow-x-auto border-b border-slate-300 " role="tablist" aria-label="tab options">
                        <button @click="selectedTab = 'courses'" :aria-selected="selectedTab === 'courses'"
                            :tabindex="selectedTab === 'courses' ? '0' : '-1'"
                            :class="selectedTab === 'courses' ?
                                'font-bold text-blue-700 border-b-2 border-blue-700 ' :
                                'text-slate-700 font-medium hover:border-b-2 hover:border-b-slate-800 hover:text-black'"
                            class="flex h-min items-center gap-2 px-4 py-2 text-sm" type="button" role="tab"
                            aria-controls="tabpanelGroups">


                            <div class="text-xl flex items-center justify-start gap-2">
                                <p>الدورات</p>
                                <x-icons.course class="w-5 h-5 text-blue" />
                                <span
                                    :class="selectedTab === 'courses' ?
                                        'border-indigo-500' :
                                        'border-gray-500'"
                                    class="border-2  p-1 rounded-full min-w-6  text-xs">5</span>
                            </div>
                        </button>
                        <button @click="selectedTab = 'lessons'" :aria-selected="selectedTab === 'lessons'"
                            :tabindex="selectedTab === 'lessons' ? '0' : '-1'"
                            :class="selectedTab === 'lessons' ?
                                'font-bold text-blue-700 border-b-2 border-blue-700 ' :
                                'text-slate-700 font-medium hover:border-b-2 hover:border-b-slate-800 hover:text-black'"
                            class="flex h-min items-center gap-2 px-4 py-2 text-sm" type="button" role="tab"
                            aria-controls="tabpanelGroups">


                            <div class="text-xl flex items-center justify-start gap-2">
                                <p>الدورات</p>
                                <x-icons.lesson class="w-5 h-5 text-blue" />
                                <span
                                    :class="selectedTab === 'lessons' ?
                                        'border-indigo-500' :
                                        'border-gray-500'"
                                    class="border-2  p-1 rounded-full min-w-6  text-xs">5</span>
                            </div>
                        </button>

                    </div>
                    <div class="px-2 py-4 text-slate-700">
                        <div x-show="selectedTab === 'courses'" id="tabpanelGroups" role="tabpanel" aria-label="courses">
                            <div class="w-full">
                                <div
                                    class="w-full h-32 rounded-3xl bg-gradient-to-tr from-indigo-400 to-indigo-500 flex flex-col justify-center items-end">
                                    <div
                                        class="w-24 h-24 me-4 rounded-full bg-indigo-800 border-2 border-while text-center text-2xl font-judur flex justify-center items-center text-white">
                                        <p>44%</p>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div x-show="selectedTab === 'lessons'" id="tabpanelGroups" role="tabpanel" aria-label="lessons">
                            <div class="grid grid-cols-2 gap-4">
                                <x-card.student-lesson title="aaaaaaa" :progress="50" :lessonId="1" class="w-1/3" />
                                <x-card.student-lesson title="aaaaaaa" :progress="50" :lessonId="1" class="w-1/3" />
                                <x-card.student-lesson title="aaaaaaa" :progress="50" :lessonId="1" class="w-1/3" />
                                <x-card.student-lesson title="aaaaaaa" :progress="50" :lessonId="1" class="w-1/3" />
                            </div>
                        </div>

                    </div>
                </div>

                {{-- /////////////////////////////////////////////////////// --}}
            </div>
        </div>

        <div class="w-1/3 bg-gray-200 rounded-xl min-h-screen ">
            {{-- //////////////// Avatar //////////////// --}}
            <div class="flex justify-center items-center h-56 p-2">
                <img src="{{ asset($student->avatar) }}" alt="" class="w-56 h-56">
            </div>
            {{-- //////////////// Info //////////////// --}}
            <div class="py-2 flex justify-center items-center gap-8">
                <div>
                    <p class="font-hacen text-4xl text-gray-800">{{ $student->name }}</p>
                    <p class="font-hacen text-xl text-gray-500 ">{{ $student->email }}</p>
                </div>
                <div class="flex flex-col justify-center items-center">
                    <img class="w-16 " src="{{ asset('assets/images/profile_badges/level_1.png') }}" />
                </div>
            </div>
            {{-- //////////////// Badges //////////////// --}}
            <div class="grid grid-cols-2 gap-4 p-6 align-middle place-items-center">
                <div
                    class="w-[90%] p-3 rounded-lg h-20 bg-gradient-to-tr from-blue-400 to-blue-500 flex flex-col justify-center items-center">
                    <div class="flex justify-center items-center gap-3">
                        <p class="text-xl text-white">الدورات</p>
                        <x-icons.course class="w-5 h-5 text-white" />
                    </div>
                    <p class="text-3xl text-white">8</p>
                </div>
                <div
                    class="w-[90%] p-3 rounded-lg h-20 bg-gradient-to-tr from-red-400 to-red-500 flex flex-col justify-center items-center">
                    <div class="flex justify-center items-center gap-3">
                        <p class="text-xl text-white">الشروحات</p>
                        <x-icons.lesson class="w-5 h-5 text-white" />
                    </div>
                    <p class="text-3xl text-white">2</p>
                </div>
                <div
                    class="w-[90%] p-3 rounded-lg h-20 bg-gradient-to-tr from-green-400 to-green-500 flex flex-col justify-center items-center">
                    <div class="flex justify-center items-center gap-3">
                        <p class="text-xl text-white">الإختبارات</p>
                        <x-icons.test class="w-5 h-5 text-white" />
                    </div>
                    <p class="text-3xl text-white">10</p>
                </div>
                <div
                    class="w-[90%] p-3 rounded-lg h-20 bg-gradient-to-tr from-yellow-400 to-yellow-500 flex flex-col justify-center items-center">
                    <div class="flex justify-center items-center gap-3">
                        <p class="text-xl text-white">الشواهد</p>
                        <x-icons.certificate class="w-5 h-5 text-white" />
                    </div>
                    <p class="text-3xl text-white">2</p>
                </div>
            </div>

            {{-- //////////////// Join Date //////////////// --}}
            <div class="px-8 my-4 flex gap-x-4">
                <x-icons.calendar class="w-16 h-16 text-gray-600 " />
                <div>
                    <p class="text-xl text-gray-500 font-nitaqat mb-2 ">تاريخ الانضمام :</p>
                    <div class="flex text-xl gap-1 ">
                        <p class=" ">{{ $student->created_at->format('d-m-Y') }}</p>
                        <p class=" ">({{ $student->created_at->diffForHumans() }})</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection()
