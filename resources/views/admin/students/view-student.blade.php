@extends('layouts.admin')
@section('content')
    <div class=" flex flex-col md:flex-row gap-8">

        {{-- //////////////////// Student Items //////////////////// --}}
        <div class="order-2 md:order-1 md:w-2/3 w-full bg-gray-200 rounded-xl md:min-h-screen h-fit px-6 py-4">

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
                                <p>الشروحات</p>
                                <x-icons.lesson class="w-5 h-5 text-blue" />
                                <span
                                    :class="selectedTab === 'lessons' ?
                                        'border-indigo-500' :
                                        'border-gray-500'"
                                    class="border-2  p-1 rounded-full min-w-6  text-xs">5</span>
                            </div>
                        </button>                        
                        <button @click="selectedTab = 'tests'" :aria-selected="selectedTab === 'tests'"
                            :tabindex="selectedTab === 'tests' ? '0' : '-1'"
                            :class="selectedTab === 'tests' ?
                                'font-bold text-blue-700 border-b-2 border-blue-700 ' :
                                'text-slate-700 font-medium hover:border-b-2 hover:border-b-slate-800 hover:text-black'"
                            class="flex h-min items-center gap-2 px-4 py-2 text-sm" type="button" role="tab"
                            aria-controls="tabpanelGroups">


                            <div class="text-xl flex items-center justify-start gap-2">
                                <p>الإختبارات</p>
                                <x-icons.test class="w-5 h-5 text-blue" />
                                <span
                                    :class="selectedTab === 'tests' ?
                                        'border-indigo-500' :
                                        'border-gray-500'"
                                    class="border-2  p-1 rounded-full min-w-6  text-xs">5</span>
                            </div>
                        </button>
                        <button @click="selectedTab = 'certificates'" :aria-selected="selectedTab === 'certificates'"
                            :tabindex="selectedTab === 'certificates' ? '0' : '-1'"
                            :class="selectedTab === 'certificates' ?
                                'font-bold text-blue-700 border-b-2 border-blue-700 ' :
                                'text-slate-700 font-medium hover:border-b-2 hover:border-b-slate-800 hover:text-black'"
                            class="flex h-min items-center gap-2 px-4 py-2 text-sm" type="button" role="tab"
                            aria-controls="tabpanelGroups">


                            <div class="text-xl flex items-center justify-start gap-2">
                                <p>الشواهد</p>
                                <x-icons.certificate class="w-5 h-5 text-blue" />
                                <span
                                    :class="selectedTab === 'certificates' ?
                                        'border-indigo-500' :
                                        'border-gray-500'"
                                    class="border-2  p-1 rounded-full min-w-6  text-xs">5</span>
                            </div>
                        </button>


                    </div>
                    <div class="p-2">
                        <div x-show="selectedTab === 'courses'" id="tabpanelGroups" role="tabpanel" aria-label="courses">
                            <div class="mt-4 space-y-6">
                                {{-- course component --}}
                               <x-card.student-profile-course title="مقدمة يَعرُب في التأسيس للقدرات - اللفظي" startDate="منذ 10 ايام" lastVisitDate="منذ 5 ساعات" courseCount="2/5" testCount="1/4" :progress="66" />
                               <x-card.student-profile-course title="مقدمة يَعرُب في التأسيس للقدرات - اللفظي" startDate="منذ 10 ايام" lastVisitDate="منذ 5 ساعات" courseCount="2/5" testCount="1/4" :progress="5" />
                               <x-card.student-profile-course title="مقدمة يَعرُب في التأسيس للقدرات - اللفظي" startDate="منذ 10 ايام" lastVisitDate="منذ 5 ساعات" courseCount="2/5" testCount="1/4" :progress="75" />
                               <x-card.student-profile-course title="مقدمة يَعرُب في التأسيس للقدرات - اللفظي" startDate="منذ 10 ايام" lastVisitDate="منذ 5 ساعات" courseCount="2/5" testCount="1/4" :progress="33" />
                               <x-card.student-profile-course title="مقدمة يَعرُب في التأسيس للقدرات - اللفظي" startDate="منذ 10 ايام" lastVisitDate="منذ 5 ساعات" courseCount="2/5" testCount="1/4" :progress="66" />

                            </div>
                        </div>
                        <div x-show="selectedTab === 'lessons'" id="tabpanelGroups" role="tabpanel" aria-label="lessons">
                            <div class="mt-4 space-y-6">
                                <x-card.student-profile-lesson title="مقدمة يَعرُب في التأسيس للقدرات - اللفظي" startDate="منذ 10 ايام" endDate="01/04/2024" duration="10 ساعات" testCount="1/4" :progress="66" />
                                <x-card.student-profile-lesson title="مقدمة يَعرُب في التأسيس للقدرات - اللفظي" startDate="منذ 10 ايام" endDate="01/04/2024" duration="10 ساعات" testCount="1/4" :progress="90" />
                                <x-card.student-profile-lesson title="مقدمة يَعرُب في التأسيس للقدرات - اللفظي" startDate="منذ 10 ايام" endDate="01/04/2024" duration="10 ساعات" testCount="1/4" :progress="33" />
                                <x-card.student-profile-lesson title="مقدمة يَعرُب في التأسيس للقدرات - اللفظي" startDate="منذ 10 ايام" endDate="01/04/2024" duration="10 ساعات" testCount="1/4" :progress="6" />
                            </div>
                        </div>
                        <div x-show="selectedTab === 'tests'" id="tabpanelGroups" role="tabpanel" aria-label="tests">
                            <div class="mt-4 space-y-6">
                                <p>...</p>
                            </div>
                        </div>
                        <div x-show="selectedTab === 'certificates'" id="tabpanelGroups" role="tabpanel" aria-label="certificates">
                            <div class="mt-4 space-y-6">
                                <p>...</p>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- /////////////////////////////////////////////////////// --}}
            </div>
        </div>

        {{-- //////////////////// Student Info //////////////////// --}}
        <div class="order-1 md:order-2 md:w-1/3 w-full bg-gray-200 rounded-xl md:min-h-screen h-fit  ">
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
