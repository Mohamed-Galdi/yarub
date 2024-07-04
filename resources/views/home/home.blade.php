@extends('layouts.guest')
@section('content')
    <div class="">
        {{-- /////////////////////////////// Hero section /////////////////////////////// --}}
        <div class="mx-auto max-w-screen-xl flex justify-between items-center pt-3 px-3">
            <div class="w-1/2 flex flex-col items-start gap-6  justify-start">
                <h1 class="text-7xl font-hacen">منصتك <span class="">الرقمية</span> الاولى<br /> لتعلم <span
                        class="text-pr-100">اللغة العربية</span></h1>
                <p class="text-3xl font-nitaqat">مهمتنا تسهيل تعلم اللغة العربية لمختلف المستويات بشكل رقمي، سهل، وفعال.</p>
                <div class="mt-4">
                    <x-btn.slide-dark :text="'انضم الينا '" :link="'/home'" />
                    <x-btn.slide-light :text="'استكشف الدورات '" :link="'/home'" />

                </div>
            </div>
            <div class="w-1/2 flex justify-end items-end relative">
                <img src="./assets/illustrations/library.png" alt=" home illustration" class="w-[90%] ">
            </div>
        </div>
        {{-- /////////////////////////////// Our advantages section /////////////////////////////// --}}
        <div class="bg-gray-800 py-8">
            <div class="mx-auto max-w-screen-xl">

                <h2 class="text-gray-200 text-center text-5xl mb-6">ميزاتنا الاساسية</h2>
                <div class="flex jusfify-between items-center gap-8 ">
                    <div class="text-white flex flex-col items-start gap-4 py-4 pe-12 ps-4 w-1/3">
                        <div
                            class="w-16 h-16 bg-gradient-to-bl from-indigo-300 to-indigo-700 rounded-lg flex items-center justify-center transition-all duration-300 ease-in-out hover:scale-105 cursor-pointer shadow-blue-400 shadow-2xl ">
                            <div class="w-12">
                                <x-icons.laptop />
                            </div>
                        </div>
                        <h3 class="underline font-judur underline-offset-8">منصة رقمية </h3>
                        <p class="text-lg font-nitaqat">منصة رقمية تتيح لك تعلم اللغة العربية في بيئة حديثة ومتطورة</p>
                    </div>
                    <div class="text-white flex flex-col items-start gap-4 py-4 pe-12 ps-4 w-1/3">
                        <div
                            class="w-16 h-16 bg-gradient-to-bl from-indigo-300 to-indigo-700 rounded-lg flex items-center justify-center transition-all duration-300 ease-in-out hover:scale-105 cursor-pointer shadow-blue-400 shadow-2xl ">
                            <div class="w-8">
                                <x-icons.book />
                            </div>
                        </div>
                        <h3 class="underline font-judur underline-offset-8">اعلى جودة </h3>
                        <p class="text-lg font-nitaqat">دروس ودورات بمواد تعليمية من طرف مؤطرين باعلى الكفاءات في اللغة
                            العربية</p>
                    </div>
                    <div class="text-white flex flex-col items-start gap-4 py-4 pe-12 ps-4 w-1/3">
                        <div
                            class="w-16 h-16 bg-gradient-to-bl  from-indigo-300 to-indigo-700  rounded-lg flex items-center justify-center transition-all duration-300 ease-in-out hover:scale-105 cursor-pointer shadow-blue-400 shadow-2xl ">
                            <div class="w-[2.5rem]">
                                <x-icons.help />
                            </div>
                        </div>
                        <h3 class="underline font-judur underline-offset-8"> مواكبة مستمرة </h3>
                        <p class="text-lg font-nitaqat">مواكبة مستمرة خلال مراحل التعلم من الدروس حتى الاختبارات</p>
                    </div>

                </div>
            </div>

        </div>
        {{-- /////////////////////////////// Our Best Courses section /////////////////////////////// --}}
        <div class="py-16">
            <div class="mx-auto max-w-screen-xl">
                <div class="flex justify-between items-center ">
                    <div>
                        <h2 class="text-primary text-5xl mb-2 font">أبرز الدورات</h2>
                        <p class="text-gray-500 font-judur text-sm ms-2">الدورات الاكثر تداولا في المنصة</p>
                    </div>
                    <div class="text-lg">
                        <a href="{{ route('courses') }}">
                            <x-btn.scale-dark :text="'إكتشف المزيد ...'" />
                        </a>
                    </div>

                </div>
                <div class="mt-8 flex justify-between items-center gap-4">
                    {{-- card --}}
                    <x-card.course :title="' العربي الرقمي'" :description="'الدورة الأولى للتعلم العربي الرقمي '" :price="100" />
                    <x-card.course :title="' العربي الرقمي'" :description="'الدورة الأولى للتعلم العربي الرقمي '" :price="100" />
                    <x-card.course :title="' العربي الرقمي'" :description="'الدورة الأولى للتعلم العربي الرقمي '" :price="100" />


                </div>

            </div>

        </div>
        {{-- /////////////////////////////// Our Best Courses section /////////////////////////////// --}}
        <div class="py-16">
            <div class="mx-auto max-w-screen-xl">
                <div class="flex justify-between items-center ">
                    <div>
                        <h2 class="text-primary text-5xl mb-2 font">أبرز الشروحات</h2>
                        <p class="text-gray-500 font-judur text-sm ms-2">الشروحات الاكثر تداولا في المنصة</p>
                    </div>
                    <div class="text-lg">
                        <a href="{{ route('courses') }}">
                            <x-btn.scale-dark :text="'إكتشف المزيد ...'" />
                        </a>
                    </div>

                </div>
                <div class="mt-8 flex justify-between items-center gap-4">
                    {{-- card --}}
                    <x-card.course :title="' العربي الرقمي'" :description="'الدورة الأولى للتعلم العربي الرقمي '" :price="100" />
                    <x-card.course :title="' العربي الرقمي'" :description="'الدورة الأولى للتعلم العربي الرقمي '" :price="100" />
                    <x-card.course :title="' العربي الرقمي'" :description="'الدورة الأولى للتعلم العربي الرقمي '" :price="100" />

                </div>

            </div>

        </div>
        {{-- /////////////////////////////// Why Choose us section /////////////////////////////// --}}
        <div class="pt-16">
            <div class="mx-auto max-w-screen-xl  relative">
                <h2 class="text-gray-700 text-5xl mb-2 font text-center">لماذا قد <span
                        class="text-primary underline underline-offset-2">تختار</span> منصة يعرب ؟
                </h2>
                <div class="flex justify-center items-center mt-36 ">
                    <img src="./assets/illustrations/tests.png" alt="" class=" absolute w-44 left-44 top-72 -rotate-12">
                    <img src="./assets/illustrations/instructors.png" alt="" class=" absolute w-44 left-72 top-24 -rotate-6">
                    <img src="./assets/illustrations/diversity.png" alt="" class=" absolute w-44 left-[45%] top-16">
                    <img src="./assets/illustrations/arabic.png" alt="" class=" absolute w-44 right-72 top-24 rotate-6">
                    <img src="./assets/illustrations/support.png" alt="" class=" absolute w-44 right-44 top-72 rotate-12">
                    <img src="./assets/illustrations/circle.png" alt=" why choose us illustration" class="w-[40%] ">
                </div>
            </div>
        </div>
    </div>
@endsection()
