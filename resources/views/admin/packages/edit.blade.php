@extends('layouts.admin')
@section('content')
    <div class="">
        <x-errors :errors="$errors" />
        <div class="flex justify-between">
            <h1 class="lg:text-4xl text-2xl text-nowrap truncate text-indigo-700 mb-4"><span class="text-gray-500">تعديل
                    حقيبة</span> : {{ Str::words($package->title, 8) }} </h1>
            </h1>
            {{-- // back button --}}
            <x-btn.back route="admin.packages" />
        </div>
        <form id="courseForm" action="{{ route('admin.packages.update', $package->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
              <x-form.toogle label="حالة النشر" name="is_active" value="{{ $package->is_active }}"
                    class="lg:w-[10%] w-full lg:items-center items-start justify-start lg:order-3 order-1" />
            <ul class=" grid lg:grid-cols-3 grid-cols-1 gap-2">
                <label for="type" class="text-gray-800 font-judur ms-3 font-semibold lg:col-span-3">نوع الحقيبة</label>

                <li>
                    <input type="radio" id="courses" name="type" value="courses" class="hidden peer"
                        {{ $package->type === 'courses' ? 'checked' : '' }} required />
                    <label for="courses"
                        class="inline-flex items-center  justify-between w-full p-3 text-gray-700 bg-gray-300 border-2 border-gray-700 rounded-lg cursor-pointer peer-checked:border-blue-600 peer-checked:text-slate-50 peer-checked:bg-indigo-500 hover:text-gray-600 hover:bg-gray-400 transition-all duration-300 ease-in-out">
                        <div class="block w-full text-center">
                            <div class="w-full text-2xl">الدورات</div>
                        </div>
                    </label>
                </li>
                <li>
                    <input type="radio" id="lessons" name="type" value="lessons" class="hidden peer"
                        {{ $package->type === 'lessons' ? 'checked' : '' }} required />
                    <label for="lessons"
                        class="inline-flex items-center  justify-between w-full p-3 text-gray-700 bg-gray-300 border-2 border-gray-700 rounded-lg cursor-pointer peer-checked:border-blue-600 peer-checked:text-slate-50 peer-checked:bg-indigo-500 hover:text-gray-600 hover:bg-gray-400 transition-all duration-300 ease-in-out">
                        <div class="block w-full text-center ">
                            <div class="block w-full text-center">
                                <div class="w-full text-2xl">الشروحات</div>

                            </div>
                    </label>
                </li>
                <li>
                    <input type="radio" id="mixed" name="type" value="mixed" class="hidden peer"
                        {{ $package->type === 'mixed' ? 'checked' : '' }} required />
                    <label for="mixed"
                        class="inline-flex items-center  justify-between w-full p-3 text-gray-700 bg-gray-300 border-2 border-gray-700 rounded-lg cursor-pointer peer-checked:border-blue-600 peer-checked:text-slate-50 peer-checked:bg-indigo-500 hover:text-gray-600 hover:bg-gray-400 transition-all duration-300 ease-in-out">
                        <div class="block w-full text-center ">
                            <div class="block w-full text-center">
                                <div class="w-full text-2xl">مختلط</div>

                            </div>
                    </label>
                </li>

            </ul>
            <div class="grid lg:grid-cols-2 grid-cols-1 gap-4">
                <div class=" w-full">
                    <label for="courses" class="text-gray-800 font-judur ms-3 mb-1 font-semibold"> تحديد الدورات</label>
                    <button id="dropdownSearchButtonCourses" data-dropdown-toggle="dropdownSearchCourses"
                        class="block w-full h-[3.1rem] rounded-md bg-indigo-500 text-white disabled:text-black disabled:bg-slate-300 shadow-sm disabled:cursor-not-allowed "
                        type="button">تحديد الدورات </button>
                    <!-- Dropdown menu -->
                    <div id="dropdownSearchCourses" class="z-10 hidden bg-white rounded-lg shadow w-fit">
                        <ul class="h-48 px-3 pb-3 overflow-y-auto text-lg text-gray-700 "
                            aria-labelledby="dropdownSearchButtonCourses">
                            @foreach ($courses as $course)
                                <li>
                                    <div class="flex items-center p-2 rounded hover:bg-gray-100 ">
                                        <input id="course-checkbox-{{ $course->id }}" type="checkbox" name="courses[]"
                                            value="{{ $course->id }}" data-price="{{ $course->price }}"
                                            {{ $package->courses->contains($course->id) ? 'checked' : '' }}
                                            class="w-4 h-4 text-slate-600 bg-gray-100 focus:ring-0 focus:ring-transparent ">
                                        <label for="course-checkbox-{{ $course->id }}"
                                            class="w-full text-slate-500 ms-4">{{ Str::words($course->title, 10) }}</label>
                                    </div>
                                </li>
                            @endforeach
                        </ul>

                    </div>
                </div>
                <div class=" w-full">
                    <label for="applicable_to" class="text-gray-800 font-judur ms-3 mb-1 font-semibold"> تحديد
                        الشروحات
                    </label>
                    <button id="dropdownSearchButtonLessons" data-dropdown-toggle="dropdownSearchLessons"
                        class="block w-full h-[3.1rem] rounded-md bg-indigo-500 text-white disabled:text-black disabled:bg-slate-300 shadow-sm disabled:cursor-not-allowed "
                        type="button">تحديد الشروحات</button>
                    <!-- Dropdown menu -->
                    <div id="dropdownSearchLessons" class="z-10 hidden bg-white rounded-lg shadow w-fit">
                        <ul class="h-48 px-3 pb-3 overflow-y-auto text-lg text-gray-700 "
                            aria-labelledby="dropdownSearchButtonLessons">
                            @foreach ($lessons as $lesson)
                                <li>
                                    <div class="flex items-center p-2 rounded hover:bg-gray-100 ">
                                        <input id="lesson-checkbox-{{ $lesson->id }}" type="checkbox" name="lessons[]"
                                            value="{{ $lesson->id }}" data-monthly-price="{{ $lesson->monthly_price }}"
                                            data-annual-price="{{ $lesson->annual_price }}"
                                            {{ $package->lessons->contains($lesson->id) ? 'checked' : '' }}
                                            class="w-4 h-4 text-slate-600 bg-gray-100 focus:ring-0 focus:ring-transparent">
                                        <label for="lesson-checkbox-{{ $lesson->id }}"
                                            class="w-full text-slate-500 ms-4">{{ Str::words($lesson->title, 10) }}</label>
                                    </div>
                                </li>
                            @endforeach
                        </ul>

                    </div>
                </div>
            </div>
            {{-- ////////////////////////////////////////////////////////////////////////////////////////////////////////////// --}}
            <div class="form-group w-full flex lg:flex-row flex-col gap-4 items-start justify-start">
                <x-form.input-light name="title" label="عنوان الحقيبة" placeholder="عنوان مناسب للحقيبة" type="text"
                    required class=" w-full" id="title" value="{{ $package->title }}" required />
            </div>
            <div class="form-group">
                <x-form.textarea-light name="description" label="وصف للحقيبة" placeholder=" {{ $package->description }}"
                    type="text" required class="w-full " />
            </div>
            <!-- New Price Section -->
            <div class="mt-4">
                <!-- Single price input for courses -->
                <div id="coursePriceSection" class="hidden">
                    <div class="w-full flex gap-4 items-end justify-start">
                        <x-form.input-light name="price" id="coursePrice" label="سعر الحقيبة" placeholder=""
                            type="number" value="{{ $package->price }}" required class=" w-1/4" required />
                        <div>
                            <p>قيمة الحقيبة الفعلية: </p>
                            <p id="totalValue" class="text-lg font-bold ">0 ريال</p>
                        </div>
                    </div>
                </div>

                <!-- Monthly and Annual price inputs for lessons/mixed -->
                <div id="lessonPriceSection" class="hidden">
                    <div class="w-full grid gap-4 grid-cols-2 lg:grid-cols-4 items-end justify-start">
                        <x-form.input-light name="monthly_price" id="monthlyPrice" label=" سعر الإشتراك الشهري    "
                            placeholder="" type="number" required class=" w-full" value="{{ $package->monthly_price }}"
                            required />
                        <div class="w-full">
                            <p>قيمة الحقيبة الفعلية: </p>
                            <p id="totalMonthlyValue" class="text-lg font-bold ">0 ريال</p>
                        </div>
                        <x-form.input-light name="annual_price" id="annualPrice" label=" سعر الإشتراك السنوي    "
                            placeholder="" type="number" required class=" w-full" value="{{ $package->annual_price }}"
                            required />
                        <div class="w-full">
                            <p>قيمة الحقيبة الفعلية: </p>
                            <p id="totalAnnualValue" class="text-lg font-bold ">0 ريال</p>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full bg-green-500 my-4 p-3 rounded-lg text-white  hover:bg-green-700"> تحديث
                الحقيبة</button>
        </form>

    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const radioButtons = document.querySelectorAll('input[name="type"]');
            const coursesButton = document.getElementById('dropdownSearchButtonCourses');
            const lessonsButton = document.getElementById('dropdownSearchButtonLessons');
            const coursePriceSection = document.getElementById('coursePriceSection');
            const lessonPriceSection = document.getElementById('lessonPriceSection');
            const totalValueDisplay = document.getElementById('totalValue');
            const totalMonthlyValueDisplay = document.getElementById('totalMonthlyValue');
            const totalAnnualValueDisplay = document.getElementById('totalAnnualValue');

            function updateDropdowns() {
                const selectedValue = document.querySelector('input[name="type"]:checked')?.value;

                switch (selectedValue) {
                    case 'courses':
                        coursesButton.disabled = false;
                        lessonsButton.disabled = true;
                        coursePriceSection.classList.remove('hidden');
                        lessonPriceSection.classList.add('hidden');
                        break;
                    case 'lessons':
                        coursesButton.disabled = true;
                        lessonsButton.disabled = false;
                        coursePriceSection.classList.add('hidden');
                        lessonPriceSection.classList.remove('hidden');
                        break;
                    case 'mixed':
                        coursesButton.disabled = false;
                        lessonsButton.disabled = false;
                        coursePriceSection.classList.add('hidden');
                        lessonPriceSection.classList.remove('hidden');
                        break;
                    default:
                        coursesButton.disabled = true;
                        lessonsButton.disabled = true;
                        coursePriceSection.classList.add('hidden');
                        lessonPriceSection.classList.add('hidden');
                }
                calculateTotalValue();
            }

            function calculateTotalValue() {
                let total = 0;
                let monthlyTotal = 0;
                let annualTotal = 0;
                const selectedValue = document.querySelector('input[name="type"]:checked')?.value;

                if (selectedValue === 'courses') {
                    const selectedCourses = document.querySelectorAll(
                        '#dropdownSearchCourses input[type="checkbox"]:checked');
                    selectedCourses.forEach(course => {
                        total += parseFloat(course.dataset.price || 0);
                    });
                } else if (selectedValue === 'lessons') {
                    const selectedLessons = document.querySelectorAll(
                        '#dropdownSearchLessons input[type="checkbox"]:checked');
                    selectedLessons.forEach(lesson => {
                        monthlyTotal += parseFloat(lesson.dataset.monthlyPrice || 0);
                        annualTotal += parseFloat(lesson.dataset.annualPrice || 0);
                    });
                } else {
                    const selectedCourses = document.querySelectorAll(
                        '#dropdownSearchCourses input[type="checkbox"]:checked');
                    const selectedLessons = document.querySelectorAll(
                        '#dropdownSearchLessons input[type="checkbox"]:checked');

                    selectedCourses.forEach(course => {
                        total += parseFloat(course.dataset.price || 0);
                    });
                    selectedLessons.forEach(lesson => {
                        monthlyTotal += parseFloat(lesson.dataset.monthlyPrice || 0);
                        annualTotal += parseFloat(lesson.dataset.annualPrice || 0);
                        monthlyTotal += total;
                        annualTotal += total;
                    });
                }
                console.log(monthlyTotal, annualTotal, total);

                totalValueDisplay.textContent = total.toFixed(2) + ' ريال';
                totalMonthlyValueDisplay.textContent = monthlyTotal.toFixed(2) + ' ريال';
                totalAnnualValueDisplay.textContent = annualTotal.toFixed(2) + ' ريال';
            }

            radioButtons.forEach(button => {
                button.addEventListener('change', updateDropdowns);
            });

            // Add event listeners to checkboxes
            document.querySelectorAll(
                    '#dropdownSearchCourses input[type="checkbox"], #dropdownSearchLessons input[type="checkbox"]')
                .forEach(checkbox => {
                    checkbox.addEventListener('change', calculateTotalValue);
                });

            // Initial update
            updateDropdowns();
            calculateTotalValue();
        });
    </script>
@endsection()
