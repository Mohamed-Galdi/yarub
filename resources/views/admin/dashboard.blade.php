@extends('layouts.admin')
@section('content')
    <div class="flex flex-col justify-start items-start min-h-screen space-y-6">

        {{-- //////////////////// Title //////////////////// --}}
        <div class="w-full md:py-2 py-4 space-y-1 font-judur">
            <h1
                class="text-4xl font-bold text-gradient-to-r bg-gradient-to-r from-pr-500 to-indigo-500 p-2 w-fit text-transparent bg-clip-text ">
                إحصائيات المنصة</h1>
            <div class="w-full h-1 rounded-lg bg-gradient-to-r from-pr-500 to-indigo-500"></div>
        </div>

        {{-- //////////////////// Overview Charts //////////////////// --}}
        <div dir="rtl" class="w-full grid grid-cols-1  lg:grid-cols-2 xl:grid-cols-4  gap-8 mb-12  pb-12">
            {{-- //////////////////// Users //////////////////// --}}
            <x-charts.overview :id="'users'" :title="' الزوار'" :value="5720" :trend="'+ 63,6 %'" :color="'text-blue-500'"
                icon="icons.eye" />
            {{-- //////////////////// Students //////////////////// --}}
            <x-charts.overview :id="'students'" :title="'الطلاب '" :value="120" :trend="'+ 13,6 %'" :color="'text-red-500'"
                icon="icons.students" />

            {{-- //////////////////// Courses //////////////////// --}}
            <x-charts.overview :id="'courses'" :title="'الدورات  '" :value="12" :trend="'+ 20 %'"
                :color="'text-yellow-500'" icon="icons.lesson" />
            {{-- //////////////////// Sales //////////////////// --}}
            <x-charts.overview :id="'sales'" :title="'المبيعات'" :value="3000" :trend="'+ 3,6 %'"
                :color="'text-green-500'" icon="icons.money-bag" />
        </div>

        {{-- //////////////////// Detail Charts //////////////////// --}}
        <div class="w-full bg-gray-200 rounded-lg min-h-screen shadow-md  border border-white lg:px-12 px-2 py-6 ">
            <p class="text-3xl text-gray-700 text-center  ">الإحصائيات المفصلة</p>
            {{-- //////////////////// Users //////////////////// --}}
            <div class="mt-12">
                <p class="text-2xl text-indigo-500 ">الزوار </p>
                <div class="w-full h-1 rounded-lg bg-gradient-to-r from-pr-500 to-indigo-500"></div>
                <div class="mt-12">
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
                    <div class="bg-gray-100 rounded-xl lg:w-1/2 w-full md:h-[34rem] h-[24rem] py-6 px-4 space-y-4 lg:space-y-0 ">
                        <p class="text-indigo-500 font-judur text-xl font-bold">الدورات الاكثر مبيعا</p>
                        <div id="CoursesalesDetail"></div>
                    </div>
                    <div class="bg-gray-100 rounded-xl lg:w-1/2 w-full md:h-[34rem] h-[24rem] py-6 px-4 space-y-4 lg:space-y-0 ">
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
            [31, 40, 28, 51, 42 ],
            [0, 3, 4, 11, 12],
            [1, 2, 5, 15,19],
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
                    data: [440, 505, 414, 671, 227, 413, 201, 352, 752, 320, 257, 160],
                }, {
                    name: 'المشتركين الجدد',
                    type: 'line',
                    data: [23, 25, 20, 37, 18, 22, 17, 31, 52, 22, 17, 16]
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
                    text: 'الزوار والمشتركين'
                },
                colors: ['#43399f', '#92a3fe'],
                dataLabels: {
                    enabled: true,
                    enabledOnSeries: [1]
                },
                labels: ['01 يناير ', '02 فبراير ', '03 مارس ', '04 أبريل ', '05 مايو ',
                    '06 يونيو ',
                    '07 يوليو ', '08 أغسطس ', '09 سبتمبر ', '10 أكتوبر ', '11 نوفمبر ',
                    '12 ديسمبر '
                ],
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
