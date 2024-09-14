@extends('layouts.guest')
@section('content')
    <div class="mx-auto max-w-screen-xl py-6 px-2 space-y-12 text-primary pb-12 min-h-screen">
        {{-- ////////////////////////////// Title ////////////////////////////// --}}
        <h1 class="text-center lg:text-7xl md:text-5xl text-4xl ">حقائب منصة يعرب</h1>

        {{-- ////////////////////////////// Search form ////////////////////////////// --}}
        <div class="space-y-2">
            <form method="GET" action="{{ route('packages') }}"
                class="mx-8 md:mx-4 lg:mx-3 flex justify-start items-center gap-2 ">
                <div class="relative w-full flex items-center justify-start rounded-md">
                    <x-icons.search class="absolute right-2 block h-5 w-5 text-gray-400" />
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="h-12 w-full cursor-text rounded-md border-2 border-slate-300 focus:border-slate-500 bg-gray-100 py-4 pr-8 pl-12 shadow-sm outline-none focus:ring-0 focus:ring-transparent "
                        placeholder="إبحث عن الحقيبة التي تريد ..." />
                </div>
                <div class="">
                    <x-btn.scale-light type="submit" class="w-24">بحث</x-btn.scale-light>
                </div>
            </form>
            <div class="md:w-1/4 w-full pe-16 md:pe-0   mx-8 md:mx-4 lg:mx-3">
                @php
                    // Mapping of types to Arabic labels
                    $typeLabels = [
                        'courses' => 'حقيبة دورات',
                        'lessons' => 'حقيبة الشروحات',
                        'mixed' => 'حقيبة مختلطة',
                    ];
                @endphp
                <select name="type" id="type"
                    class="block w-full rounded-md border-2 border-slate-300 focus:border-slate-500 bg-slate-300 shadow-sm outline-none focus:ring-0 focus:ring-transparent"
                    onchange="this.form.submit()">
                    <option value="الكل" {{ $selectedType === 'الكل' ? 'selected' : '' }}>الكل</option>
                    @foreach ($availableTypes as $type)
                        <option value="{{ $type }}" {{ $selectedType === $type ? 'selected' : '' }}>
                            {{ $typeLabels[$type] ?? $type }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{--  Courses list  --}}
        <div id="packagesList" class="grid grid-cols-1  md:grid-cols-2 lg:grid-cols-3  gap-12  px-8 place-items-center">
            @forelse ($packages as $package)
                <x-card.guest-package class="w-[22rem] " data-type="{{ $package->type }}" :id="$package->id" 
                    :title="$package->title" :description="$package->description" :price="$package->price" :monthly-price="$package->monthly_price" :annual-price="$package->annual_price" :type="$typeLabels[$package->type]" :type-value="$package->type" :courses_count="$package->courses()->count()" :lessons_count="$package->lessons()->count()" />
            @empty
                <div class="col-span-3 flex flex-col justify-center items-center w-full ">
                    <p class="text-2xl">لا توجد نتائج مطابقة حاليا</p>
                    <img class="w-72" src="{{ asset('assets/images/empty.svg') }}" alt="صورة فارغة" />
                </div>
            @endforelse
        </div>

        <div class="flex justify-center">
            {{ $packages->links() }}
        </div>
    </div>
@endsection()

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const typeSelect = document.getElementById('type');
            const packagesList = document.getElementById('packagesList');
            const packages = packagesList.children;

            typeSelect.addEventListener('change', function() {
                console.log('first')
                const selectedType = this.value;

                for (let package of packages) {
                    const packageType = package.getAttribute('data-type');
                    if (selectedType === 'الكل' || packageType === selectedType) {
                        package.style.display = '';
                    } else {
                        package.style.display = 'none';
                    }
                }
            });
        });
    </script>
@endpush
