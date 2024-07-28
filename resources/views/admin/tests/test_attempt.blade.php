@extends('layouts.admin')
@section('content')
    <div class="container">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            {{-- student --}}
            <div
                class="flex justify-start items-start gap-6 bg-gradient-to-l from-indigo-500 to-purple-800 rounded-lg p-4 shadow-md text-gray-200">
                <img src="{{ asset($testAttempt->user->avatar) }}" alt="Profile Picture" class="rounded-circle" width="100"
                    height="100">
                <div>
                    <h2 class="text-2xl font-bold text-white"> {{ $testAttempt->user->name }}</h2>
                    <p>تاريخ الإجتياز: {{ $testAttempt->created_at->format('d-m-Y') }}</p>

                </div>
            </div>
            {{-- test --}}
            <div class=" gap-6 bg-gradient-to-l from-indigo-500 to-purple-800 rounded-lg p-4 shadow-md text-gray-200">
                <h2 class="text-2xl font-bold text-white"> {{ $testAttempt->test->title }}</h2>
                <p class="font-judur">
                    {{ $testAttempt->test->course_id ? $testAttempt->test->course->title : $testAttempt->test->lesson->title }}
                </p>
                <div class="m-2 py-2 px-4 bg-gray-200 text-gray-800 rounded-lg shadow-md w-fit ">
                    {{ $testAttempt->test->type }}
                </div>
            </div>
            {{-- score --}}
            <div class=" gap-6 bg-gradient-to-l from-indigo-500 to-purple-800 rounded-lg p-4 shadow-md text-gray-200">
                <h2 class="text-2xl font-bold text-white">النتيجة</h2>
                <div class="flex justify-center items-end gap-1">
                    <p class="font-judur text-gray-200 text-2xl">({{ number_format($percentage, 2) }}%)</p>
                    <p class="text-6xl font-judur text-white" dir="ltr"> {{ $score }}/{{ $totalQuestions }} </p>
                </div>
            </div>
        </div>

        <h2 class="my-8 text-4xl font-bold font-judur text-gray-800">الأسئلة و الإجابات </h2>
        <div class="space-y-16">
            @foreach ($result as $item)
                <div class="bg-gradient-to-bl from-slate-700 to-slate-500 p-4 rounded-lg shadow-md text-white text-2xl">
                    <div class="space-y-4">
                        <h5 class="text-3xl text-indigo-400">السؤال {{ $loop->iteration }}:
                            {{ $item['question']->question }}</h5>
                        <div class="flex lg:flex-row flex-col gap-6">
                            <p class="text-2xl"><span class="text-xl font-nitaqat text-gray-300">إجابة الطالب:</span>
                                @if ($item['student_answer_text'])
                                    {{ $item['student_answer_text'] }} (الخيار {{ $item['student_answer'] }})
                                @else
                                    لم يتم الإجابة عليه
                                @endif
                            </p>
                            <p class="text-2xl"><span class="text-xl font-nitaqat text-gray-300">الجواب الصحيح :</span>
                                {{ $item['correct_answer_text'] }} (الخيار
                                {{ $item['question']->correct_answer }})
                            </p>
                        </div>
                        <ol class="bg-slate-300 rounded-lg p-4 text-white grid lg:grid-cols-2 grid-cols-1 gap-4">
                            @for ($i = 1; $i <= 4; $i++)
                                @php
                                    $option = 'option_' . $i;
                                    $isCorrect = $item['question']->correct_answer == $i;
                                    $isSelected = $item['student_answer'] == $i;
                                    $incorrectSelected = !$isCorrect && $isSelected;
                                @endphp
                                <li
                                    class="text-xl p-2 rounded-lg {{ $incorrectSelected ? 'bg-red-500' : ($isCorrect && $isSelected ? 'bg-green-500' : ($isCorrect ? 'bg-slate-400' : 'bg-slate-100 text-gray-800')) }}">
                                    <div class="flex items-center gap-2">
                                        @if ($isCorrect && $isSelected)
                                            <x-icons.correct class="text-gray-100 w-6 h-6" />
                                        @elseif ($incorrectSelected)
                                            <x-icons.wrong class="text-gray-100 w-6 h-6" />
                                        @elseif ($isCorrect)
                                            <x-icons.correct class="text-gray-100 w-6 h-6" />
                                        @endif
                                        {{ $item['question']->$option }}
                                    </div>
                                </li>
                            @endfor
                        </ol>

                        {{-- <p>Result: {{ $item['is_correct'] ? 'Correct' : 'Incorrect' }}</p> --}}

                    </div>
                </div>
            @endforeach





        </div>

    </div>
@endsection
