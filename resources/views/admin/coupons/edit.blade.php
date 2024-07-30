@extends('layouts.admin')

@section('content')
        <h1 class="text-4xl text-indigo-700 mb-4">تعديل   القسيمة: <span class="text-gray-500">{{$coupon->code }}</span></h1>
    <form action="{{ route('admin.coupons.update', $coupon->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="flex flex-col lg:flex-row gap-4 mb-6">
            <x-form.toogle label="تفعيل القسيمة " name="is_active" value="{{ $coupon->is_active }}"
                class="lg:w-1/4 w-full lg:items-center items-start justify-start  order-1 lg:order-4" />
            <div class="lg:w-1/4 w-full order-2 lg:order-1">
                <x-form.input-light type="text" id="code" name="code" label="كود الخصم" :value="$coupon->code"
                    placeholder="مثال: YARUB-50" class="w-full" required />
            </div>
            <div class="lg:w-1/4 w-full order-3 lg:order-2">
                <label for="type" class="text-gray-800 font-judur ms-3 mb-1 font-semibold">نوع الخصم</label>
                <select name="type" id="type"
                    class="block w-full h-[3.1rem] rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    required>
                    <option value="percentage" {{ $coupon->type == 'percentage' ? 'selected' : '' }}>نسبة مئوية (%)</option>
                    <option value="fixed" {{ $coupon->type == 'fixed' ? 'selected' : '' }}> مبلغ ثابت (ريال سعودي)</option>
                </select>
            </div>
            <div class="lg:w-1/4 w-full order-4 lg:order-3">
                <x-form.input-light type="number" id="value" name="value" label="القيمة المئوية للخصم"
                    :value="(int) $coupon->value" placeholder="مثال: 50" class="w-full" required />
            </div>
        </div>
        <div class="flex flex-col lg:flex-row gap-4 mb-6">
            <div class="lg:w-1/3 w-full">
                <label for="applicable_to" class="text-gray-800 font-judur ms-3 mb-1 font-semibold">ينطبق على</label>
                <select name="applicable_to" id="applicable_to"
                    class="block w-full h-[3.1rem] rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    required>
                    <option value="all" {{ $coupon->applicable_to == 'all' ? 'selected' : '' }}>كل المواد التعليمية
                    </option>
                    <option value="courses" {{ $coupon->applicable_to == 'courses' ? 'selected' : '' }}> كل الدروس</option>
                    <option value="lessons" {{ $coupon->applicable_to == 'lessons' ? 'selected' : '' }}> كل الشروحات
                    </option>
                    <option value="specific" {{ $coupon->applicable_to == 'specific' ? 'selected' : '' }}> دروس و شروحات
                        محددة </option>
                </select>
            </div>
            <div class="lg:w-2/3 w-full">
                <label for="applicable_to" class="text-gray-800 font-judur ms-3 mb-1 font-semibold">تحديد الدروس و
                    الشروحات</label>
                <button id="dropdownSearchButton" data-dropdown-toggle="dropdownSearch"
                    class="block w-full h-[3.1rem] rounded-md bg-indigo-500 text-white disabled:text-black disabled:bg-slate-300 shadow-sm disabled:cursor-not-allowed"
                    type="button" disabled>تحديد الدروس و الشروحات</button>
                <!-- Dropdown menu -->
                <div id="dropdownSearch" class="z-10 hidden bg-white rounded-lg shadow w-fit">
                    <ul class="h-48 px-3 pb-3 overflow-y-auto text-lg text-gray-700 dark:text-gray-200"
                        aria-labelledby="dropdownSearchButton">
                        <p>تحديد الدروس:</p>
                        @foreach ($courses as $course)
                            <li>
                                <div class="flex items-center p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600">
                                    <input id="course-checkbox-{{ $course->id }}" type="checkbox" name="courses[]"
                                        value="{{ $course->id }}"
                                        {{ $coupon->courses->contains($course->id) ? 'checked' : '' }}
                                        class="w-4 h-4 text-slate-600 bg-gray-100 focus:ring-0 focus:ring-transparent">
                                    <label for="course-checkbox-{{ $course->id }}"
                                        class="w-full text-slate-500 ms-4">{{ $course->title }}</label>
                                </div>
                            </li>
                        @endforeach
                        <p>تحديد الشروحات:</p>
                        @foreach ($lessons as $lesson)
                            <li>
                                <div class="flex items-center p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600">
                                    <input id="lesson-checkbox-{{ $lesson->id }}" type="checkbox" name="lessons[]"
                                        value="{{ $lesson->id }}"
                                        {{ $coupon->lessons->contains($lesson->id) ? 'checked' : '' }}
                                        class="w-4 h-4 text-slate-600 bg-gray-100 focus:ring-0 focus:ring-transparent">
                                    <label for="lesson-checkbox-{{ $lesson->id }}"
                                        class="w-full text-slate-500 ms-4">{{ $lesson->title }}</label>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        <div class="flex flex-col lg:flex-row gap-8 my-6">
            <div class="lg:w-1/2 w-full">
                <label for="start_date" class="text-gray-800 font-judur ms-3 mb-1 font-semibold">تاريخ البدء</label>
                <x-flatpickr placeholder="إختر تاريخ" name="start_date" :value="$coupon->start_date" class="" required />
                @error('start_date')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>
            <div class="lg:w-1/2 w-full">
                <label for="end_date" class="text-gray-800 font-judur ms-3 mb-1 font-semibold">تاريخ الانتهاء</label>
                <x-flatpickr placeholder="إختر تاريخ" name="end_date" :value="$coupon->end_date" class="" required />
                @error('end_date')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <button type="submit" class="w-full bg-green-500 my-4 p-3 rounded-lg text-white hover:bg-green-700">تحديث
            القسيمة</button>
    </form>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const applicableToSelect = document.getElementById('applicable_to');
            const dropdownSearchButton = document.getElementById('dropdownSearchButton');

            function toggleDropdownButton() {
                dropdownSearchButton.disabled = applicableToSelect.value !== 'specific';
            }

            applicableToSelect.addEventListener('change', toggleDropdownButton);
            toggleDropdownButton(); // Initial state
        });
        document.addEventListener('DOMContentLoaded', function() {
            const typeSelect = document.getElementById('type');
            const valueInput = document.getElementById('value');
            const valueLabel = valueInput.parentElement.querySelector('label');

            function updateValueField() {
                if (typeSelect.value === 'percentage') {
                    valueLabel.textContent = ' النسبة المئوية للخصم (%)';
                } else {
                    valueLabel.textContent = '  مبلغ  الخصم ( ريال سعودي)';
                }
            }

            // Initial update
            updateValueField();

            // Listen for changes on the select element
            typeSelect.addEventListener('change', updateValueField);
        });
    </script>
@endpush
