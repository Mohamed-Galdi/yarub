@extends('layouts.guest')
@section('content')
    <div class="mx-auto max-w-screen-xl lg:py-16 pt-1 pb-6 lg:px-2 px-6 space-y-12 text-primary min-h-screen">
        {{-- Course --}}
        @php
            // Mapping of types to Arabic labels
            $typeLabels = [
                'courses' => 'حقيبة دورات',
                'lessons' => 'حقيبة الشروحات',
                'mixed' => 'حقيبة مختلطة',
            ];
        @endphp
        <div class=" flex lg:flex-row flex-col lg:justify-center lg:items-stretch gap-12 h-fit py-4">
            <div class="lg:w-2/5 lg:order-1 w-full order-2   space-y-4 ">
                <p class="  text-slate-100 px-1 bg-pr-400 rounded-xl w-fit mt-2 text-base">
                    {{ $typeLabels[$package->type] ?? $package->type }}</p>
                <h1 class="text-start lg:text-4xl md:text-5xl text-4xl ">{{ $package->title }}</h1>
                <p class="text-start text-xl text-gray-400 line-clamp-3 ">{{ $package->description }}</p>
                <div class="flex gap-3 justify-between items-start">
                    <div class="w-1/2 text-center bg-red-200 text-slate-800 rounded-lg">
                        <p>عدد الدورات</p>
                        <p>{{ $package->courses()->count() }}</p>
                    </div>
                    <div class="w-1/2 text-center bg-red-200 text-slate-800 rounded-lg">
                        <p>عدد الشروحات</p>
                        <p>{{ $package->lessons()->count() }}</p>
                    </div>

                </div>
                <div class="w-full">
                    <div class="flex w-full justify-between items-start gap-x-4">
                        @if ($package->type === 'courses')
                            <div class="bg-slate-700 px-2 pb-2 rounded-2xl w-1/3 text-center ">
                                <p class="font-nitaqat text-warning-500 font-bold text-xl">{{ $package->price }} ر.س </p>
                            </div>
                        @else
                            <div class="bg-slate-700 px-2 pb-2 rounded-2xl w-1/2">
                                <p class="font-nitaqat text-warning-500 font-bold text-xl  text-center">
                                    {{ $package->monthly_price }} ر.س<span class="text-white "> /
                                        شهريا</span></p>
                            </div>
                            <div class="bg-slate-700 px-2 pb-2 rounded-2xl w-1/2">
                                <p class="font-nitaqat text-warning-500 font-bold text-xl text-center">
                                    {{ $package->annual_price }} ر.س <span class="text-white "> /
                                        سنويا</span></p>
                            </div>
                        @endif
                    </div>

                </div>
                <button
                    class="bg-red-500 w-full text-white border-2 border-gray-100 text-xl py-2 px-4 rounded-2xl flex gap-4 justify-center items-center 
              hover:text-warning-300  group transition-all duration-300 ease-in-out add-to-cart"
                    data-item-id="{{ $package->id }}" data-type="package" data-title="{{ $package->title }}"
                    data-description="{{ $package->description }}" data-price="{{ $package->price }}"
                    data-annual-price="{{ $package->annual_price }}" data-monthly-price="{{ $package->monthly_price }}">
                    <p> أضف الى السلة</p>
                    <x-icons.cart class="w-6 h-6" />
                </button>

            </div>
            <div class="lg:w-2/5 lg:order-2 w-full order-1   ">
                <div
                    class=" rounded-xl lg:block hidden h-full overflow-hidden bg-gradient-to-tr from-red-800 to-red-400 shadow-lg p-4 ">
                    <x-card.course-img title="{{ $package->title }}" class="" />
                </div>
            </div>
        </div>
        <div class="px-2">
            <h2 class="text-3xl mb-1">محتويات الحقيبة</h2>
            <div class="h-full w-full rounded-md bg-gradient-to-r p-1  to-indigo-500 via-red-500 from-teal-500 ">
                <div class="flex h-full w-full items-center justify-center bg-slate-50 back p-6">
                    <div id="coursesList"
                        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12 px-8 place-items-center">
                        @foreach ($courses as $course)
                            <x-card.package-course data-type="{{ $course->type }}" class="w-[22rem]" :id="$course->id"
                                :title="$course->title" :description="$course->description" :price="$course->price" :type="$course->type" :averagerating="$course->reviews_avg_rating"
                                :totalreviews="$course->reviews_count" :content_type="$course->content_type" />
                        @endforeach
                        @foreach ($lessons as $lesson)
                            <x-card.package-lesson class="w-[22rem] " data-type="{{ $lesson->type }}" :id="$lesson->id"
                                :title="$lesson->title" :description="$lesson->description" :monthly-price="$lesson->monthly_price" :annual-price="$lesson->annual_price"
                                :type="$lesson->type" :averagerating="$lesson->reviews_avg_rating" :totalreviews="$lesson->reviews_count" />
                        @endforeach
                    </div>
                </div>
            </div>
        </div>






    </div>
@endsection
