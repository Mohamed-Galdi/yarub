@extends('layouts.admin')
@section('content')
    <div class="">
        <h1 class="lg:text-4xl text-2xl text-nowrap truncate text-indigo-700 mb-4">المدفوعات</h1>
        </h1>
        <div>
            <table class="w-full text-left rtl:text-right text-gray-500 ">
                <thead class="text-lg  text-gray-100 uppercase bg-indigo-500 w-full">
                    <tr>
                        <th scope="col" class="px-6 py-3 w-[25%] text-nowrap truncate">
                            الطالب
                        </th>
                        <th scope="col" class="px-6 py-3 w-[12.5%]">
                            المبلغ الاصلي
                        </th>
                        <th scope="col" class="px-6 py-3 w-[12.5%] text-nowrap truncate">
                            القسيمة
                        </th>
                        <th scope="col" class="px-6 py-3 w-[12.5%] ">
                            خصم القسيمة
                        </th>
                        <th scope="col" class="px-6 py-3 w-[12.5%] text-nowrap truncate">
                            المبلغ المدفوع
                        </th>
                        <th scope="col" class="px-6 py-3 w-[12.5%] text-nowrap truncate">
                            حالة الدفع
                        </th>
                        <th scope="col" class="px-6 py-3 w-[12.5%] text-nowrap truncate">
                            التاريخ
                        </th>

                    </tr>
                </thead>
                <tbody>
                    @foreach ($payments as $payment)
                        <tr
                            class="border-b cursor-pointer  w-full text-slate-800 transition-all ease-in-out duration-00 bg-slate-200 hover:bg-slate-300">

                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <p class=" text-nowrap truncate w-full">
                                        {{ $payment->user->name }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <p class=" text-nowrap truncate w-full text-blue-500">
                                        {{ $payment->original_amount }} ريال</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <p class=" text-nowrap truncate w-full {{ $payment->coupon_used ? 'text-yellow-400' : '' }}">
                                        {{ $payment->coupon_used ?? 'لا يوجد' }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <p class=" text-nowrap truncate w-full text-center {{ $payment->coupon_used ? 'text-yellow-400' : '' }}">
                                        {{ $payment->coupon_reduction ?$payment->coupon_reduction. ' ريال ': 'لا يوجد' }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <p class=" text-nowrap truncate w-full text-green-500">
                                        {{ $payment->payment_amount }} ريال</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <p class=" text-nowrap truncate w-full">
                                        {{ $payment->payment_status == 'paid' ? 'مدفوع' : 'لم يتم الدفع' }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <p class=" text-nowrap truncate w-full">
                                        {{ $payment->created_at->format('d-m-Y') }}</p>
                                </div>
                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table> 
            <div class="flex justify-center mt-4">
                {{ $payments->links() }}
            </div>
        </div>
    </div>
@endsection()
