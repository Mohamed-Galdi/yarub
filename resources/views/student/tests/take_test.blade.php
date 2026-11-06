@extends('layouts.student')
@section('content')
    <div class="w-full min-h-[calc(100vh-6rem)]  ">
        <div class="max-w-screen-xl mx-auto p-4 space-y-8 relative">
            <div class="panel-test-heading">
                {{-- Test Infos --}}
                <div class="mt-6 space-y-3 w-5/6">
                    <div class="panel-test-title">
                        <h1 class="panel-page-title">{{ $test->title }}</h1>
                        {{-- <p class="font-judur text-2xl text-slate-500 text-nowrap truncate">({{Str::words( $test->description , 8)}})</p> --}}
                        <p class="panel-status">{{ $test->type }}</p>
                    </div>
                    <div class="flex items-center justify-start gap-2 text-xl">
                        <p class="font-bold text-slate-900 underline underline-offset-2">{{ $test->course ? 'دورة' : 'شرح' }}
                            :
                        </p>
                        <p class="font-judur">{{ $test->course ? $test->course->title : $test->lesson->title }}</p>
                    </div>
                </div>
               <div class="w-1/6">
                   <x-btn.back route="student.tests.index" />
               </div>
            </div>


            {{-- Start button --}}
            <div id="startButton" class="w-full flex justify-center h-[40vh] items-center">
                <button id="startButton"
                    class="panel-button panel-button--primary">ابدأ
                    الاختبار</button>
            </div>

            {{-- Loader button --}}
            <div id="loader" class="hidden absolute top-[100%] right-[45%] w-full h-full  items-center justify-center">
                <div class="animate-spin rounded-full h-32 w-32 border-t-2 border-b-2 border-blue-500"></div>
            </div>

            {{-- Test form --}}
            <div class="hidden" id="testContainer">
                <div class="panel-surface my-4">
                    <p>- أختي الطالبة ، وفقك الله، اجيبي عن جميع الأسئلة مستعينه بالله ومتوكله عليه.</p>
                    <p> - اقرأ الأسئلة جيدا قبل الحل وتأكد من إجابتك.</p>

                </div>
                <form id="testForm" action="{{ route('student.tests.submit', $test) }}" method="POST">
                    @csrf
                    @foreach ($test->questions as $index => $question)
                        <div class="panel-surface mb-4 space-y-4">
                            <p class="text-lg">{{ $question->question_text }}</p>
                            <p class="text-lg font-bold text-slate-600">{{ $index + 1 }}. {{ $question->question }}</p>
                            <ul class="mt-2 grid lg:grid-cols-2 grid-cols-1 gap-2">
                                @foreach (['option_1', 'option_2', 'option_3', 'option_4'] as $option)
                                    
                                    <li>
                                        <input type="radio" id="{{ $question->id . '_option_' . $loop->index }}"
                                            name="answers[{{ $question->id }}]" value="{{ $loop->index + 1 }}"
                                            class="sr-only peer" required />
                                        <label for="{{ $question->id . '_option_' . $loop->index }}"
                                            class="panel-answer-option inline-flex items-center justify-between w-full p-4 cursor-pointer ">
                                            <div class="block">
                                                <div class="w-full text-lg">{{ $question->$option }}</div>

                                            </div>
                                        </label>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                    <button type="submit"
                        class="panel-button panel-button--primary w-full text-center py-3 px-4 rounded-xl mb-4"
                        id="submitBtn">
                        تسليم الاختبار
                    </button>
                </form>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    {{-- Start button and Loader --}}
    <script>
        document.getElementById('startButton').addEventListener('click', function(e) {
            e.preventDefault();

            // Hide start button
            this.classList.add('hidden');

            // Show loader
            document.getElementById('loader').classList.remove('hidden');

            // After 1 second, hide loader and show form
            setTimeout(function() {
                document.getElementById('loader').classList.add('hidden');
                document.getElementById('testContainer').classList.remove('hidden');
            }, 1000);
        });
    </script>

    {{-- Submition Confirmation --}}
    <script>
        document.getElementById('submitBtn').addEventListener('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'هل أنت متأكد؟',
                text: 'يرجى التحقق من إجاباتك مرة أخرى قبل التسليم.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'نعم، أريد التسليم',
                cancelButtonText: 'لا، أريد المراجعة'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('testForm').submit();
                }
            });
        });
    </script>
@endpush
