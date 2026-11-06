@extends('layouts.admin')
@section('panel-heading', true)

@section('content')
    <x-panel.heading title="القسائم">
        <x-slot:actions>
            <x-panel.button :href="route('admin.coupons.create')" variant="primary">إضافة قسيمة جديدة</x-panel.button>
        </x-slot:actions>
    </x-panel.heading>

    <div class="panel-section panel-grid panel-grid--courses">
        @forelse ($coupons as $coupon)
            @php
                $isActive = $coupon->is_active && $coupon->end_date >= now();
                $coverage = match ($coupon->applicable_to) {
                    'all' => 'كل المحتوى',
                    'courses' => 'جميع الدورات',
                    'lessons' => 'جميع الشروحات',
                    default => 'دورات وشروحات محددة',
                };
            @endphp
            <x-panel.card class="panel-course">
                <header class="panel-course-heading">
                    <span @class(['panel-status', 'panel-status--published' => $isActive])>
                        {{ $isActive ? 'نشطة' : 'غير نشطة' }}
                    </span>
                    <h2 class="panel-card-title"><bdi>{{ $coupon->code }}</bdi></h2>
                    <p>{{ $coverage }}</p>
                </header>
                <dl class="panel-course-stats panel-course-stats--pair">
                    <div><dt>الخصم</dt><dd><bdi>{{ (int) $coupon->value }}</bdi> {{ $coupon->type === 'percentage' ? '%' : 'ر.س' }}</dd></div>
                    <div><dt>مرات الاستخدام</dt><dd>{{ $coupon->used_count }}</dd></div>
                    <div><dt>تاريخ البدء</dt><dd><bdi>{{ $coupon->start_date->format('d-m-Y') }}</bdi></dd></div>
                    <div><dt>تاريخ الانتهاء</dt><dd><bdi>{{ $coupon->end_date->format('d-m-Y') }}</bdi></dd></div>
                </dl>
                <div class="panel-actions">
                    <x-panel.button data-coupon-copy="{{ $coupon->code }}" :aria-describedby="'copied-popup-'.$coupon->id">
                        نسخ الرمز
                    </x-panel.button>
                    <x-panel.button :href="route('admin.coupons.edit', $coupon->id)" variant="quiet">تعديل</x-panel.button>
                    <span class="panel-status" id="copied-popup-{{ $coupon->id }}" role="status"></span>
                </div>
            </x-panel.card>
        @empty
            <x-panel.card class="panel-grid-full">
                <x-panel.empty title="لا توجد قسائم متوفرة حاليًا" message="اضغط على زر الإضافة لإنشاء قسيمة جديدة." />
            </x-panel.card>
        @endforelse
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-coupon-copy]').forEach(button => {
            button.addEventListener('click', async () => {
                const status = document.getElementById(button.getAttribute('aria-describedby'));

                try {
                    await navigator.clipboard.writeText(button.dataset.couponCopy);
                    status.textContent = 'تم النسخ';
                } catch {
                    status.textContent = 'تعذّر النسخ';
                }
            });
        });
    </script>
@endpush
