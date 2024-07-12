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
        <div dir="rtl" class="w-full flex md:flex-row flex-col justify-start items-start md:gap-3 gap-8 mb-12  pb-12">
            {{-- //////////////////// Users //////////////////// --}}
            <x-charts.overview :id="'users'" :title="'إجمالي الزوار'" :value="5720" :trend="'+ 63,6 %'" :color="'text-blue-500'"
                icon="icons.eye" />
            {{-- //////////////////// Students //////////////////// --}}
            <x-charts.overview :id="'students'" :title="'الطلاب المسجلين'" :value="120" :trend="'+ 13,6 %'" :color="'text-red-500'"
                icon="icons.students" />

            {{-- //////////////////// Courses //////////////////// --}}
            <x-charts.overview :id="'courses'" :title="'الدورات و الشروحات'" :value="12" :trend="'+ 20 %'"
                :color="'text-yellow-500'" icon="icons.lesson" />
            {{-- //////////////////// Sales //////////////////// --}}
            <x-charts.overview :id="'sales'" :title="'المبيعات'" :value="3000" :trend="'+ 3,6 %'"
                :color="'text-green-500'" icon="icons.money-bag" />
        </div>

        {{-- //////////////////// Detail Charts //////////////////// --}}
        <div class="w-full bg-gray-200 rounded-lg h-screen shadow-md  border border-white px-12 py-6 ">
            <p class="text-3xl text-gray-700 text-center  ">الإحصائيات المفصلة</p>
            <div class="">
                <p class="text-xl text-indigo-500 ">الزوار </p>
                <div class="w-full h-1 rounded-lg bg-gradient-to-r from-pr-500 to-indigo-500"></div>
                <div class="mt-12">
                    <div id="usersDetail"></div>
                </div>
            </div>

        </div>
    </div>

    <script>
        // Overview Charts
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
                    }
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

        function createDetailAreaChart(chartId, seriesName, seriesData, categories, color) {
            const options = {
                series: [{
                    name: seriesName,
                    data: seriesData
                }],
                chart: {
                    height: 350,
                    type: 'area',
                    toolbar: {
                        show: true
                    }
                },
                colors: [color],
                dataLabels: {
                    enabled: true
                },
                stroke: {
                    curve: 'smooth'
                },
                xaxis: {
                    categories: categories
                },
                yaxis: {
                    show: true
                },
                grid: {
                    show: true
                },
            };

            if (document.getElementById(chartId) && typeof ApexCharts !== 'undefined') {
                const chart = new ApexCharts(document.getElementById(chartId), options);
                chart.render();
            }
        }   

        const seriesData = [
            [31, 40, 28, 51, 42, 109, 120],
            [0, 3, 4, 11, 12, 13, 24],
            [1, 2, 5, 15],
            [10, 20, 15, 30, 25, 70, 80]
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

        // Detail Charts
        const seriesData2 = [
            [31, 40, 28, 51, 42, 109, 120],
            [0, 3, 4, 11, 12, 13, 24],
            [1, 2, 5, 15],
            [10, 20, 15, 30, 25, 70, 80]
        ];

        const categories2 = ["يناير", "فبراير", "مارس", "أبريل", "مايو", "يونيو", "يوليو", "أغسطس", "سبتمبر", "أكتوبر",
            "نوفمبر", "ديسمبر"
        ];

        const chartConfigs2 = [{
                id: 'usersDetail',
                name: 'زائر',
                color: '#008FFB' // blue

            },
            {
                id: 'studentsDetail',
                name: 'طلاب',
                color: '#FF4560' // red

            },
            {
                id: 'coursesDetail',
                name: 'دورات',
                color: '#FEB019' // yellow

            },
            {
                id: 'salesDetail',
                name: 'مبيعات',
                color: '#00E396' // green

            }
        ];

        chartConfigs2.forEach((config, index) => {
            createDetailAreaChart(config.id, config.name, seriesData2[index], categories2, config.color);
        });
    </script>

@endsection()
