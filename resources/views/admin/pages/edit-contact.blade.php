@extends('layouts.admin')
@section('panel-own-title', true)
@section('content')
    <div class="flex justify-between">
        <h1 class="panel-page-title">تعديل صفحة تواصل معنا</h1>
        {{-- // back button --}}
        <x-btn.back route="admin.pages" />
    </div>
    <div class="mt-8">
        <div class="flex justify-start items-center gap-4">
            <h2 class="panel-section-title">الفقرة الرئيسية</h2>
            <div class="flex-1 border-b border-gray-200"></div>
        </div>
        <form action="{{ route('admin.pages.update-contact') }}" class="panel-surface mt-4 space-y-4" method="post">
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
                class="panel-button panel-button--primary w-full flex justify-center items-center gap-2 px-4 py-2 rounded-md">
                <x-icons.save class="w-4 h-4  scale-x-[-1] " />
                <p> حفظ</p>
            </button>
        </form>
    </div>
@endsection
