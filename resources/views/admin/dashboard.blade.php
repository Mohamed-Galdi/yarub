@extends('layouts.admin')
@section('panel-heading', true)

@section('content')
    <x-panel.heading title="لوحة التحكم" description="نظرة عامة على نشاط منصة يعرب والمبيعات.">
        <x-slot:actions>
            <x-panel.button :href="route('admin.data-export-page')">
                <x-icons.file-download aria-hidden="true" />
                تصدير البيانات
            </x-panel.button>
        </x-slot:actions>
    </x-panel.heading>

    <section class="panel-section" aria-labelledby="overview-heading">
        <div class="panel-section-heading"><h2 id="overview-heading">مؤشرات المنصة</h2></div>
        <dl class="panel-grid panel-grid--metrics">
            <x-panel.metric label="المشتركون" :value="$students_count" />
            <x-panel.metric label="الدورات والشروحات" :value="$courses_and_lessons_count" />
            <x-panel.metric label="زوار المنصة" :value="$allTimeVisitors" />
            <x-panel.metric label="المداخيل" :value="number_format($total_sales, 2)" unit="ر.س" />
        </dl>
    </section>

    <section class="panel-section" aria-labelledby="activity-heading">
        <div class="panel-section-heading"><h2 id="activity-heading">النشاط بمرور الوقت</h2></div>
        <div class="panel-grid panel-grid--charts">
            <x-panel.card title="الزوار والمشتركون" class="panel-chart">
                <div id="usersDetail" dir="ltr"></div>
            </x-panel.card>
            <x-panel.card title="مبيعات الدورات واشتراكات الشروحات" class="panel-chart">
                <div id="coursesDetail" dir="ltr"></div>
            </x-panel.card>
        </div>
    </section>

    <section class="panel-section" aria-labelledby="sales-heading">
        <div class="panel-section-heading"><h2 id="sales-heading">توزيع المبيعات</h2></div>
        <div class="panel-grid panel-grid--charts">
            <x-panel.card title="الدورات الأكثر مبيعًا" class="panel-chart">
                @if(empty($coursesWithSubsCount['subs_count']))
                    <x-panel.empty title="لا توجد بيانات مبيعات للدورات" message="سيظهر توزيع المبيعات عند تسجيل اشتراكات." />
                @else
                    <div id="CoursesalesDetail" dir="ltr"></div>
                @endif
            </x-panel.card>
            <x-panel.card title="الشروحات الأكثر مبيعًا" class="panel-chart">
                @if(empty($lessonsWithSubsCount['subs_count']))
                    <x-panel.empty title="لا توجد بيانات مبيعات للشروحات" message="سيظهر توزيع المبيعات عند تسجيل اشتراكات." />
                @else
                    <div id="LessonsalesDetail" dir="ltr"></div>
                @endif
            </x-panel.card>
        </div>
    </section>

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
                    height: 310,
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
                colors: ['#43399f', '#616cba'],
                dataLabels: {
                    enabled: false
                },
                labels: visitorsAndRegistrationsChartData.months,
                responsive: [{
                    breakpoint: 520,
                    options: {
                        xaxis: { labels: { rotate: -45, rotateAlways: true } }
                    }
                }],
                grid: {
                    padding: { top: 12, right: 14 },
                },
                yaxis: [{
                    show: false,

                }, {
                    opposite: true,
                    min: 0,
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
                    height: 310,
                    type: 'line',
                    background: '#fff',
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

                colors: ['#43399f', '#616cba'],
                dataLabels: {
                    enabled: false,
                },
                stroke: {
                    curve: 'smooth'
                },
                grid: {
                    padding: { top: 12, right: 20 },
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
                    min: 0,
                },
                legend: {
                    position: 'bottom'
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
                    height: 310,
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
                        shadeIntensity: 0.22,
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
                    formatter: value => value.toFixed(1) + '%',
                },
                legend: {
                    position: 'bottom',
                    height: 48,
                },
            };

            var chart = new ApexCharts(document.querySelector("#CoursesalesDetail"), options);
            chart.render();
        }
        if (document.getElementById('CoursesalesDetail')) {
            createDetailCoursesSalesChart();
        }

        function createDetailLessonsSalesChart() {
            var lessonsWithSubsCount = @json($lessonsWithSubsCount);
            var options = {
                series: lessonsWithSubsCount.subs_count,
                chart: {
                    width: '100%',
                    height: 310,
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
                        shadeIntensity: 0.22,
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
                    formatter: value => value.toFixed(1) + '%',
                },
                legend: {
                    position: 'bottom',
                    height: 48,
                },
            };

            var chart = new ApexCharts(document.querySelector("#LessonsalesDetail"), options);
            chart.render();
        }
        if (document.getElementById('LessonsalesDetail')) {
            createDetailLessonsSalesChart();
        }
    </script>
@endsection()
@push('styles')
    .yarub-panel .apexcharts-menu-item.exportSVG { display: none; }
    .yarub-panel .apexcharts-menu-item.exportCSV { display: none; }
@endpush
