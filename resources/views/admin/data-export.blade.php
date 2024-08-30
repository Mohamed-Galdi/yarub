@extends('layouts.admin')
@section('content')
    <div>
        <h1 class="lg:text-4xl text-2xl text-nowrap truncate text-indigo-700 mb-8">إنشاء ملف للتحميل </h1>
        <form action="{{ route('admin.data-export') }}" method="POST">
            @csrf
            <label for="data_type" class="text-gray-800 font-judur ms-3 mb-1 font-semibold">نوع البيانات </label>
            <select name="data_type" id="data_type"
                class=" block w-full h-[3.1rem] rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                required>
                <option value="" disabled selected>اختر نوع البيانات</option>
                <option value="users">المشتركين</option>
                <option value="courses_subs">مبيعات الدورات</option>
                <option value="lessons_subs">إشتراكات الشروحات</option>
                <option value="payments">المدفوعات</option>
            </select>
            @error('data_type')
                <p class="text-red-500 text-xs italic">{{ $message }}</p>
            @enderror
            <div class="flex flex-col lg:flex-row gap-8 my-6 0">
                <div class="lg:w-1/2 w-full">
                    <label for="applicable_to" class="text-gray-800 font-judur ms-3 mb-1 font-semibold"> تاريخ البداء
                    </label>
                    <x-flatpickr placeholder="إختر تاريخ" name="start_date" class="" required />
                    @error('start_date')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>
                <div class="lg:w-1/2 w-full">
                    <label for="applicable_to" class="text-gray-800 font-judur ms-3 mb-1 font-semibold"> تاريخ الانتهاء
                    </label>
                    <x-flatpickr placeholder="إختر تاريخ" name="end_date" class="" required />
                    @error('end_date')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <x-form.input-light type="text" name="file_name" label="ضع إسم للملف " placeholder="مثال: مشتركين شهر أكتوبر"
                :required="true" />
            <button type="submit"
                class="w-full flex justify-center items-center gap-2   my-4 p-3 rounded-lg text-white bg-green-500 hover:bg-green-600 transition-all duration-200 ease-in-out ">
                <p>تحميل الملف</p>
                <x-icons.file-download class="w-6 h-6 mr-2" />
            </button>
        </form>
    </div>
@endsection()
