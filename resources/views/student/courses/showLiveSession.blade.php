@extends('layouts.student')
@section('content')
    <div class="w-full min-h-[calc(100vh-6rem)] bg-gradient-to-tr from-gray-400 to-slate-100">
        {{-- Wait For Live Session --}}
        <div class="max-w-screen-xl mx-auto  p-4 space-y-8 flex flex-col justify-start items-center">
            <div class="flex justify-start items-end gap-4">
                <h1 class="lg:text-4xl text-xl mt-4 font-bold text-slate-700">حصة مباشرة</h1>
                <x-icons.live class="lg:w-10 w-5 lg:h-10 h-5 text-slate-700" />
                <h2 class="lg:text-3xl text-lg text-slate-500"> : {{ $course->title }}</h2>
            </div>
            @if ($meetingData['status'] == 'waiting')
                <div class="w-full text-xl flex md:flex-row flex-col justify-center items-center gap-4 mt-4">
                    <div
                        class="flex justify-center md:w-1/3 w-full items-center gap-2 bg-slate-800 text-slate-100 rounded-xl p-2">
                        <p class="text-lg text-slate-300">مدة البث: </p>
                        <p>{{ $course->liveSession->duration }} دقائق</p>
                    </div>
                    <div
                        class="flex justify-center md:w-1/3 w-full items-center gap-2 bg-slate-800 text-slate-100 rounded-xl p-2">
                        <p class="text-lg text-slate-300">تاريخ البداء: </p>
                        <p dir="ltr">{{ Carbon\Carbon::parse($course->liveSession->start_time)->format('d-m-Y H:i') }}
                        </p>
                    </div>
                </div>
                {{-- Timer --}}
                <div dir="ltr">
                    <div class="flex items-start justify-center w-full gap-4 count-down-main">
                        <div class="timer w-16">
                            <div class=" bg-indigo-500 py-4 px-2 rounded-lg overflow-hidden">
                                <h3
                                    class="countdown-element days font-Cormorant font-semibold text-2xl text-white text-center">
                                </h3>
                            </div>
                            <p class="text-lg font-Cormorant font-medium text-gray-900 mt-1 text-center w-full">أيام</p>
                        </div>
                        <h3 class="font-manrope font-semibold text-2xl text-gray-900 mt-4">:</h3>
                        <div class="timer w-16">
                            <div class=" bg-indigo-500 py-4 px-2 rounded-lg overflow-hidden">
                                <h3
                                    class="countdown-element hours font-Cormorant font-semibold text-2xl text-white text-center">
                                </h3>
                            </div>
                            <p class="text-lg font-Cormorant font-normal text-gray-900 mt-1 text-center w-full"> ساعات</p>
                        </div>
                        <h3 class="font-manrope font-semibold text-2xl text-gray-900 mt-4">:</h3>
                        <div class="timer w-16">
                            <div class=" bg-indigo-500 py-4 px-2 rounded-lg overflow-hidden">
                                <h3
                                    class="countdown-element minutes font-Cormorant font-semibold text-2xl text-white text-center">
                                </h3>
                            </div>
                            <p class="text-lg font-Cormorant font-normal text-gray-900 mt-1 text-center w-full">دقائق</p>
                        </div>
                        <h3 class="font-manrope font-semibold text-2xl text-gray-900 mt-4">:</h3>
                        <div class="timer w-16">
                            <div class=" bg-indigo-500 py-4 px-2 rounded-lg overflow-hidden ">
                                <h3
                                    class="countdown-element seconds font-Cormorant font-semibold text-2xl text-white text-center animate-countinsecond">
                                </h3>
                            </div>
                            <p class="text-lg font-Cormorant font-normal text-gray-900 mt-1 text-center w-full">ثانية</p>
                        </div>
                    </div>
                </div>
                <img src="{{ asset('assets/images/waiting_for_session.png') }}" alt="صورة حصة مباشرة"
                    class="lg:w-1/3 md:w-1/2 w-full">
            @else
                <div class="text-center flex flex-col gap-4 items-center justify-center w-full px-6 mt-8 h-[50vh]">
                    <h2 class="text-2xl text-red-500">بدأ البث... </h2>
                    <div class="flex md:flex-row flex-col w-2/3 justify-center items-center gap-6">
                        <a href="{{ $course->liveSession->join_url }}" target="_blank"
                            class="md:w-1/2 w-full bg-indigo-500 text-white rounded-lg p-2 hover:bg-indigo-600 transition-all duration-300 ease-in-out">
                            الإنضمام من ZOOM </a>
                        <a href="{{ route('student.courses.livePage', $course) }}"
                            class="md:w-1/2 w-full bg-indigo-500 text-white rounded-lg p-2 hover:bg-indigo-600 transition-all duration-300 ease-in-out">الإنضمام
                            من هنا</a>
                    </div>
                </div>
            @endif
        </div>
    </div>
    <script>
        // count-down timer
        // let dest = new Date("apr 25, 2026 10:00:00").getTime();
        let dest = new Date("{{ \Carbon\Carbon::parse($course->liveSession->start_time)->format('M d, Y H:i:s') }}")
            .getTime();
        let x = setInterval(function() {
            let now = new Date().getTime();
            let diff = dest - now;

            // Check if the countdown has reached zero or negative
            if (diff <= 0) {
                clearInterval(x); // Stop the countdown
                return; // Exit the function
            }

            let days = Math.floor(diff / (1000 * 60 * 60 * 24));
            let hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            let minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            let seconds = Math.floor((diff % (1000 * 60)) / 1000);

            if (days < 10) {
                days = `0${days}`;
            }
            if (hours < 10) {
                hours = `0${hours}`;
            }
            if (minutes < 10) {
                minutes = `0${minutes}`;
            }
            if (seconds < 10) {
                seconds = `0${seconds}`;
            }

            // Get elements by class name
            let countdownElements = document.getElementsByClassName("countdown-element");

            // Loop through the elements and update their content
            for (let i = 0; i < countdownElements.length; i++) {
                let className = countdownElements[i].classList[1]; // Get the second class name
                switch (className) {
                    case "days":
                        countdownElements[i].innerHTML = days;
                        break;
                    case "hours":
                        countdownElements[i].innerHTML = hours;
                        break;
                    case "minutes":
                        countdownElements[i].innerHTML = minutes;
                        break;
                    case "seconds":
                        countdownElements[i].innerHTML = seconds;
                        break;
                    default:
                        break;
                }
            }
        }, 1000);
    </script>
@endsection()
