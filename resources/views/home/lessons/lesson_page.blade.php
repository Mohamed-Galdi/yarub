@extends('layouts.guest')
@section('content')
    <div class="mx-auto max-w-screen-xl lg:py-16 pt-1 pb-6 lg:px-2 px-6 space-y-12 text-primary min-h-screen">
        {{-- Course --}}
        <div class=" flex lg:flex-row flex-col lg:justify-center lg:items-stretch gap-12 h-fit py-4">
            <div class="lg:w-2/5 lg:order-1 w-full order-2   space-y-4 ">
                <p class="  text-slate-100 px-1 bg-pr-400 rounded-xl w-fit mt-2 text-base">{{ $lesson->type }}</p>
                <h1 class="text-start lg:text-4xl md:text-5xl text-4xl ">{{ $lesson->title }}</h1>
                <p class="text-start text-xl text-gray-400 ">{{ Str::words($lesson->description, 9) }}</p>
                <div class="flex lg:flex-row flex-col justify-center items-center gap-4 mt-4 w-full ">
                    <p class="text-warning-500 text-2xl font-bold bg-slate-200 rounded-xl px-4 py-2 lg:w-1/2 w-full">{{ $lesson->monthly_price }} <span class="font-normal text-xl text-gray-400">
                             ريال سعودي </span> <span class="text-sm text-gray-600 font-nitaqat">/ شهريا </span></p>
                    <p class="text-warning-500 text-2xl font-bold bg-slate-200 rounded-xl px-4 py-2 lg:w-1/2 w-full">{{ $lesson->annual_price }} <span class="font-normal text-xl text-gray-400">
                            ريال سعودي </span> <span class="text-sm text-gray-600 font-nitaqat">/ سنويا </span></p>
                </div>
                <button
                    class="bg-teal-500 w-full text-white border-2 border-gray-100 text-xl py-2 px-4 rounded-2xl flex gap-4 justify-center items-center 
              hover:text-warning-400  group transition-all duration-300 ease-in-out add-to-cart"
                    data-item-id="{{ $lesson->id }}" data-type="lesson" data-title="{{ $lesson->title }}"
                    data-description="{{ $lesson->description }}" data-price="{{ $lesson->price }}">
                    <p> أضف الى السلة</p>
                    <x-icons.cart class="w-6 h-6" />
                </button>

            </div>
            <div class="lg:w-2/5 lg:order-2 w-full order-1   ">
                <div
                    class=" rounded-xl lg:block hidden h-full overflow-hidden bg-gradient-to-tr from-teal-800 to-teal-400 shadow-lg p-4 ">
                    <x-card.course-img title="{{ $lesson->title }}" class="" />
                </div>
            </div>
        </div>

        {{-- Reviews --}}
        <div class="mt-16 bg-slate-200 rounded-md p-6">
            <h3 class="text-lg font-bold text-[#333]">التقييمات({{ $lesson->reviews->count() }})</h3>
            @forelse ($lesson->reviews as $review)
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
            <div class="mt-8 flex lg:flex-row flex-col justify-between items-start gap-4  w-full">
                {{-- card --}}
                @foreach ($suggestions as $suggestion)
                    <x-card.guest-lesson class="w-[22rem]" :id="$suggestion->id" :title="$suggestion->title" :description="$suggestion->description"
                        :monthly-price="$suggestion->monthly_price" :annual-price="$suggestion->annual_price" :type="$suggestion->type"  :averagerating="$suggestion->reviews_avg_rating" :totalreviews="$suggestion->reviews_count" />
                @endforeach
            </div>
        </div>
    </div>

@endsection
