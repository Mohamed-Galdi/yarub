@extends('layouts.admin')
@section('content')
    <div class="">
        {{-- <x-errors :errors="$errors" /> --}}
        <div class="flex justify-between">
            <h1 class="lg:text-4xl text-2xl text-nowrap truncate text-indigo-700 mb-4 flex gap-2 justify-start items-center">
                <p>إنشاء حصة مباشرة</p>
                <x-icons.live class="w-10 h-10 mr-2" />
            </h1>
            </h1>
            {{-- // back button --}}
            <x-btn.back route="admin.courses" />
        </div>
        <form id="courseForm" action="{{ route('admin.live-sessions.store') }}" method="POST" class="space-y-6">
            @csrf
            <div class="form-group w-full flex lg:flex-row flex-col gap-4 items-start justify-start">
                <x-form.input-light name="title" label="العنوان" placeholder="مثال لعنوان: مقدمة يَعرُب في التأسيس للقدرات"
                    type="text" required class="lg:w-1/2 w-full" id="title" required />
                <div class="lg:w-1/4 w-full">
                    <label for="type" class="text-gray-800 font-judur ms-3 mb-1 font-semibold">نوع الدورة</label>
                    <select name="type" id="type"
                        class=" block w-full  h-[3.1rem] rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        required>
                        <option value="دورة تأسيس">دورة تأسيس</option>
                        <option value="دورة تدريب">دورة تدريب</option>
                        <option value="غير مصنفة">غير مصنفة</option>
                    </select>
                </div>

                <x-form.input-light type="number" id="price" name="price" label="السعر" currency="ريال سعودي"
                    :value="30" class="lg:w-1/4 w-full" required />

            </div>
            <div class="form-group">
                <x-form.textarea-light name="description" label="الوصف" placeholder="اكتب نص يصف محتويات الدورة"
                    type="text" required class="w-full " />
            </div>
            <div>
                <h2 class="text-2xl text-indigo-500 mt-8 mb-2">جدولة البث</h2>
                <div class="w-full h-1 bg-gray-400 rounded-md mb-6"></div>
                <div class="grid lg:grid-cols-2 grid-cols-1 justify-start items-start py-2  gap-4 w-full">
                    <div class=" w-full">
                        <label for="applicable_to" class="text-gray-800 font-judur ms-3 mb-1 font-semibold"> تاريخ البداء
                        </label>
                        <x-flatpickr show-time placeholder="إختر تاريخ" name="start_date" class="rounded-lg h-12  "
                            required />
                        @error('start_date')
                            <p class="text-red-500 text-xs italic">{{ $message }}</p>
                        @enderror
                    </div>
                    <x-form.input-light maxlength="40" name="duration" label="مدة البث (بالدقائق)" placeholder="مثال: 30 " type="number"
                        class=" w-full" />
                </div>
            </div>
            <button type="submit" class="w-full flex justify-center items-center gap-4 bg-red-400 my-4 p-3 rounded-lg text-white  hover:bg-red-500 transition-all duration-200 ease-in-out "
                id="submitBtn">
                <p>إنشاء حصة مباشرة</p>
                <x-icons.live class="w-5 h-5 mr-2" />
            </button>
        </form>
    </div>
@endsection()
