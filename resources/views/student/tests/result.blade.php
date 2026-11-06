@extends('layouts.student')
@section('content')
    <div class="w-full min-h-[calc(100vh-6rem)]  ">
        <div class="max-w-screen-xl mx-auto p-4 space-y-10">
            {{-- Test Infos --}}
            <div class="my-6 space-y-3">
                <div class="pb-4 flex justify-between items-center">
                    <p class="text-3xl">درجتك: <span
                            class="p-1 bg-green-500 text-white rounded-lg mx-1 m">{{ number_format($testAttempt->score, 2) }}%</span>
                    </p>
                    <x-btn.back route="student.tests.index" />

                </div>
                <div class="panel-test-title">
                    <h1 class="panel-page-title">{{ $test->title }}</h1>
                    {{-- <p class="font-judur text-2xl text-slate-500 text-nowrap truncate">({{Str::words( $test->description , 8)}})</p> --}}
                    <p class="panel-status">{{ $test->type }}</p>
                </div>
                <div class="flex items-center justify-start gap-2 text-xl">
                    <p class="font-bold text-slate-900 underline underline-offset-2">{{ $test->course ? 'دورة' : 'شرح' }} :
                    </p>
                    <p class="font-judur">{{ $test->course ? $test->course->title : $test->lesson->title }}</p>
                </div>
            </div>
            <div>
                <div class="mt-8">
                    @foreach ($answersWithCorrectness as $index => $item)
                        <p class="my-2 text-lg">{{ $item['question']?->question_text }}</p>
                        <div class="panel-surface mb-6 {{ $item['isCorrect'] ? 'panel-answer-correct' : 'panel-answer-incorrect' }}">
                            <p class="text-xl font-bold">{{ $index + 1 }}. {{ $item['question']->question }}</p>
                            <ul class="mt-2 text-lg">
                                @foreach (['option_1', 'option_2', 'option_3', 'option_4'] as $optionKey => $option)
                                    <li
                                        class="{{ $optionKey + 1 == $item['question']->correct_answer ? 'font-bold text-green-700' : '' }} 
                                           {{ $optionKey + 1 == $item['userAnswer'] && !$item['isCorrect'] ? 'font-bold text-red-700' : '' }}">
                                        {{ $item['question']->$option }}
                                        @if ($optionKey + 1 == $item['question']->correct_answer)
                                            (الإجابة الصحيحة)
                                        @elseif ($optionKey + 1 == $item['userAnswer'] && !$item['isCorrect'])
                                            (إجابتك)
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
@endsection
