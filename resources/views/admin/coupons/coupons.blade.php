@extends('layouts.admin')
@section('content')
    <div class="flex flex-col justify-start items-start min-h-screen space-y-6">

        <div>
            <x-btn.add route="admin.coupons.create">إضافة قسيمة جديدة</x-btn.add>
        </div>
        <div class="w-full grid lg:grid-cols-2 xl:grid-cols-3 grid-cols-1 gap-8">
            @forelse ($coupons as $coupon)
                @php
                    $is_active = $coupon->is_active && $coupon->end_date >= now() ? true : false;
                @endphp
                <div
                    class="relative w-full h-64 bg-gradient-to-tr rounded-lg border-2 border-slate-400 overflow-hidden {{ $is_active ? 'from-blue-500 to-blue-700' : 'from-slate-400 to-slate-600' }}">

                    <div style="clip-path: polygon(0 0, 100% 0, 100% 100%, 50% 80%, 0 100%);"
                        class="absolute space-y-3 pt-3 top-0 right-6 bg-gradient-to-tr  w-1/3 h-[90%] flex flex-col justify-start items-center font-judur {{ $is_active ? 'from-blue-600 to-blue-900' : 'from-slate-500 to-slate-700' }}">
                        @if ($is_active)
                            <x-card.live  />
                        @else
                            <x-card.not-live />
                        @endif
                        <h5 class="text-5xl font-semibold text-gray-100 uppercase text-center ">
                            {{ (int) $coupon->value }}
                            {{ $coupon->type == 'percentage' ? '%' : '' }}</h5>
                        <p class="text-gray-100">{{ $coupon->type == 'fixed' ? '(ريال سعودي)' : '(نسبة الخصم)' }}</p>
                        <p class="text-slate-300 text-center mt-4 font-hacen">
                            @switch($coupon->applicable_to)
                                @case('all')
                                    <span class="">كل المحتوى</span>
                                @break

                                @case('courses')
                                    <span class="">جميع الدورات</span>
                                @break

                                @case('lessons')
                                    <span class="">جميع الشروحات</span>
                                @break

                                @case('specified')
                                    <span class="">دورات و شروحات محددة</span>
                                @break

                                @default
                                    <span class="">دورات و شروحات محددة</span>
                            @endswitch
                        </p>
                    </div>
                    <div
                        class="absolute  h-full w-[calc(66%-1.5rem)]  left-0 flex flex-col justify-start items-center px-1 py-5 overflow-hidden space-y-3">
                        <div class="flex justify-between items-center gap-4 bg-gradient-to-tr px-3 pb-1 rounded-lg border hover:scale-[0.98] transition-all duration-300 ease-in-out border-white  {{$is_active ? 'from-blue-600 to-blue-800' : 'from-slate-500 to-slate-700' }}">

                            <div class="relative">
                                <h2 id="coupon-{{ $coupon->id }}"
                                    class="text-3xl uppercase text-center font-semibold font-nitaqat selection:text-gray-100 selection:bg-gray-800 {{ $is_active ? 'text-blue-100' : 'text-slate-200' }} cursor-pointer"
                                    onclick="copyCouponCode('{{ $coupon->code }}', {{ $coupon->id }})">
                                    {{ $coupon->code }}
                                </h2>
                                <div id="copied-popup-{{ $coupon->id }}"
                                    class="absolute left-1/2 -translate-x-1/2 -bottom-8 bg-gray-800 text-white px-2 py-1 rounded text-sm hidden">
                                    تم النسخ!
                                </div>
                            </div>
                        </div>


                        <div class="flex justify-between items-center gap-4">
                            <div
                                class="border-4 font-judur font-bold  rounded-full flex justify-center items-center w-16 h-16 text-3xl text-center text-slate-200 {{ $is_active ? 'bg-blue-500 text-blue-200' : 'bg-slate-500 text-slate-800' }}">
                                {{ $coupon->used_count }}
                            </div>

                            <div class="space-y-2 flex flex-col justify-center items-center">
                                <p
                                    class="w-full py-1 px-4 text-gray-100 rounded-lg text-center text-nowrap truncate {{ $is_active ? 'bg-blue-400' : 'bg-slate-500' }}">
                                    {{ $coupon->start_date->format('d-m-Y') }}</p>
                                <x-icons.arrow-down class="w-4 h-4" />
                                <p
                                    class="w-full py-1 px-4 text-gray-100 rounded-lg text-center text-nowrap truncate {{ $is_active ? 'bg-blue-400' : 'bg-slate-500' }}">
                                    {{ $coupon->end_date->format('d-m-Y') }}</p>
                            </div>

                        </div>
                        <div class="w-full">
                            <a href="{{ route('admin.coupons.edit', ['id' => $coupon->id]) }}"
                                class = "flex justify-center items-cente gap-2 mx-6  p-2 rounded-xl border-2  overflow-hidden hover:scale-[0.97] transition-all duration-300 ease-in-out cursor-pointer {{ $is_active ? 'bg-blue-400 hover:bg-blue-500 text-white border-white' : 'bg-slate-400 hover:bg-slate-500 text-white ' }}">
                                <p> Edit </p>
                                <x-icons.edit class="w-5 h-5 " />
                            </a>

                        </div>

                    </div>

                </div>
                @empty
                    <x-card.empty-state :image="true" :title="' لا توجد قسائم متوفرة حالياً'" :message="'اضغط على زر الإضافة في الأعلى لإضافة قسيمة جديدة'" />
                @endforelse
            </div>
        </div>
    @endsection
    @push('scripts')
        <script>
            function copyCouponCode(code, id) {
                navigator.clipboard.writeText(code).then(() => {
                    const popup = document.getElementById(`copied-popup-${id}`);
                    popup.classList.remove('hidden');
                    setTimeout(() => {
                        popup.classList.add('hidden');
                    }, 2000);
                }).catch(err => {
                    console.error('Failed to copy: ', err);
                });
            }
        </script>
    @endpush
