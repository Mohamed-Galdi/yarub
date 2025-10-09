@extends('layouts.guest')

@section('content')
<div class="bg-slate-300 min-h-screen flex py-12 justify-center px-4">
    <div class="max-w-2xl w-full bg-white rounded-2xl shadow-2xl overflow-hidden">
        <div class="p-8 text-center space-y-6">
            
            {{-- Icon --}}
            <div class="flex justify-center">
                <div class="bg-red-100 text-red-600 p-4 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v4m0 4h.01M12 2a10 10 0 1010 10A10 10 0 0012 2z" />
                    </svg>
                </div>
            </div>

            {{-- Title --}}
            <h1 class="text-3xl font-bold text-slate-800">
                حدث خطأ أثناء معالجة الدفع
            </h1>

            {{-- Description --}}
            <p class="text-slate-600 text-lg leading-relaxed">
                للأسف لم تتم عملية الدفع بنجاح.  
                يرجى التحقق من بيانات الدفع أو المحاولة لاحقًا.
            </p>

            {{-- Error Message --}}
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-700 text-right">
                <span class="font-semibold">سبب الفشل:</span>
                <p class="mt-1">{{ $message }}</p>
            </div>

            {{-- Action --}}
            <div class="pt-4">
                <a href="{{ url('/') }}"
                   class="inline-block px-6 py-2 bg-slate-800 text-white rounded-lg hover:bg-slate-700 transition">
                    العودة إلى الصفحة الرئيسية
                </a>
            </div>
        </div>
    </div>
</div>

{{-- SweetAlert Notification --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        title: 'حدث خطأ أثناء معالجة الدفع',
        text: 'سبب الفشل: {{ $message }}',
        icon: 'error',
        confirmButtonText: 'حسنًا',
        confirmButtonColor: '#1e293b',
    });
});
</script>
@endsection
