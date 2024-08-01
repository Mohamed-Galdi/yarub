@extends('layouts.admin')
@section('content')
    <div>
        <div class="flex justify-between px-8">
            <h1 class="text-4xl text-indigo-700 mb-4">إنشاء إختبار جديد</h1>
            {{-- // back button --}}
            <x-btn.back route="admin.tests" />
        </div>
        {{-- <x-form.errors :errors="$errors" /> --}}

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form action="{{ route('admin.tests.store') }}" method="POST">
                            @csrf
                            {{-- //////////////// Course or Lesson //////////////// --}}
                            <div class="mb-4 flex lg:flex-row flex-col items-start justify-start gap-4">
                                <label for="is_for_course" class="flex flex-col cursor-pointer w-1/4 items-center">
                                    <input name="is_for_course" id="is_for_course" type="checkbox" class="peer sr-only"
                                        role="switch" checked onchange="toggleSelect()" />
                                    <span
                                        class="trancking-wide text-lg font-hacen  peer-checked:text-black peer-disabled:cursor-not-allowed peer-disabled:opacity-70 ">لدورة
                                        / لشرح</span>
                                    <div class="relative h-6 w-11 after:h-5 after:w-5 peer-checked:after:translate-x-5 rounded-full border border-slate-300 bg-green-500 after:absolute after:bottom-0 after:left-[0.0625rem] after:top-0 after:my-auto after:rounded-full after:bg-white after:transition-all after:content-[''] peer-checked:bg-indigo-600 peer-checked:after:bg-white peer-focus:outline peer-focus:outline-2 peer-focus:outline-offset-2 peer-focus:outline-green-800 peer-focus:peer-checked:outline-indigo-600 peer-active:outline-offset-0 peer-disabled:cursor-not-allowed peer-disabled:opacity-70 "
                                        aria-hidden="true"></div>
                                </label>
                                <div class="mb-4 lg:w-3/4 w-full">
                                    <label for="related_id" class="text-gray-800 font-judur ms-3 mb-1 font-semibold"> إختر
                                        <span id="course_or_lesson">الدورة</span></label>
                                    <select name="related_id" id="related_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        required>
                                        <!-- Options will be populated dynamically -->
                                    </select>
                                </div>
                            </div>

                            {{-- //////////////// Title and Type //////////////// --}}
                            <div class="mb-4 flex lg:flex-row flex-col items-start justify-start gap-4 ">
                                <div class="mb-4 lg:w-1/4 w-full">
                                    <label for="type" class="text-gray-800 font-judur ms-3 mb-1 font-semibold">نوع
                                        الإختبار </label>
                                    <select name="type" id="type"
                                        class=" block w-full h-[3.1rem] rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        required>
                                        <option value="قبلي">قبلي</option>
                                        <option value="بعدي">بعدي</option>
                                        <option value="عادي">عادي</option>
                                    </select>
                                </div>

                                <x-form.input-light class="mb-4 lg:w-3/4 w-full" type="text" name="title" id="title"
                                    label="عنوان الإختبار" placeholder="" required />

                            </div>

                            {{-- //////////////// Description //////////////// --}}
                            <div class="mb-4">
                                <x-form.textarea-light name="description" id="description" label="وصف الإختبار"
                                    placeholder="" required />
                            </div>


                            <div id="questions-container">
                                <h2 class="text-2xl mb-4 w-full text-center space-y-12">أسئلة الإختبار</h2>
                                <!-- Questions will be added here -->
                            </div>

                            <button type="button" onclick="addQuestion()"
                                class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                أضف سؤالا اخر
                            </button>

                            <div class="mt-6 w-full">
                                <button type="submit"
                                    class="w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    انشئ الإختبار
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script>
            let questionCount = 0;

            function toggleSelect() {
                const isForCourse = document.getElementById('is_for_course').checked;
                const selectElement = document.getElementById('related_id');
                selectElement.innerHTML = '';

                if (isForCourse) {
                    // Populate with courses
                    @foreach ($courses as $course)
                        selectElement.innerHTML += `<option value="{{ $course->id }}">{{ $course->title }}</option>`;
                    @endforeach
                } else {
                    // Populate with lessons
                    @foreach ($lessons as $lesson)
                        selectElement.innerHTML += `<option value="{{ $lesson->id }}">{{ $lesson->title }}</option>`;
                    @endforeach
                };
                const courseOrLesson = document.getElementById('course_or_lesson');
                courseOrLesson.innerHTML = isForCourse ? 'الدورة' : 'الشرح';
            }

            function addQuestion() {
                questionCount++;
                const container = document.getElementById('questions-container');
                const questionDiv = document.createElement('div');
                questionDiv.className = 'mb-6  border border-gray-300 rounded-md overflow-hidden bg-gray-300';
                questionDiv.innerHTML = `
                <h3 class="text-lg font-medium mb-2 bg-gray-800 p-1 text-center text-gray-100">السؤال ${questionCount}</h3>
                <div class="p-4 space-y-4">
                <div class="p-4">
                    <div class="mb-4">
                        <label for="question_${questionCount}" class="block text-sm font-medium text-gray-700">نص السؤال (إختياري) </label>
                        <textarea  name="questions[${questionCount}][question_text]" id="question_text_${questionCount}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" ></textarea>
                    </div>
                    <div class="mb-4">
                        <label for="question_${questionCount}" class="block text-sm font-medium text-gray-700">السؤال</label>
                        <input type="text" name="questions[${questionCount}][question]" id="question_${questionCount}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                    </div>
                    
                    <div class="grid lg:grid-cols-2 grid-cols-1 gap-4">
                        <div class="mb-4">
                        <label for="option_1_${questionCount}" class="block text-sm font-medium text-gray-700">الخيار الأول</label>
                        <input type="text" name="questions[${questionCount}][option_1]" id="option_1_${questionCount}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required onchange="updateCorrectAnswerOptions(${questionCount})">
                    </div>
                    <div class="mb-4">
                        <label for="option_2_${questionCount}" class="block text-sm font-medium text-gray-700">الخيار الثاني</label>
                        <input type="text" name="questions[${questionCount}][option_2]" id="option_2_${questionCount}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required onchange="updateCorrectAnswerOptions(${questionCount})">
                    </div>
                    <div class="mb-4">
                        <label for="option_3_${questionCount}" class="block text-sm font-medium text-gray-700">الخيار الثالث</label>
                        <input type="text" name="questions[${questionCount}][option_3]" id="option_3_${questionCount}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required onchange="updateCorrectAnswerOptions(${questionCount})">
                    </div>
                    <div class="mb-4">
                        <label for="option_4_${questionCount}" class="block text-sm font-medium text-gray-700">الخيار الرابع</label>
                        <input type="text" name="questions[${questionCount}][option_4]" id="option_4_${questionCount}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required onchange="updateCorrectAnswerOptions(${questionCount})">
                    </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="correct_answer_${questionCount}" class="block text-sm font-medium text-gray-700">الجواب الصحيح</label>
                        <select name="questions[${questionCount}][correct_answer]" id="correct_answer_${questionCount}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            <option value="">اختر الجواب الصحيح</option>
                            <option value="1">الخيار الأول</option>
                            <option value="2">الخيار الثاني</option>
                            <option value="3">الخيار الثالث</option>
                            <option value="4">الخيار الرابع</option>
                        </select>
                    </div>
                </div>
            `;
                container.appendChild(questionDiv);
            }

            function updateCorrectAnswerOptions(questionNumber) {
                const correctAnswerSelect = document.getElementById(`correct_answer_${questionNumber}`);
                for (let i = 1; i <= 4; i++) {
                    const optionText = document.getElementById(`option_${i}_${questionNumber}`).value;
                    correctAnswerSelect.options[i].text = optionText || `Option ${i}`;
                }
            }

            // Add initial 3 questions
            for (let i = 0; i < 3; i++) {
                addQuestion();
            }

            // Initialize the select options
            toggleSelect();
        </script>
    </div>
@endsection
