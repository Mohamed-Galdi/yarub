@extends('layouts.guest')
@section('content')
    <div class="relative">
        <div style="background-image: url('./assets/images/books-bg.webp')"
            class=" h-[40rem] place-items-left bg-no-repeat bg-cover bg-buttom flex justify-center brightness-50 ">
        </div>
        <h1
            class="text-[10rem] text-white mt-16 mix-blend-diffeerence z-50 absolute top-12 left-[50%] -translate-x-1/2 font-arabic_handwrite">
            إكتشف من نحن
        </h1>

        {{-- Who we are --}}
        <div class="my-16 mx-auto max-w-screen-xl flex gap-12 justify-between items-start">
            <div class="text-start w-1/2 space-y-6">
                <h2 class="text-4xl">فريقنا</h2>
                <p class="text-justify font-judur">
                    تُدار منصة يعرب من طرف خبراء في اللغة العربية، حاصلين على شواهد و جوائز في دروس اللغة العربية. بالإضافة
                    الى فريق من التقنيين الذين يسهرون على إشتغال المنصة على اعلى كفائة و بشكل مستمر و كذالك تطويرها دائما
                    للأفضل. إلى جانب دعم تقني و فني يسهر على تلبية كل متطلباتكم على مدار الساعة.
                </p>
            </div>
            <div class="text-start w-1/2 space-y-6">
                <h2 class="text-4xl">هدفنا</h2>
                <p class="text-justify font-judur">
                    رفع المستوى التحصيلي للطلاب والطاالبات في اختبارات القدرات المحلية الجزء اللفظي حصول معلمي و معلمات
                    اللغة
                    العربية على درجات عالية في اختبارات الرخصة المهنية رفع المستوى الاستيعابي والاعتماد على الذاكرة الدائمة
                    في شروحات مواد اللغة العربية للمرحلة الثانوية
                </p>
            </div>
        </div>
        {{-- Our partners --}}
        <div class="bg-pr-800">
            <div class="py-16 mx-auto max-w-screen-xl text-center">
                <h2 class="text-gray-200 text-4xl ">شركاء النجاح</h2>
                <div class="flex  mt-8 w-full justify-center items-center gap-12 text-2xl text-nowrap">
                    <a href="https://google.com/" target="_blank"
                        class="p-8 w-72 h-24 bg-gray-200 rounded-xl cursor-pointer shadow-blue-500 shadow-lg hover:scale-[0.99] hover:shadow-none transition-all duration-300 ease-in-out  ">
                        <p> مؤسسة سراة بروداكشن</p>
                    </a>
                    <a href="https://wajasportal.com/" target="_blank"
                        class="p-8 w-72 h-24 bg-gray-200 rounded-xl cursor-pointer shadow-blue-500 shadow-lg hover:scale-[0.99] hover:shadow-none transition-all duration-300 ease-in-out  ">
                        <p>منصة وجس للتقنيات</p>
                    </a>
                </div>
            </div>
        </div>

        {{-- //////////// FAQs /////////////////// --}}
        <div class="mx-auto max-w-screen-xl pt-16">
            <div class="flex justify-between">
                <div class="w-1/4 space-y-4">
                    <h2 class="text-6xl">أجوبة</h2>
                    <p class="text-2xl font-nitaqat font-bold">لأسئلة قد تخطر ببالك</p>
                </div>
                <div class="w-3/4">
                    <ul class="flex flex-col">
                        {{-- ///////////////////////////// Question 1 ///////////////////////////////////////////////// --}}
                        <li class="bg-pr-500 my-2 shadow-lg rounded-xl overflow-hidden" x-data="accordion(1)">
                            <h2 @click="handleClick()"
                                class="flex flex-row justify-between items-center p-3 cursor-pointer">
                                <span class="font-pr text-2xl text-gray-200"> كيف يمكنني التسجيل في الدورة؟</span>
                                <svg :class="handleRotate()"
                                    class="fill-current text-gray-100  h-10 min-w-10 transform transition-transform duration-500"
                                    viewBox="0 0 20 20">
                                    <path
                                        d="M13.962,8.885l-3.736,3.739c-0.086,0.086-0.201,0.13-0.314,0.13S9.686,12.71,9.6,12.624l-3.562-3.56C5.863,8.892,5.863,8.611,6.036,8.438c0.175-0.173,0.454-0.173,0.626,0l3.25,3.247l3.426-3.424c0.173-0.172,0.451-0.172,0.624,0C14.137,8.434,14.137,8.712,13.962,8.885 M18.406,10c0,4.644-3.763,8.406-8.406,8.406S1.594,14.644,1.594,10S5.356,1.594,10,1.594S18.406,5.356,18.406,10 M17.521,10c0-4.148-3.373-7.521-7.521-7.521c-4.148,0-7.521,3.374-7.521,7.521c0,4.147,3.374,7.521,7.521,7.521C14.148,17.521,17.521,14.147,17.521,10">
                                    </path>
                                </svg>
                            </h2>
                            <div x-ref="tab" :style="handleToggle()"
                                class="border-l-8 border-pr-500 overflow-hidden max-h-0 duration-500 transition-all bg-gray-200">
                                <p class="p-3 text-gray-800 font-sec text-xl "> يمكنك التسجيل في الدورة من خلال زيارة موقعنا
                                    الإلكتروني والضغط على زر "التسجيل" في الصفحة الرئيسية. بعد ذلك، اتبع الخطوات المطلوبة
                                    لإكمال عملية التسجيل.
                                </p>
                            </div>
                        </li>
                        {{-- /////////////////////////// Question 2 ///////////////////////////////////////////////// --}}
                        <li class="bg-pr-500 my-2 shadow-lg rounded-xl overflow-hidden" x-data="accordion(2)">
                            <h2 @click="handleClick()"
                                class="flex flex-row justify-between items-center p-3 cursor-pointer">
                                <span class="font-pr text-2xl text-gray-200"> ما هي طريقة الدفع المتاحة؟ </span>
                                <svg :class="handleRotate()"
                                    class="fill-current text-gray-100  h-10 min-w-10 transform transition-transform duration-500"
                                    viewBox="0 0 20 20">
                                    <path
                                        d="M13.962,8.885l-3.736,3.739c-0.086,0.086-0.201,0.13-0.314,0.13S9.686,12.71,9.6,12.624l-3.562-3.56C5.863,8.892,5.863,8.611,6.036,8.438c0.175-0.173,0.454-0.173,0.626,0l3.25,3.247l3.426-3.424c0.173-0.172,0.451-0.172,0.624,0C14.137,8.434,14.137,8.712,13.962,8.885 M18.406,10c0,4.644-3.763,8.406-8.406,8.406S1.594,14.644,1.594,10S5.356,1.594,10,1.594S18.406,5.356,18.406,10 M17.521,10c0-4.148-3.373-7.521-7.521-7.521c-4.148,0-7.521,3.374-7.521,7.521c0,4.147,3.374,7.521,7.521,7.521C14.148,17.521,17.521,14.147,17.521,10">
                                    </path>
                                </svg>
                            </h2>
                            <div x-ref="tab" :style="handleToggle()"
                                class="border-l-8 border-pr-500 overflow-hidden max-h-0 duration-500 transition-all bg-gray-200">
                                <p class="p-3 text-gray-800 font-sec text-xl "> نوفر عدة طرق للدفع مثل البطاقات الائتمانية،
                                    الدفع عبر PayPal، والتحويلات البنكية. يمكنك اختيار الطريقة التي تناسبك عند إتمام عملية
                                    الشراء.
                                </p>
                            </div>
                        </li>
                        {{-- /////////////////////////// Question 3 ///////////////////////////////////////////////// --}}
                        <li class="bg-pr-500 my-2 shadow-lg rounded-xl overflow-hidden" x-data="accordion(3)">
                            <h2 @click="handleClick()"
                                class="flex flex-row justify-between items-center p-3 cursor-pointer">
                                <span class="font-pr text-2xl text-gray-200"> هل يمكنني استرداد المبلغ المدفوع إذا لم أكن
                                    راضياً عن الدورة؟</span>
                                <svg :class="handleRotate()"
                                    class="fill-current text-gray-100  h-10 min-w-10 transform transition-transform duration-500"
                                    viewBox="0 0 20 20">
                                    <path
                                        d="M13.962,8.885l-3.736,3.739c-0.086,0.086-0.201,0.13-0.314,0.13S9.686,12.71,9.6,12.624l-3.562-3.56C5.863,8.892,5.863,8.611,6.036,8.438c0.175-0.173,0.454-0.173,0.626,0l3.25,3.247l3.426-3.424c0.173-0.172,0.451-0.172,0.624,0C14.137,8.434,14.137,8.712,13.962,8.885 M18.406,10c0,4.644-3.763,8.406-8.406,8.406S1.594,14.644,1.594,10S5.356,1.594,10,1.594S18.406,5.356,18.406,10 M17.521,10c0-4.148-3.373-7.521-7.521-7.521c-4.148,0-7.521,3.374-7.521,7.521c0,4.147,3.374,7.521,7.521,7.521C14.148,17.521,17.521,14.147,17.521,10">
                                    </path>
                                </svg>
                            </h2>
                            <div x-ref="tab" :style="handleToggle()"
                                class="border-l-8 border-pr-500 overflow-hidden max-h-0 duration-500 transition-all bg-gray-200">
                                <p class="p-3 text-gray-800 font-sec text-xl "> نعم، نوفر سياسة استرداد خلال 14 يومًا من
                                    تاريخ الشراء. إذا لم تكن راضياً عن الدورة، يمكنك تقديم طلب لاسترداد المبلغ عبر خدمة
                                    العملاء. </p>
                            </div>
                        </li>
                        {{-- /////////////////////////// Question 4 ///////////////////////////////////////////////// --}}
                        <li class="bg-pr-500 my-2 shadow-lg rounded-xl overflow-hidden" x-data="accordion(4)">
                            <h2 @click="handleClick()"
                                class="flex flex-row justify-between items-center p-3 cursor-pointer">
                                <span class="font-pr text-2xl text-gray-200"> هل يمكنني الوصول إلى محتوى الدورة بعد
                                    انتهائها؟</span>
                                <svg :class="handleRotate()"
                                    class="fill-current text-gray-100  h-10 min-w-10 transform transition-transform duration-500"
                                    viewBox="0 0 20 20">
                                    <path
                                        d="M13.962,8.885l-3.736,3.739c-0.086,0.086-0.201,0.13-0.314,0.13S9.686,12.71,9.6,12.624l-3.562-3.56C5.863,8.892,5.863,8.611,6.036,8.438c0.175-0.173,0.454-0.173,0.626,0l3.25,3.247l3.426-3.424c0.173-0.172,0.451-0.172,0.624,0C14.137,8.434,14.137,8.712,13.962,8.885 M18.406,10c0,4.644-3.763,8.406-8.406,8.406S1.594,14.644,1.594,10S5.356,1.594,10,1.594S18.406,5.356,18.406,10 M17.521,10c0-4.148-3.373-7.521-7.521-7.521c-4.148,0-7.521,3.374-7.521,7.521c0,4.147,3.374,7.521,7.521,7.521C14.148,17.521,17.521,14.147,17.521,10">
                                    </path>
                                </svg>
                            </h2>
                            <div x-ref="tab" :style="handleToggle()"
                                class="border-l-8 border-pr-500 overflow-hidden max-h-0 duration-500 transition-all bg-gray-200">
                                <p class="p-3 text-gray-800 font-sec text-xl "> نعم، بمجرد تسجيلك في الدورة، سيكون لديك وصول
                                    دائم إلى المحتوى. يمكنك العودة إلى المواد والدروس في أي وقت تحتاج إليه.
                            </div>
                        </li>
                        {{-- /////////////////////////// Question 5 ///////////////////////////////////////////////// --}}
                        <li class="bg-pr-500 my-2 shadow-lg rounded-xl overflow-hidden" x-data="accordion(5)">
                            <h2 @click="handleClick()"
                                class="flex flex-row justify-between items-center p-3 cursor-pointer">
                                <span class="font-pr text-2xl text-gray-200">هل هناك دعم فني متاح في حال واجهت مشاكل تقنية؟
                                </span>
                                <svg :class="handleRotate()"
                                    class="fill-current text-gray-100  h-10 min-w-10 transform transition-transform duration-500"
                                    viewBox="0 0 20 20">
                                    <path
                                        d="M13.962,8.885l-3.736,3.739c-0.086,0.086-0.201,0.13-0.314,0.13S9.686,12.71,9.6,12.624l-3.562-3.56C5.863,8.892,5.863,8.611,6.036,8.438c0.175-0.173,0.454-0.173,0.626,0l3.25,3.247l3.426-3.424c0.173-0.172,0.451-0.172,0.624,0C14.137,8.434,14.137,8.712,13.962,8.885 M18.406,10c0,4.644-3.763,8.406-8.406,8.406S1.594,14.644,1.594,10S5.356,1.594,10,1.594S18.406,5.356,18.406,10 M17.521,10c0-4.148-3.373-7.521-7.521-7.521c-4.148,0-7.521,3.374-7.521,7.521c0,4.147,3.374,7.521,7.521,7.521C14.148,17.521,17.521,14.147,17.521,10">
                                    </path>
                                </svg>
                            </h2>
                            <div x-ref="tab" :style="handleToggle()"
                                class="border-l-8 border-pr-500 overflow-hidden max-h-0 duration-500 transition-all bg-gray-200">
                                <p class="p-3 text-gray-800 font-sec text-xl "> نعم، لدينا فريق دعم فني متاح على مدار الساعة
                                    لمساعدتك في حل أي مشكلة تقنية قد تواجهها. يمكنك التواصل معنا عبر البريد الإلكتروني أو
                                    الدردشة المباشرة.</p>
                            </div>
                        </li>
                        {{-- /////////////////////////// Question 6 ///////////////////////////////////////////////// --}}
                        <li class="bg-pr-500 my-2 shadow-lg rounded-xl overflow-hidden" x-data="accordion(6)">
                            <h2 @click="handleClick()"
                                class="flex flex-row justify-between items-center p-3 cursor-pointer">
                                <span class="font-pr text-2xl text-gray-200">هل يتم تقديم شهادة بعد الانتهاء من
                                    الدورة؟</span>
                                <svg :class="handleRotate()"
                                    class="fill-current text-gray-100  h-10 min-w-10 transform transition-transform duration-500"
                                    viewBox="0 0 20 20">
                                    <path
                                        d="M13.962,8.885l-3.736,3.739c-0.086,0.086-0.201,0.13-0.314,0.13S9.686,12.71,9.6,12.624l-3.562-3.56C5.863,8.892,5.863,8.611,6.036,8.438c0.175-0.173,0.454-0.173,0.626,0l3.25,3.247l3.426-3.424c0.173-0.172,0.451-0.172,0.624,0C14.137,8.434,14.137,8.712,13.962,8.885 M18.406,10c0,4.644-3.763,8.406-8.406,8.406S1.594,14.644,1.594,10S5.356,1.594,10,1.594S18.406,5.356,18.406,10 M17.521,10c0-4.148-3.373-7.521-7.521-7.521c-4.148,0-7.521,3.374-7.521,7.521c0,4.147,3.374,7.521,7.521,7.521C14.148,17.521,17.521,14.147,17.521,10">
                                    </path>
                                </svg>
                            </h2>
                            <div x-ref="tab" :style="handleToggle()"
                                class="border-l-8 border-pr-500 overflow-hidden max-h-0 duration-500 transition-all bg-gray-200">
                                <p class="p-3 text-gray-800 font-sec text-xl "> نعم، عند إتمام الدورة بنجاح، ستحصل على شهادة
                                    معتمدة تثبت مشاركتك وإتمامك لجميع متطلبات الدورة. </p>
                            </div>
                        </li>


                    </ul>
                </div>
            </div>

        </div>

        {{-- //////////////////////////// Wanna know more //////////////////////////// --}}
        <div>
            <!-- Hero -->
            <div class="relative overflow-hidden">
                <div class="max-w-[85rem] mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-24">
                    <div class="text-center">
                        <h1 class="text-4xl sm:text-6xl font-bold text-gray-800">
                            تريد معرفة المزيد ؟
                        </h1>

                        <p class="mt-3 text-gray-600">
                            أترك لنا سؤالك هنا 👇 </p>

                        <div class="mt-7 sm:mt-12 mx-auto max-w-xl relative">
                            <!-- Form -->
                            <form>
                                <div
                                    class="relative z-10 flex items-center space-x-3 p-3 bg-white border rounded-lg shadow-lg shadow-gray-100">
                                    <div class="flex-[1_0_0%] ">
                                        <label for="hs-search-article-1"
                                            class="block text-sm text-gray-700 font-medium"><span class="sr-only">أكتب سؤالك
                                                هنا...</span></label>
                                        <input type="email" name="hs-search-article-1" id="hs-search-article-1"
                                            class="py-2.5 px-4 block w-full border-transparent rounded-lg focus:border-transparent focus:ring-transparent"
                                            placeholder="أكتب سؤالك هنا...">
                                    </div>
                                    <div class="flex-[0_0_auto] ">
                                        <x-btn.scale-dark class="w-32 gap-3">
                                            <p>إرسال</p>
                                            <x-icons.send class="w-4 h-4" />
                                        </x-btn.scale-dark></>
                                    </div>
                                </div>
                            </form>

                            <!-- End Form -->

                            <!-- SVG Element -->
                            <div class="hidden md:block absolute top-0 end-0 -translate-y-12 translate-x-20">
                                <svg class="w-16 h-auto text-orange-500" width="121" height="135"
                                    viewBox="0 0 121 135" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M5 16.4754C11.7688 27.4499 21.2452 57.3224 5 89.0164" stroke="currentColor"
                                        stroke-width="10" stroke-linecap="round" />
                                    <path d="M33.6761 112.104C44.6984 98.1239 74.2618 57.6776 83.4821 5"
                                        stroke="currentColor" stroke-width="10" stroke-linecap="round" />
                                    <path d="M50.5525 130C68.2064 127.495 110.731 117.541 116 78.0874"
                                        stroke="currentColor" stroke-width="10" stroke-linecap="round" />
                                </svg>
                            </div>
                            <!-- End SVG Element -->

                            <!-- SVG Element -->
                            <div class="hidden md:block absolute bottom-0 start-0 translate-y-10 -translate-x-32">
                                <svg class="w-40 h-auto text-cyan-500" width="347" height="188"
                                    viewBox="0 0 347 188" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M4 82.4591C54.7956 92.8751 30.9771 162.782 68.2065 181.385C112.642 203.59 127.943 78.57 122.161 25.5053C120.504 2.2376 93.4028 -8.11128 89.7468 25.5053C85.8633 61.2125 130.186 199.678 180.982 146.248L214.898 107.02C224.322 95.4118 242.9 79.2851 258.6 107.02C274.299 134.754 299.315 125.589 309.861 117.539L343 93.4426"
                                        stroke="currentColor" stroke-width="7" stroke-linecap="round" />
                                </svg>
                            </div>
                            <!-- End SVG Element -->
                        </div>

                    </div>
                </div>
            </div>
            <!-- End Hero -->
        </div>
    </div>

    {{-- //////////// FAQs script /////////////////// --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('accordion', {
                tab: 1 // Initialize with the first tab open
            });

            Alpine.data('accordion', (idx) => ({
                init() {
                    this.idx = idx;
                },
                idx: -1,
                handleClick() {
                    this.$store.accordion.tab = this.$store.accordion.tab === this.idx ? 0 : this.idx;
                },
                handleRotate() {
                    return this.$store.accordion.tab === this.idx ? 'rotate-180' : '';
                },
                handleToggle() {
                    return this.$store.accordion.tab === this.idx ?
                        `max-height: ${this.$refs.tab.scrollHeight}px` : '';
                }
            }));
        })
    </script>
@endsection()
