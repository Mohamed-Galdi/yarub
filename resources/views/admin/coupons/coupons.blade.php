@extends('layouts.admin')
@section('content')
    <div class="flex flex-col justify-start items-start min-h-screen space-y-6">

        <div>
            <x-btn.add route="admin.coupons.create">إضافة قسيمة جديدة</x-btn.add>
        </div>
        <div class="w-full grid lg:grid-cols-4 md:grid-cols-2 grid-cols-1 gap-8">
            @forelse ($coupons as $coupon)
                <div class="w-full h-80 bg-white rounded-lg border-2 border-yellow-300 overflow-hidden">
                    <div class="w-full h-2/5 bg-yellow-300 flex justify-center items-center">
                        <h2 class="text-3xl font-bold text-center">{{ $coupon->code }}</h2>
                    </div>
                    <div class="w-full h-3/5 flex justify-center items-start gap-2">
                        <div class="p-1 bg-warning-300 rounded-b-lg ">
                            <p> {{ $coupon->type == 'fixed' ? 'مبلغ الخصم ' : 'نسبة الخصم' }}</p>
                            <div>
                                <p class="text-3xl">{{ (int) $coupon->value }}</p>
                                <p>{{ $coupon->type == 'fixed' ? 'ريال سعودي' : '%' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <p>No coupons found.</p>
            @endforelse
        </div>


        {{-- @forelse ($coupons as $coupon)
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">{{ $coupon->code }}</h5>
                    <p class="card-text">
                        <strong>Type:</strong> {{ $coupon->type }}<br>
                        <strong>Value:</strong> {{ $coupon->value }}<br>
                        <strong>Applicable to:</strong> {{ $coupon->applicable_to }}<br>
                        <strong>Start date:</strong> {{ $coupon->start_date }}<br>
                        <strong>End date:</strong> {{ $coupon->end_date }}<br>
                        <strong>Usage limit:</strong> {{ $coupon->usage_limit }}<br>
                        <strong>Used count:</strong> {{ $coupon->used_count }}<br>
                        <strong>Is active:</strong> {{ $coupon->is_active }}<br>
                    </p>
                </div>
            </div>

        @empty
            <p>No coupons found.</p>
        @endforelse --}}
    </div>
@endsection