@extends('layouts.admin')
@section('content')
    <div>
        <div class="flex justify-between px-8">
            <h1 class="lg:text-4xl text-2xl text-nowrap truncate text-indigo-700 mb-4">تعديل : <span class="text-gray-800">{{ $test->title }} <span
                        class="font-nitaqat text-gray-500 text-2xl">({{ $test->course_id ? ' الدرس : ' . $test->course->title : ' الشرح : ' . $test->lesson->title }})</span></span>
            </h1>
            {{-- // back button --}}
            <x-btn.back route="admin.tests" />
        </div>

        {{-- <x-form.errors :errors="$errors" /> --}}
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form action="{{ route('admin.tests.update', $test->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="w-full mb-4 ">
                                <button type="submit"
                                    class="px-4 py-2 w-full bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    تحديث الإختبار
                                </button>
                            </div>
                            <div class=" flex items-start justify-start gap-4">
                                <x-form.input-light class="mb-4 lg:w-3/5 w-full lg:block hidden" type="text"
                                    name="title" id="title" label="عنوان الإختبار" placeholder="" required
                                    value="{{ $test->title }}" />

                                <div class="mb-4 lg:w-1/5 w-1/2">
                                    <label for="type" class="text-gray-800 font-judur ms-3 mb-1 font-semibold">نوع
                                        الإختبار </label>
                                    <select name="type" id="type"
                                        class=" block w-full h-[3.1rem] rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        required>
                                        <option {{ $test->type === 'قبلي' ? 'selected' : '' }} value="قبلي">قبلي</option>
                                        <option {{ $test->type === 'بعدي' ? 'selected' : '' }} value="بعدي">بعدي</option>
                                        <option {{ $test->type === 'عادي' ? 'selected' : '' }} value="عادي">عادي</option>
                                    </select>
                                </div>

                                <x-form.toogle label="حالة النشر" name="is_published" value="{{ $test->is_published }}"
                                    class="lg:w-1/5 w-1/2 lg:items-center items-start justify-start " />

                            </div>
                            <x-form.input-light class="mb-4 lg:w-3/5 w-full lg:hidden block" type="text" name="title"
                                id="title" label="عنوان الإختبار" placeholder="" required value="{{ $test->title }}" />
                            <div class="mb-4">
                                <x-form.textarea-light name="description" id="description" label="وصف الإختبار"
                                    placeholder="" required placeholder="{{ $test->description }}" />
                            </div>
                            <div id="questions-container" class="space-y-8">
                                @foreach ($test->questions as $index => $question)
                                    <div class="mb-6  border border-gray-300 rounded-md overflow-hidden bg-gray-300"
                                        id="question-block-{{ $index }}">
                                        <div class="flex justify-between items-center mb-2 w-full">
                                            <div
                                                class="text-lg font-medium mb-2 bg-gray-800 p-1 text-center text-gray-100 w-full flex relative justify-center items-center">
                                                <h3> السؤال {{ $index + 1 }} </h3>
                                                <button type="button" onclick="removeQuestion({{ $index }})"
                                                    class=" absolute left-0 top-0 px-2 py-1 bg-red-600 text-white w-8 h-full hover:bg-red-700 ">
                                                    <x-icons.trash class="h-full w-full" />
                                                </button>
                                            </div>


                                        </div>

                                        <div class="px-4 py-2 space-y-4">
                                            <input type="hidden" name="questions[{{ $index }}][id]"
                                                value="{{ $question->id }}">
                                            <div class="mb-4">
                                                <label for="question_text_{{ $index }}"
                                                    class="block text-sm font-medium text-gray-700">نص السؤال (إختياري)
                                                </label>
                                                <textarea name="questions[{{ $index }}][question_text]" id="question_text_{{ $index }}"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ $question->question_text }}</textarea>
                                            </div>
                                            <div class="mb-4">
                                                <label for="question_{{ $index }}"
                                                    class="block text-sm font-medium text-gray-700">السؤال</label>
                                                <input type="text" name="questions[{{ $index }}][question]"
                                                    id="question_{{ $index }}" value="{{ $question->question }}"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                    required>
                                            </div>
                                            <div class="grid lg:grid-cols-2 grid-cols-1 gap-4">
                                                @for ($i = 1; $i <= 4; $i++)
                                                    <div class="mb-4">
                                                        <label for="option_{{ $i }}_{{ $index }}"
                                                            class="block text-sm font-medium text-gray-700">الخيار
                                                            {{ $i }}</label>
                                                        <input type="text"
                                                            name="questions[{{ $index }}][option_{{ $i }}]"
                                                            id="option_{{ $i }}_{{ $index }}"
                                                            value="{{ $question->{'option_' . $i} }}"
                                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                            required
                                                            onchange="updateCorrectAnswerOptions({{ $index }})">
                                                    </div>
                                                @endfor
                                            </div>
                                            <div class="mb-4">
                                                <label for="correct_answer_{{ $index }}"
                                                    class="block text-sm font-medium text-gray-700"> الجواب الصحيح</label>
                                                <select name="questions[{{ $index }}][correct_answer]"
                                                    id="correct_answer_{{ $index }}"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                    required>
                                                    @for ($i = 1; $i <= 4; $i++)
                                                        <option value="{{ $i }}"
                                                            {{ $question->correct_answer == $i ? 'selected' : '' }}>
                                                            الخيار {{ $i }}
                                                        </option>
                                                    @endfor
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <button type="button" onclick="addQuestion()"
                                class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                أضف سؤال جديد
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // initialize the question coutner starting from number of questions
            let questionCount = {{ $test->questions->count() }};
            // let questionCount = 0;

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

                questionDiv.className = 'question-form mb-6  border border-gray-300 rounded-md overflow-hidden bg-gray-300';
                questionDiv.innerHTML = `
                <div
                    class="text-lg font-medium mb-2 bg-gray-800 p-1 text-center text-gray-100 w-full flex relative justify-center items-center">
                    <h3>  سؤال إضافي ${questionCount} </h3>
                    <button  type="button"
                        class="remove-question absolute left-0 top-0 px-2 py-1 bg-red-600 text-white w-8 h-full hover:bg-red-700 ">
                        <x-icons.trash class="h-full w-full" />
                    </button>
                </div>
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

            document.getElementById('questions-container').addEventListener('click', function(event) {
                if (event.target.closest('.remove-question')) {
                    event.target.closest('.question-form').remove();
                }
            });


            function updateCorrectAnswerOptions(questionNumber) {
                const correctAnswerSelect = document.getElementById(`correct_answer_${questionNumber}`);
                for (let i = 1; i <= 4; i++) {
                    const optionText = document.getElementById(`option_${i}_${questionNumber}`).value;
                    correctAnswerSelect.options[i].text = optionText || `Option ${i}`;
                }
            }



            function removeQuestion(index) {
                const questionBlock = document.getElementById(`question-block-${index}`);
                if (questionBlock) {
                    questionBlock.remove();
                    renumberQuestions();
                }

                // Prevent removing all questions
                const remainingQuestions = document.querySelectorAll('.question-block').length;

                document.querySelector('button[type="submit"]').disabled = true;

            }

            function renumberQuestions() {
                const questionBlocks = document.querySelectorAll('.question-block');
                questionBlocks.forEach((block, index) => {
                    const heading = block.querySelector('h3');
                    heading.textContent = `Question ${index + 1}`;

                    // Update input names and ids
                    const inputs = block.querySelectorAll('input, select');
                    inputs.forEach(input => {
                        input.name = input.name.replace(/questions\[\d+\]/, `questions[${index}]`);
                        input.id = input.id.replace(/_\d+/, `_${index}`);
                    });

                    // Update labels
                    const labels = block.querySelectorAll('label');
                    labels.forEach(label => {
                        label.setAttribute('for', label.getAttribute('for').replace(/_\d+/, `_${index}`));
                    });

                    // Update remove button
                    const removeButton = block.querySelector('button');
                    removeButton.setAttribute('onclick', `removeQuestion(${index})`);

                    // Update block id
                    block.id = `question-block-${index}`;
                });
            }

            // Initialize the select options
            toggleSelect();
        </script>
    </div>
@endsection
