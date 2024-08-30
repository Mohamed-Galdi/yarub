@extends('layouts.admin')
@section('content')
    <div class="flex flex-col justify-start items-start min-h-screen space-y-6">
        {{-- ////////////////////// Banner ///////////////////// --}}
        <div
            class="w-full lg:h-64 rounded-2xl bg-gradient-to-b from-indigo-400 to-blue-700 shadow-xl flex lg:flex-row flex-col justify-start items-start lg:ps-0 ps-6">
            <div class="h-full lg:w-1/3 w-full  lg:relative flex flex-col justify-center items-center pe-3 ">
                <img src="{{ asset('assets/illustrations/admin_panel_character_cropped.svg') }}" alt="الصورة الرمزية"
                    class="lg:h-full h-56 lg:absolute -bottom-4 right-0 object-cover" />
                <h1
                    class="lg:absolute top-12 right-32 text-3xl  text-gray-200 text-center text-wrap w-2/3 font-judur font-bold pe-3">
                    لوحة تحكم منصة يعرب</h1>
            </div>
            <div class="h-full lg:w-2/3 w-full pl-6 pt-6  grid md:grid-cols-2 grid-cols-1  gap-4 pb-8">
                <div
                    class="w-full  rounded-lg bg-gradient-to-b border-2 border-gray-100 overflow-hidden from-green-400 to-green-600 flex justify-between items-start py-2 px-4  ">
                    <div class="w-1/4 h-full  flex justify-start items-start ">
                        <x-icons.students class="w-full h-full text-green-800" />
                    </div>
                    <div class="w-3/4  flex  justify-center items-center h-full ">
                        <div class="w-fit  flex flex-col justify-center items-center ">
                            <h1 class="lg:text-4xl text-2xl text-nowrap truncate text-green-900">المشتركين</h1>
                            <p class="text-gray-50 text-3xl">{{ $students_count }}</p>
                        </div>
                    </div>
                </div>
                <div
                    class="w-full  rounded-lg bg-gradient-to-b border-2 border-gray-100 overflow-hidden from-red-400 to-red-500 flex justify-start items-start  py-2 px-4  ">
                    <div class="w-1/4 h-full  flex justify-start items-start ">
                        <x-icons.course class="w-full h-full text-red-800" />
                    </div>
                    <div class="w-3/4  flex  justify-center items-center h-full ">
                        <div class="w-fit  flex flex-col justify-center items-center ">
                            <h1 class="lg:text-3xl text-2xl text-nowrap truncate text-red-800">الدورات و الشروحات</h1>
                            <p class="text-gray-50 text-3xl">{{ $courses_and_lessons_count }}</p>
                        </div>
                    </div>
                </div>
                <div
                    class="w-full  rounded-lg bg-gradient-to-b border-2 border-gray-100 overflow-hidden from-blue-500 to-blue-600 flex justify-start items-start  py-2 px-4  ">
                    <div class="w-1/4 h-full  flex justify-center items-center ">
                        <x-icons.eye class="w-[4.5rem] h-[4.5rem] text-blue-900" />
                    </div>
                    <div class="w-3/4  flex  justify-center items-center h-full ">
                        <div class="w-fit  flex flex-col justify-center items-center ">
                            <h1 class="lg:text-4xl text-2xl text-nowrap truncate text-blue-900">زوار المنصة</h1>
                            <p class="text-gray-50 text-3xl">{{ $visitors_count }}</p>
                        </div>
                    </div>

                </div>
                <div
                    class="w-full  rounded-lg bg-gradient-to-b border-2 border-gray-100 overflow-hidden from-warning-300 to-yellow-400 flex justify-start items-start  py-2 px-4  ">
                    <div class="w-1/4 h-full  flex justify-start items-start ">
                        <x-icons.money-bag class="w-full h-full text-warning-800" />
                    </div>
                    <div class="w-3/4  flex  justify-center items-center h-full ">
                        <div class="w-fit  flex flex-col justify-center items-center ">
                            <h1 class="lg:text-4xl text-2xl text-nowrap truncate text-warning-800">المداخيل</h1>
                            <p class="text-white text-3xl align-middle">{{ $sales_count }} <span class="text-sm">(ريال
                                    سعودي)</span></p>
                        </div>
                    </div>
                </div>



            </div>

        </div>


        {{-- //////////////////// Overview Charts //////////////////// --}}
        {{-- <div dir="rtl" class="w-full grid grid-cols-1  lg:grid-cols-2 xl:grid-cols-4  gap-8 mb-12  pb-12">
            <x-charts.overview :id="'users'" :title="' الزوار'" :value="5720" :trend="'+ 63,6 %'" :color="'text-blue-500'"
                icon="icons.eye" />
            <x-charts.overview :id="'students'" :title="'المشتركين '" :value="120" :trend="'+ 13,6 %'" :color="'text-red-500'"
                icon="icons.students" />
            <x-charts.overview :id="'courses'" :title="'الدورات  '" :value="12" :trend="'+ 20 %'"
                :color="'text-yellow-500'" icon="icons.lesson" />
            <x-charts.overview :id="'sales'" :title="'المبيعات'" :value="3000" :trend="'+ 3,6 %'"
                :color="'text-green-500'" icon="icons.money-bag" />
        </div> --}}

        {{-- //////////////////// Detail Charts //////////////////// --}}
        <div class="w-full bg-slate-300 rounded-lg min-h-screen shadow-md  border border-white lg:px-12 px-2 py-3">
            {{-- <p class="text-3xl text-gray-700 text-center  ">الإحصائيات المفصلة</p> --}}
            {{-- //////////////////// Users //////////////////// --}}
            <div class="mt-6 ">
                <div class="rounded-2xl overflow-hidden">
                    <div id="usersDetail"></div>
                </div>
            </div>
            {{-- //////////////////// Courses //////////////////// --}}
            <div class="mt-12">
                <p class="text-2xl text-indigo-500 ">الدورات و الشروحات </p>
                <div class="w-full h-1 rounded-lg bg-gradient-to-r from-pr-500 to-indigo-500"></div>
                <div class="mt-12">
                    <div id="coursesDetail"></div>
                </div>
            </div>
            {{-- //////////////////// Courses //////////////////// --}}
            <div class="mt-12">
                <p class="text-2xl text-indigo-500 "> توزيع المبيعات</p>
                <div class="w-full h-1 rounded-lg bg-gradient-to-r from-pr-500 to-indigo-500"></div>
                <div class="mt-12 flex lg:flex-row flex-col justify-start items-start w-full gap-6">
                    <div
                        class="bg-gray-100 rounded-xl lg:w-1/2 w-full md:h-[34rem] h-[24rem] py-6 px-4 space-y-4 lg:space-y-0 ">
                        <p class="text-indigo-500 font-judur text-xl font-bold">الدورات الاكثر مبيعا</p>
                        <div id="CoursesalesDetail"></div>
                    </div>
                    <div
                        class="bg-gray-100 rounded-xl lg:w-1/2 w-full md:h-[34rem] h-[24rem] py-6 px-4 space-y-4 lg:space-y-0 ">
                        <p class="text-indigo-500 font-judur text-xl font-bold">الشروحات الاكثر مبيعا</p>
                        <div id="LessonsalesDetail"></div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        // ///////////////////////////////////////////////////////////// Overview Charts //////////////////////////////////////////////////
        function createOverviewAreaChart(chartId, seriesName, seriesData, categories, color) {
            const options = {
                series: [{
                    name: seriesName,
                    data: seriesData
                }],
                chart: {
                    height: 150,
                    type: 'area',
                    toolbar: {
                        show: false
                    },
                    fontFamily: 'judur, Arial, sans-serif'

                },
                colors: [color],
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth'
                },
                xaxis: {
                    categories: categories
                },
                yaxis: {
                    show: false
                },
                grid: {
                    show: false
                },
            };

            if (document.getElementById(chartId) && typeof ApexCharts !== 'undefined') {
                const chart = new ApexCharts(document.getElementById(chartId), options);
                chart.render();
            }
        }
        const seriesData = [
            [31, 40, 28, 51, 42],
            [0, 3, 4, 11, 12],
            [1, 2, 5, 15, 19],
            [10, 20, 15, 30, 25]
        ];

        const categories = ["يناير", "فبراير", "مارس", "أبريل", "مايو", "يونيو", "يوليو", "أغسطس", "سبتمبر", "أكتوبر",
            "نوفمبر", "ديسمبر"
        ];

        const chartConfigs = [{
                id: 'users',
                name: 'زائر',
                color: '#008FFB' // blue

            },
            {
                id: 'students',
                name: 'طلاب',
                color: '#FF4560' // red

            },
            {
                id: 'courses',
                name: 'دورات',
                color: '#FEB019' // yellow

            },
            {
                id: 'sales',
                name: 'مبيعات',
                color: '#00E396' // green

            }
        ];
        chartConfigs.forEach((config, index) => {
            createOverviewAreaChart(config.id, config.name, seriesData[index], categories, config.color);
        });
        // ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


        // ///////////////////////////////////////// Detail Charts //////////////////////////////////////////////////
        // ///////////////// Users and Students /////////////////
        function createDetailUsersChart() {
            const options = {
                series: [{
                    name: 'زوار المنصة',
                    type: 'column',
                    data: [440, 505, 414, 671, 227, 413, 201],
                }, {
                    name: 'المشتركين الجدد',
                    type: 'line',
                    data: [23, 25, 20, 37, 18, 22, 17]
                }],
                chart: {
                    height: 400,
                    type: 'line',
                    background: '#fff',
                    locales: [{
                        "name": "ar",
                        "options": {
                            "toolbar": {
                                "exportToSVG": "تحميل الصورة SVG",
                                "exportToPNG": "تحميل الصورة PNG",
                                "exportToCSV": "تصدير البيانات  Excel",
                            }
                        }
                    }],
                    defaultLocale: "ar",
                    zoom: {
                        enabled: false
                    },
                    fontFamily: 'judur, Arial, sans-serif'

                },
                stroke: {
                    width: [0, 4]
                },
                title: {
                    align: 'center',
                    text: 'الزوار و المشتركين اخر أسبوع'
                },
                colors: ['#43399f', '#92a3fe'],
                dataLabels: {
                    enabled: true,
                    enabledOnSeries: [1]
                },
                // arabic weekdays days
                labels: ['الاحد', 'الاثنين', 'الثلاثاء', 'الاربعاء', 'الخميس', 'الجمعة', 'السبت'],
                grid: {
                    show: true,
                },
                yaxis: [{
                    show: false,

                }, {
                    opposite: true,
                    title: {
                        text: ' المشتركين الجدد'
                    }
                }]
            };

            var chart = new ApexCharts(document.querySelector("#usersDetail"), options);
            chart.render();
        }
        createDetailUsersChart();

        // ///////////////// Courses and Lessons /////////////////
        function createDetailCoursesChart() {
            var options = {
                series: [{
                        name: "الدورات",
                        data: [28, 29, 33, 36, 32, 32, 33],
                    },
                    {
                        name: "الشروحات",
                        data: [12, 11, 14, 18, 17, 13, 13],
                    }
                ],
                chart: {
                    height: 450,
                    type: 'line',
                    background: '#fff',

                    dropShadow: {
                        enabled: true,
                        color: '#000',
                        top: 18,
                        left: 7,
                        blur: 10,
                        opacity: 0.2
                    },
                    zoom: {
                        enabled: false
                    },
                    toolbar: {
                        show: true
                    },
                    locales: [{
                        "name": "ar",
                        "options": {
                            "toolbar": {
                                "exportToSVG": "تحميل الصورة SVG",
                                "exportToPNG": "تحميل الصورة PNG",
                                "exportToCSV": "تصدير البيانات  Excel",
                            }
                        }
                    }],
                    defaultLocale: "ar",
                    fontFamily: 'judur, Arial, sans-serif'
                },

                colors: ['#43399f', '#92a3fe'],
                dataLabels: {
                    enabled: true,
                },
                stroke: {
                    curve: 'smooth'
                },
                title: {
                    text: 'مبيعات الدورات و إشتراكات الشروحات',
                    align: 'center'
                },
                grid: {
                    color: '#fff'
                },
                markers: {
                    size: 1
                },
                xaxis: {
                    categories: ['01 يناير ', '02 فبراير ', '03 مارس ', '04 أبريل ', '05 مايو ',
                        '06 يونيو ',
                        '07 يوليو ', '08 أغسطس ', '09 سبتمبر ', '10 أكتوبر ', '11 نوفمبر ',
                        '12 ديسمبر '
                    ],
                    title: {
                        text: ' الشهر'
                    }
                },
                yaxis: {
                    show: false,
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'right',
                    floating: true,
                    offsetY: -25,
                    offsetX: -5
                }
            };

            var chart = new ApexCharts(document.querySelector("#coursesDetail"), options);
            chart.render();
        }
        createDetailCoursesChart();

        // /////////////////////// Sales //////////////////////////
        function createDetailCoursesSalesChart() {
            var options = {
                series: [28, 12, 8, 5],
                chart: {
                    width: '100%',
                    height: '100%',
                    type: 'pie',
                    toolbar: {
                        show: true
                    },
                    locales: [{
                        "name": "ar",
                        "options": {
                            "toolbar": {
                                "exportToSVG": "تحميل الصورة SVG",
                                "exportToPNG": "تحميل الصورة PNG",
                                "exportToCSV": "تصدير البيانات  Excel",
                            }
                        }
                    }],
                    defaultLocale: "ar",
                    fontFamily: 'judur, Arial, sans-serif'


                },
                labels: [
                    'مقدمة يَعرُب في التأسيس للقدرات',
                    'التعريف بأقسام اختبار القدرات',
                    'المفردة الشاذة',
                    'الارتباط والاختلاف '

                ],
                theme: {
                    monochrome: {
                        enabled: true,
                        color: '#43399f',
                    },
                },
                plotOptions: {
                    pie: {
                        dataLabels: {
                            offset: -5,
                        },
                    },
                },
                grid: {
                    padding: {
                        top: 0,
                        bottom: 0,
                        left: 0,
                        right: 0,
                    },
                },
                dataLabels: {
                    formatter(val, opts) {
                        const name = opts.w.globals.labels[opts.seriesIndex]
                        return [name, val.toFixed(1) + '%']
                    },
                },
                legend: {
                    show: false,
                },
            };

            var chart = new ApexCharts(document.querySelector("#CoursesalesDetail"), options);
            chart.render();
        }
        createDetailCoursesSalesChart();

        function createDetailLessonsSalesChart() {
            var options = {
                series: [12, 8, 2, 6],
                chart: {
                    width: '100%',
                    height: '100%',
                    type: 'pie',
                    toolbar: {
                        show: true
                    },
                    locales: [{
                        "name": "ar",
                        "options": {
                            "toolbar": {
                                "exportToSVG": "تحميل الصورة SVG",
                                "exportToPNG": "تحميل الصورة PNG",
                                "exportToCSV": "تصدير البيانات  Excel",
                            }
                        }
                    }],
                    defaultLocale: "ar",
                    fontFamily: 'judur, Arial, sans-serif'

                },
                labels: [
                    'الخيل والليل لامروء القيس',
                    'التعريف بأقسام اختبار القدرات',
                    'همزة الوصل',
                    'التوابع'

                ],
                theme: {
                    monochrome: {
                        enabled: true,
                        color: '#43399f',
                    },
                },
                plotOptions: {
                    pie: {
                        dataLabels: {
                            offset: -5,
                        },
                    },
                },
                grid: {
                    padding: {
                        top: 0,
                        bottom: 0,
                        left: 0,
                        right: 0,
                    },
                },
                dataLabels: {
                    formatter(val, opts) {
                        const name = opts.w.globals.labels[opts.seriesIndex]
                        return [name, val.toFixed(1) + '%']
                    },
                },
                legend: {
                    show: false,
                },
            };

            var chart = new ApexCharts(document.querySelector("#LessonsalesDetail"), options);
            chart.render();
        }
        createDetailLessonsSalesChart();
    </script>
@endsection()
