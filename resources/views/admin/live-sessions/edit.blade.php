@extends('layouts.admin')
@section('content')
    <div class="container">
        <div class="flex justify-between">
            <h1 class="lg:text-4xl text-2xl text-nowrap truncate text-indigo-700 mb-4">تعديل دورة: <span
                    class="text-gray-500">{{ $course->title }}</span></h1>
            </h1>
            {{-- // back button --}}
            <x-btn.back route="admin.courses" />
        </div>
        {{-- Course Form --}}
        <form action="{{ route('admin.live-session.updateCourse', $course->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group flex lg:flex-row flex-col justify-start items-start gap-4 ">

                <x-form.input-light name="title" label="العنوان" placeholder="مثال لعنوان: مقدمة يَعرُب في التأسيس للقدرات"
                    type="text" required class="lg:w-3/5 w-full lg:order-1 order-2" id="title"
                    value="{{ $course->title }}" required />
                <div class="lg:w-1/4 w-full lg:order-2 order-3">
                    <label for="type" class="text-gray-800 font-judur ms-3 mb-1 font-semibold">نوع الدورة</label>
                    <select name="type" id="type"
                        class=" block w-full  h-[3.1rem] rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        required>
                        <option value="دورة تأسيس" {{ $course->type === 'دورة تأسيس' ? 'selected' : '' }}>دورة تأسيس
                        </option>
                        <option value="دورة تدريب" {{ $course->type === 'دورة تدريب' ? 'selected' : '' }}>دورة تدريب
                        </option>
                        <option value="غير مصنفة" {{ $course->type === 'غير مصنفة' ? 'selected' : '' }}>غير مصنفة</option>
                    </select>
                </div>


                <x-form.input-light type="number" id="price" name="price" label="السعر " currency="ريال سعودي"
                    value="{{ $course->price }}" class="lg:w-1/6 w-full lg:order-2 order-3" required />

                <x-form.toogle label="حالة النشر" name="published" value="{{ $course->is_published }}"
                    class="lg:w-1/5 w-full lg:items-center items-start justify-start lg:order-3 order-1" />



            </div>
            <div class="form-group">

                <x-form.textarea-light name="description" label="الوصف" placeholder="{{ $course->description }}"
                    type="text" required class="w-full" id="description" />
            </div>
            <button type="submit"
                class="w-full bg-blue-500 my-4 p-3 rounded-lg text-white  hover:bg-blue-700 flex gap-2 items-center justify-center"
                id="submitBtn">
                <p>حفظ التغييرات</p>
                <x-icons.save class="w-6 h-6 mr-2" />
            </button>
        </form>

        {{-- Live Session Form --}}
        <div>
            <div>
                <h2 class="text-2xl text-indigo-500 mt-8 mb-2">جدولة البث</h2>
                <div class="w-full h-1 bg-gray-400 rounded-md mb-6"></div>
                <form action="{{ route('admin.live-session.updateLiveSession', $course->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="grid lg:grid-cols-2 grid-cols-1 justify-start items-start py-2  gap-4 w-full">
                        <div class=" w-full">
                            <label for="applicable_to" class="text-gray-800 font-judur ms-3 mb-1 font-semibold"> تاريخ
                                البداء
                            </label>
                            <x-flatpickr show-time placeholder="إختر تاريخ" name="start_date" class="rounded-lg h-12  "
                                required value="{{ \Carbon\Carbon::parse($course->liveSession->start_time)->format('Y-m-d H:i') }}" />
                            @error('start_date')
                                <p class="text-red-500 text-xs italic">{{ $message }}</p>
                            @enderror
                        </div>
                        <x-form.input-light maxlength="40" name="duration" label="مدة البث (بالدقائق)"
                            placeholder="مثال: 30 " type="number" class=" w-full" value="{{ $course->liveSession->duration }}" />
                    </div>
                    <button type="submit"
                        class="w-full flex justify-center items-end gap-4 bg-red-400 my-4 p-3 rounded-lg text-white  hover:bg-red-500 transition-all duration-200 ease-in-out "
                        id="submitBtn">
                        <p>تحديث جدولة البث  </p>
                        <x-icons.live class="w-5 h-5 mr-2" />
                    </button>
                </form>
            </div>

        </div>
    </div>
@endsection()
