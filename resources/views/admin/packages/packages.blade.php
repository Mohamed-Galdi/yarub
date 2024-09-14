@extends('layouts.admin')
@section('content')
    <div class="flex flex-col justify-start items-start min-h-screen space-y-6">
        <div class="flex justify-between items-center gap-2">
            <x-btn.add route="admin.packages.create">إنشاء حقيبة </x-btn.add>
        </div>
        <div
            class="bg-white rounded-lg border border-gray-300 p-4 w-full grid lg:grid-cols-3 grid-cols-1 place-items-center gap-6">
            @forelse ($packages as $package)
                <x-card.admin-package :active="$package->is_active" :package_id="$package->id" :title="$package->title" :type="$package->type" :courses_count="$package->courses()->count()" :lessons_count="$package->lessons()->count()" :price="$package->price" :monthly_price="$package->monthly_price" :annual_price="$package->annual_price" />

            @empty
                <x-card.empty-state title="لم يتم العثور على أي حقيبة" message="اضغط على زر الإضافة لإنشاء حقيبة"
                    :image="true" />
            @endforelse
        </div>
    </div>
@endsection()
