@extends('layouts.guest')
@section('content')
    <div class="  md:mt-0 mt-6 ">
        {{-- /////////////////////////////// Hero section /////////////////////////////// --}}
        <div
            class="mx-auto max-w-screen-xl px-8 md:px-4 lg:px-3 flex md:flex-row flex-col justify-between items-center pt-3 ">
            <div
                class="md:w-1/2 w-full flex flex-col md:items-start items-center gap-6  md:justify-start justify-center md:text-start text-center">
                @if ($homePage->main_title == '' || $homePage->main_title == 'منصتك الرقمية الأولى لتعلم اللغة العربية')
                    <h1 class="md:text-6xl lg:text-7xl text-5xl font-hacen">منصتك <span class="">الرقمية</span>
                        الأولى<br />
                        لتعلم <span class="text-pr-100">اللغة العربية</span></h1>
                @else
                    <h1 class="md:text-6xl lg:text-7xl text-5xl font-hacen"> {{ $homePage->main_title }} </h1>
                @endif
                <p class="text-3xl font-nitaqat">
                    {{ $homePage->sub_title ?: 'مهمتنا تسهيل تعلم اللغة العربية لمختلف المستويات بشكل رقمي، سهل، وفعال.' }}
                </p>
                <div class="mt-4 md:space-y-0 space-y-4">
                    <a href="{{ route('register') }}">
                        <x-btn.slide-dark class="w-64">انضم إلينا </x-btn.slide-dark>
                    </a>
                    <a href="{{ route('courses') }}">
                        <x-btn.slide-light class="w-64">استكشف الدورات </x-btn.slide-light>
                    </a>
                </div>
            </div>
            <div class="md:w-1/2 w-full flex justify-end items-end relative">
                <img src="./assets/illustrations/library.png" alt=" home illustration" class="w-[90%] ">
            </div>
        </div>
        {{-- /////////////////////////////// Our advantages section /////////////////////////////// --}}
        <div class="bg-gray-800 py-8 px-8 md:px-4 lg:px-0">
            <div class="mx-auto max-w-screen-xl">

                <h2 class="text-gray-200 text-center text-5xl mb-6">
                    {{ $homePage->our_features_title ?: 'ميزاتنا الاساسية' }}</h2>
                <div class="flex md:flex-row flex-col jusfify-between items-center gap-8 ">
                    <div
                        class="text-white flex flex-col md:items-start items-center md:text-start text-center gap-4 py-4 md:pe-12 pe-4 ps-4 md:w-1/3 w-full">
                        <div
                            class="w-16 h-16 bg-gradient-to-bl from-indigo-300 to-indigo-700 rounded-lg flex items-center justify-center transition-all duration-300 ease-in-out hover:scale-105 cursor-pointer shadow-blue-400 shadow-2xl ">
                            <div class="w-12">
                                <x-icons.laptop />
                            </div>
                        </div>
                        <h3 class="underline font-judur underline-offset-8">
                            {{ $homePage->first_feature_title ?: 'منصة رقمية' }}</h3>
                        <p class="text-lg font-nitaqat">
                            {{ $homePage->first_feature_content ?: 'منصة رقمية تتيح لك تعلم اللغة العربية في بيئة حديثة ومتطورة' }}
                        </p>
                    </div>
                    <div
                        class="text-white flex flex-col md:items-start items-center md:text-start text-center  gap-4 py-4 md:pe-12 pe-4  ps-4 md:w-1/3 w-full">
                        <div
                            class="w-16 h-16 bg-gradient-to-bl from-indigo-300 to-indigo-700 rounded-lg flex items-center justify-center transition-all duration-300 ease-in-out hover:scale-105 cursor-pointer shadow-blue-400 shadow-2xl ">
                            <div class="w-8">
                                <x-icons.book />
                            </div>
                        </div>
                        <h3 class="underline font-judur underline-offset-8">
                            {{ $homePage->second_feature_title ?: 'أعلى جودة جودة' }}</h3>
                        <p class="text-lg font-nitaqat">
                            {{ $homePage->second_feature_content ?: 'دروس ودورات بمواد تعليمية من طرف مؤطرين بأعلى جودة الكفاءات في اللغة العربية' }}
                        </p>
                    </div>
                    <div
                        class="text-white flex flex-col md:items-start items-center md:text-start text-center  gap-4 py-4 md:pe-12 pe-4  ps-4 md:w-1/3 w-full">
                        <div
                            class="w-16 h-16 bg-gradient-to-bl  from-indigo-300 to-indigo-700  rounded-lg flex items-center justify-center transition-all duration-300 ease-in-out hover:scale-105 cursor-pointer shadow-blue-400 shadow-2xl ">
                            <div class="w-[2.5rem]">
                                <x-icons.help />
                            </div>
                        </div>
                        <h3 class="underline font-judur underline-offset-8">
                            {{ $homePage->third_feature_title ?: ' مواكبة مستمرة' }}</h3>
                        <p class="text-lg font-nitaqat">
                            {{ $homePage->third_feature_content ?: 'مواكبة مستمرة خلال مراحل التعلم من الدورات حتى الاختبارات' }}
                        </p>
                    </div>

                </div>
            </div>

        </div>
        {{-- /////////////////////////////// Our Best Courses section /////////////////////////////// --}}
        <div class="py-16 px-8 md:px-4 lg:px-0">
            <div class="mx-auto max-w-screen-xl">
                <div class="flex  justify-between items-end ">
                    <div>
                        <h2 class="text-primary md:text-5xl text-2xl mb-2 font">أبرز الدورات</h2>
                        <p class="text-gray-500 font-judur text-sm ms-2">الدورات الاكثر تداولا في المنصة</p>
                    </div>
                    <div class="text-lg">
                        <a href="{{ route('courses') }}">
                            <x-btn.scale-dark class="md:h-12 h-8 text-sm md:text-lg  md:w-40 w-32 ">اكتشف المزيد
                                ...</x-btn.scale-dark>
                        </a>
                    </div>

                </div>
                <div class="mt-8 flex md:flex-row flex-col justify-between items-center gap-4  w-full">
                    {{-- card --}}
                    @foreach ($courses as $course)
                        {{-- <p>{{ $course->reviews_avg_rating }}</p>
                    <p>{{ $course->reviews_count }}</p> --}}
                        <x-card.guest-course class="w-[22rem]" :id="$course->id" :title="$course->title" :description="$course->description"
                            :price="$course->price" :type="$course->type" :averagerating="$course->reviews_avg_rating" :totalreviews="$course->reviews_count" />
                    @endforeach
                </div>

            </div>

        </div>
        {{-- /////////////////////////////// Our Best Lessons section /////////////////////////////// --}}
        <div class="py-16 px-8 md:px-4 lg:px-0">
            <div class="mx-auto max-w-screen-xl">
                <div class="flex justify-between items-end ">
                    <div>
                        <h2 class="text-primary md:text-5xl text-2xl mb-2 font">أبرز الشروحات</h2>
                        <p class="text-gray-500 font-judur text-sm ms-2">الشروحات الاكثر تداولا في المنصة</p>
                    </div>
                    <div class="">
                        <a href="{{ route('lessons') }}">
                            <x-btn.scale-dark class="md:h-12 h-8 text-sm md:text-lg  md:w-40 w-32 ">إكتشف المزيد
                                ...</x-btn.scale-dark>
                        </a>
                    </div>

                </div>
                <div class="mt-8 flex md:flex-row flex-col justify-between items-center gap-4 ">
                    {{-- card --}}
                    @foreach ($lessons as $lesson)
                        <x-card.guest-lesson class="w-[22rem]" :id="$lesson->id" :title="$lesson->title" :description="$lesson->description"
                            :monthly-price="$lesson->monthly_price" :annual-price="$lesson->annual_price" :type="$lesson->type" :averagerating="$lesson->reviews_avg_rating"
                            :totalreviews="$lesson->reviews_count" />
                    @endforeach

                </div>

            </div>

        </div>
        {{-- /////////////////////////////// Why Choose us section /////////////////////////////// --}}
        <div class="pt-16 px-8 md:px-4 lg:px-0">
            <div class="mx-auto max-w-screen-xl  relative">
                <h2 class="text-gray-700 text-5xl mb-2 font text-center">لماذا قد <span
                        class="text-primary underline underline-offset-2">تختار</span> منصة يعرب ؟
                </h2>
                <div class="flex justify-center items-center md:mt-64 mt-44 ">
                    <img src="./assets/illustrations/tests.png" alt=""
                        class=" absolute lg:w-44 md:w-36 w-24 md:left-44 left-0 md:top-96 top-64 -rotate-12 animate-flight-left delay-0-200">
                    <img src="./assets/illustrations/instructors.png" alt=""
                        class=" absolute lg:w-44 md:w-36 w-24 left-72 md:top-52 top-40 -rotate-6 animate-flight-left delay-0-1000">
                    <img src="./assets/illustrations/diversity.png" alt=""
                        class=" absolute lg:w-44 md:w-36 w-24 md:left-[45%]  top-32 animate-flight delay-0-600">
                    <img src="./assets/illustrations/arabic.png" alt=""
                        class=" absolute lg:w-44 md:w-36 w-24 right-72 md:top-52 top-40 rotate-6 animate-flight-right delay-0-200">
                    <img src="./assets/illustrations/support.png" alt=""
                        class=" absolute lg:w-44 md:w-36 w-24 md:right-44 right-0 md:top-96 top-64 rotate-12 animate-flight-right delay-0-800">
                    <img src="./assets/illustrations/circle.png" alt=" why choose us illustration" class="md:w-[40%] ">
                </div>
            </div>
        </div>

        {{-- /////////////////////////////// Reviews section /////////////////////////////// --}}
        <div class="bg-pr-500 w-full px-8 md:px-4 lg:px-0">
            <div class="mx-auto max-w-screen-xl flex flex-col justify-start items-start pt-8 ">
                <p class="text-gray-200 text-2xl font-judul">شهادة</p>
                <p class="text-gray-200 text-4xl">الحب من مستخدمي المنصة</p>
            </div>
            <div class="mt-8 md:mr-32 mr-0 flex md:gap-20 gap-2 overflow-scroll w-full no-scrollbar pe-64 pb-12 ">
                @if (!$reviews->isEmpty())
                    @foreach ($reviews as $review)
                        <x-card.review :review="$review->review" :user-name="$review->reviewer_name" :user-img="$review->reviewer_image" :stars="$review->stars" />
                    @endforeach
                @else
                    <x-card.review :review="'دورة ممتازة! المحتوى غني ومفيد للغاية. أنصح الجميع بالالتحاق بها.'" :user-name="'محمد العلي'" :user-img="'./assets/users-img/man-1.webp'" />
                    <x-card.review :review="'استفدت كثيرًا من هذه الدورة، فقد حسّنت مستواي في اللغة العربية بشكل ملحوظ.'" :user-name="'فاطمة الزهراء'" :user-img="'./assets/users-img/woman-1.webp'" />
                    <x-card.review :review="'الشرح كان واضحًا والأساتذة كانوا محترفين. هذه أفضل دورة قمت بها على الإنترنت.'" :user-name="'أحمد السعدي'" :user-img="'./assets/users-img/man-2.webp'" />
                    <x-card.review :review="'الدورات مكثفة ولكنها مفيدة جدًا. ساعدتني كثيرًا في تحسين كتابتي وقراءتي.'" :user-name="'نورة العمري'" :user-img="'./assets/users-img/woman-2.webp'" />
                    <x-card.review :review="'الدورة تحتوي على مواد تعليمية ممتازة وتطبيقات عملية تعزز الفهم.'" :user-name="'خالد البدر'" :user-img="'./assets/users-img/man-3.webp'" />
                    <x-card.review :review="'الدورة تحتوي على مواد تعليمية ممتازة وتطبيقات عملية تعزز الفهم.'" :user-name="'ريم الحربي'" :user-img="'./assets/users-img/woman-3.webp'" />
                    <x-card.review :review="'أنصح بشدة بهذه الدورة لمن يرغب في تعزيز مهاراته اللغوية.'" :user-name="'سعيد الهاشمي'" :user-img="'./assets/users-img/man-4.webp'" />
                    <x-card.review :review="'المنصة سهلة الاستخدام والمحتوى التعليمي رائع. شكرًا لكم!'" :user-name="'ليلى الطيب'" :user-img="'./assets/users-img/woman-4.webp'" />
                    <x-card.review :review="'أحببت الطريقة التي يتم بها تقديم الدورات، فهي تتسم بالوضوح والتفصيل.'" :user-name="'يوسف الأنصاري'" :user-img="'./assets/users-img/man-5.webp'" />
                    <x-card.review :review="'دورة رائعة! تحسنت مهاراتي اللغوية بشكل كبير بعد إتمامها.'" :user-name="'منى الحارثي'" :user-img="'./assets/users-img/woman-2.webp'" />
                @endif

            </div>
        </div>
        {{-- /////////////////////////////// Join us section /////////////////////////////// --}}
        <div class="bg-gray-100 md:pt-28 pt-12 px-8 md:px-4 lg:px-0">
            <div
                class="mx-auto max-w-screen-xl px-8 md:px-4 lg:px-3 flex md:flex-row flex-col justify-between items-center pt-3 ">
                <div
                    class="md:w-1/2 w-full flex flex-col md:items-start items-center gap-6  md:justify-start justify-center md:text-start text-center">

                    @if ($homePage->last_section_title == '' || $homePage->last_section_title == 'إنضم الى منصة يعرب')
                        <h2 class="md:text-6xl lg:text-7xl text-5xl font-hacen">إنضم الى منصة <span
                                class="text-bold text-pr-400 underline underline-offset-[16px]">يعرب</span>
                        </h2>
                    @else
                        <h2 class="md:text-6xl lg:text-7xl text-5xl font-hacen"> {{ $homePage->last_section_title }} </h2>
                    @endif
                    <p class="text-3xl font-nitaqat text-justify">
                        {{ $homePage->last_section_content ?: 'و إبدأ رحلة في تعلم دروس اللغة العربية من خلال منصة يعرب التي توفر حلول 100% رقمية لتسهيل مسارك في التعلم. و بإشراف أجود المؤطرين في المجال، و دعم متجاوب و سريع و باللغة العربية' }}
                    </p>

                    </p>
                    <div class="mt-4">
                        <a href="{{ route('register') }}">
                            <x-btn.anime class="w-64">إنضم الينا الان </x-btn.anime>
                        </a>
                    </div>
                </div>
                <div class="md:w-1/2 w-full flex justify-end items-end relative mt-8 md:mt-0">
                    <img src="./assets/illustrations/looking.png" alt=" home illustration" class="w-[90%] ">
                </div>
            </div>

        </div>
    @endsection()
