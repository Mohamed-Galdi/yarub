@extends('layouts.admin')
@section('content')
    <div class="flex justify-between">
        <h1 class="lg:text-4xl text-2xl text-nowrap truncate text-indigo-700 mb-4">تعديل صفحة تواصل معنا</h1>
        </h1>
        {{-- // back button --}}
        <x-btn.back route="admin.pages" />
    </div>
    <div class="mt-8">
        <div class="flex justify-start items-center gap-4">
            <h2 class="w-fit text-nowrap text-warning-500 text-2xl ">الفقرة الرئيسية</h2>
            <div class="w-full h-1 bg-warning-500"></div>
        </div>
        <form action="{{ route('admin.pages.update-contact') }}" class="mt-4 space-y-2" method="post">
            @csrf
            <div class="flex lg:flex-row flex-col  justify-start items-center gap-4 w-full">
                <x-form.input-light type="text" name="commercial_registration_no" label="رقم السجل التجاري" placeholder="رقم التسوية التجارية"
                    class="lg:w-1/2 w-full " value="{{ $contactPage->commercial_registration_no }}" />
                <x-form.input-light type="text" name="phone_number" label="رقم الهاتف" placeholder="رقم الهاتف"
                    class="lg:w-1/2 w-full " value="{{ $contactPage->phone_number }}" />
            </div>
            <div class="flex lg:flex-row flex-col justify-start items-center gap-4 w-full">
                <x-form.input-light type="text" name="email" label="البريد الإلكتروني" placeholder="البريد الإلكتروني"
                    class="lg:w-1/2 w-full" value="{{ $contactPage->email }}" />
                <x-form.input-light type="text" name="address" label="العنوان" placeholder="العنوان"
                    class="lg:w-1/2 w-full" value="{{ $contactPage->address }}" />
            </div>
            <div class="flex lg:flex-row flex-col justify-start items-center gap-4 w-full">   
                <x-form.input-light type="text" name="whatsapp_number" label="الواتساب" placeholder="رقم الهاتف الموبايل"
                    class="lg:w-1/2 w-full" value="{{ $contactPage->whatsapp_number }}" />
                <x-form.input-light type="text" name="instagram" label="الانستغرام" placeholder="الانستغرام"
                    class="lg:w-1/2 w-full" value="{{ $contactPage->instagram }}" />
            </div>
            <div class="flex lg:flex-row flex-col justify-start items-center gap-4 w-full">
                <x-form.input-light type="text" name="tiktok" label="تيكتوك" placeholder="تيكتوك"
                    class="lg:w-1/2 w-full" value="{{ $contactPage->tiktok }}" />
                <x-form.input-light type="text" name="snapchat" label="سنابشات" placeholder="سنابشات"
                    class="lg:w-1/2 w-full" value="{{ $contactPage->snapchat }}" />
            </div>
            <button type="submit"
                class="w-full flex justify-center items-center gap-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md ">
                <x-icons.save class="w-4 h-4  scale-x-[-1] " />
                <p> حفظ</p>
            </button>
        </form>
    </div>
@endsection
