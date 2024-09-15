@extends('layouts.guest')
@section('content')
    <div class="mx-auto max-w-screen-xl lg:py-16 pt-1 pb-6 lg:px-2 px-6 space-y-12 text-primary min-h-screen">
        {{-- Course --}}
        <div class=" flex lg:flex-row flex-col lg:justify-center lg:items-stretch gap-12 h-fit py-4">
            <div class="lg:w-2/5 lg:order-1 w-full order-2   space-y-4 ">

                @if ($course->content_type === 'live_session')
                    <div
                        class="flex justify-start items-center gap-2 text-slate-100 px-1 py-[2px]  bg-red-500 rounded-xl w-fit mt-2 text-base">
                        <p>حصة مباشرة</p>
                        <x-icons.live class="w-5 h-5 ml-2" />
                    </div>
                @else
                    <p class="  text-slate-100 px-1 bg-pr-400 rounded-xl w-fit mt-2 text-base">{{ $course->type }}</p>
                @endif
                <h1 class="text-start lg:text-4xl md:text-5xl text-4xl ">{{ $course->title }}</h1>
                <p class="text-start text-xl text-gray-400 line-clamp-3  ">{{ $course->description }}</p>
                @if ($course->content_type === 'live_session')
                    <div class="w-full flex md:flex-row flex-col justify-start items-center gap-4 mt-4">
                        <div
                            class="flex justify-start md:w-1/3 w-full items-center gap-2 bg-slate-800 text-slate-100 rounded-xl p-2">
                            <p class="text-base text-slate-300">مدة البث: </p>
                            <p>{{ $course->liveSession->duration }} دقائق</p>
                        </div>
                        <div
                            class="flex justify-start md:w-2/3 w-full items-center gap-2 bg-slate-800 text-slate-100 rounded-xl p-2">
                            <p class="text-base text-slate-300">تاريخ البداء: </p>
                            <p dir="ltr">
                                {{ Carbon\Carbon::parse($course->liveSession->start_time)->format('d-m-Y H:i') }}</p>
                        </div>

                    </div>
                    <div>
                        {{-- Timer --}}
                        <div dir="ltr">
                            <div class="flex items-start justify-center w-full gap-4 count-down-main">
                                <div class="timer w-16">
                                    <div class=" bg-indigo-500 py-4 px-2 rounded-lg overflow-hidden">
                                        <h3
                                            class="countdown-element days font-Cormorant font-semibold text-2xl text-white text-center">
                                        </h3>
                                    </div>
                                    <p class="text-lg font-Cormorant font-medium text-gray-900 mt-1 text-center w-full">أيام
                                    </p>
                                </div>
                                <h3 class="font-manrope font-semibold text-2xl text-gray-900 mt-4">:</h3>
                                <div class="timer w-16">
                                    <div class=" bg-indigo-500 py-4 px-2 rounded-lg overflow-hidden">
                                        <h3
                                            class="countdown-element hours font-Cormorant font-semibold text-2xl text-white text-center">
                                        </h3>
                                    </div>
                                    <p class="text-lg font-Cormorant font-normal text-gray-900 mt-1 text-center w-full">
                                        ساعات</p>
                                </div>
                                <h3 class="font-manrope font-semibold text-2xl text-gray-900 mt-4">:</h3>
                                <div class="timer w-16">
                                    <div class=" bg-indigo-500 py-4 px-2 rounded-lg overflow-hidden">
                                        <h3
                                            class="countdown-element minutes font-Cormorant font-semibold text-2xl text-white text-center">
                                        </h3>
                                    </div>
                                    <p class="text-lg font-Cormorant font-normal text-gray-900 mt-1 text-center w-full">
                                        دقائق</p>
                                </div>
                                <h3 class="font-manrope font-semibold text-2xl text-gray-900 mt-4">:</h3>
                                <div class="timer w-16">
                                    <div class=" bg-indigo-500 py-4 px-2 rounded-lg overflow-hidden ">
                                        <h3
                                            class="countdown-element seconds font-Cormorant font-semibold text-2xl text-white text-center animate-countinsecond">
                                        </h3>
                                    </div>
                                    <p class="text-lg font-Cormorant font-normal text-gray-900 mt-1 text-center w-full">
                                        ثانية</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                <div class="flex justify-center items-center gap-2 mt-4 bg-slate-200 rounded-xl px-4 py-2 w-fit">
                    <p class="text-warning-500 text-3xl font-bold">{{ $course->price }} <span class="font-normal text-xl">
                            ريال سعودي </span> </p>
                </div>
                <button
                    class="bg-indigo-500 w-full text-white border-2 border-gray-100 text-xl py-2 px-4 rounded-2xl flex gap-4 justify-center items-center 
              hover:text-warning-400  group transition-all duration-300 ease-in-out add-to-cart"
                    data-item-id="{{ $course->id }}" data-type="course" data-title="{{ $course->title }}"
                    data-description="{{ $course->description }}" data-price="{{ $course->price }}">
                    <p> أضف الى السلة</p>
                    <x-icons.cart class="w-6 h-6" />
                </button>

            </div>
            <div class="lg:w-2/5 lg:order-2 w-full order-1   ">
                <div
                    class=" rounded-xl lg:block hidden h-full overflow-hidden bg-gradient-to-tr from-indigo-800 to-indigo-400 shadow-lg p-4 ">
                    <x-card.course-img title="{{ $course->title }}" class="" />
                </div>
            </div>
        </div>

        {{-- Reviews --}}
        <div class="mt-16 bg-slate-200 rounded-md p-6">
            <h3 class="text-lg font-bold text-[#333]">التقييمات({{ $course->reviews->count() }})</h3>
            @forelse ($course->reviews as $review)
                <div
                    class="bg-gray-100 hover:bg-indigo-300  shadow-lg hover:shadow-none transition-all duration-300 ease-in-out rounded-lg px-6 py-2 w-full mt-8 flex gap-4">
                    <div class="w-32 flex flex-col justify-start items-center">
                        <img src="{{ asset($review->user->avatar) }}" class="w-12 h-12 rounded-full " />
                        <p class="font-pr text-center">{{ Str::words($review->user->name, 2) }}</p>
                    </div>
                    <div class="w-1 bg-pr rounded-md">
                    </div>
                    <div>
                        @for ($i = 1; $i <= 5; $i++)
                            @if ($i <= $review->rating)
                                <span class="text-yellow-500 text-3xl">★</span>
                            @else
                                <span class="text-gray-500 text-xl">★</span>
                            @endif
                        @endfor
                        <p class="mt-2 font-sec text-lg">{{ $review->comment }}</p>
                    </div>
                </div>
            @empty
                <p class="mt-2 font-sec text-lg">لا يوجد تقييمات حتى الآن لهذه الدورة </p>
            @endforelse

        </div>

        {{-- Suggestions --}}
        <div class="px-2 mx-4 mb-6">
            <h2 class="text-3xl ">بعض الإقتراحات، تنال إعجابك</h2>
            <div class="mt-8 flex lg:flex-row flex-col justify-between items-center gap-4  w-full">
                {{-- card --}}
                @foreach ($suggestions as $suggestion)
                    <x-card.guest-course class="w-[22rem]" :id="$suggestion->id" :title="$suggestion->title" :description="$suggestion->description"
                        :content_type="$suggestion->content_type" :price="$suggestion->price" :type="$suggestion->type" :averagerating="$suggestion->reviews_avg_rating" :totalreviews="$suggestion->reviews_count" />
                @endforeach
            </div>
        </div>
    </div>

    @if ($course->content_type === 'live_session')
        <script>
            // count-down timer
            // let dest = new Date("apr 25, 2025 10:00:00").getTime();
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
    @endif
@endsection
