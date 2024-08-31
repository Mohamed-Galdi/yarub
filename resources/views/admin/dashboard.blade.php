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
                            <p class="text-gray-50 text-3xl">{{ $allTimeVisitors }}</p>
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
                            <p class="text-white text-3xl align-middle">{{ number_format($total_sales, 2) }} <span
                                    class="text-sm">(ريال سعودي)</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-full flex justify-end">
            <a href="{{route('admin.data-export-page')}}" class="w-44 p-2 flex justify-center items-center gap-2 bg-green-500 hover:bg-green-700 border-white border-2 transition-all duration-200 ease-in-out text-white rounded-lg">
                <x-icons.file-download class="w-6 h-6" />
                <p>صفحة تصدير البيانات  </p>
            </a>
        </div>

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
        // ///////////////// Users and Students /////////////////
        var visitorsAndRegistrationsChartData = @json($visitorsAndRegistrationsChartData);

        function createDetailUsersChart() {
            const options = {
                series: [{
                    name: 'زوار المنصة',
                    type: 'column',
                    data: visitorsAndRegistrationsChartData.visitors,
                }, {
                    name: 'المشتركين الجدد',
                    type: 'line',
                    data: visitorsAndRegistrationsChartData.registrations,
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
                    text: 'الزوار و المشتركين  '
                },
                colors: ['#43399f', '#92a3fe'],
                dataLabels: {
                    enabled: true,
                    enabledOnSeries: [1]
                },
                // arabic weekdays days
                labels: visitorsAndRegistrationsChartData.months,
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
        var courseAndLessonChartData = @json($courseAndLessonChartData);

        function createDetailCoursesChart() {
            var options = {
                series: [{
                        name: "الدورات",
                        data: courseAndLessonChartData.courses,
                    },
                    {
                        name: "الشروحات",
                        data: courseAndLessonChartData.lessons,
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
                    categories: courseAndLessonChartData.months,
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
            var coursesWithSubsCount = @json($coursesWithSubsCount);
            var options = {
                series: coursesWithSubsCount.subs_count,
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
                labels: coursesWithSubsCount.course_title,
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
            var lessonsWithSubsCount = @json($lessonsWithSubsCount);
            var options = {
                series: lessonsWithSubsCount.subs_count,
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
                labels: lessonsWithSubsCount.lesson_title,
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
@push('styles')
    .apexcharts-menu-item.exportSVG { display: none; }
    .apexcharts-menu-item.exportCSV { display: none; }
@endpush
