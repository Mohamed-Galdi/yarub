@extends('layouts.admin')
@section('content')
    <h1 class="text-4xl font-bold text-gradient-to-r  p-2 w-fit text-indigo-500 ">حسابات المشرفين</h1>
    <div class="mt-6">
        <div class="p-2 ms-2 flex gap-2 items-center">
            <x-icons.star class="w-4 h-4 text-yellow-500" />
            <h2>الحساب الرئيسي</h2>
        </div>
        <form action="{{ route('admin.account.update-main') }}" method="POST" class="w-full  rounded-2xl bg-white shadow-xl px-4 pb-6 pt-4 " >
            @csrf
            <div class="flex justify-start items-start py-2  gap-4">
                <x-form.input-light type="text" name="name" label="الاسم" placeholder="الاسم" class="w-full"
                    value="{{ $superAdmin->name }}" />
                <x-form.input-light type="text" name="email" label="البريد الإلكتروني" placeholder="البريد الإلكتروني"
                    class="w-full" value="{{ $superAdmin->email }}" />
            </div>
            <button type="submit"
                class="w-full text-center mt-3 text-white bg-indigo-400 hover:bg-indigo-500 rounded-2xl py-2 px-4 hover:font-bold transition-all duration-300 ease-in-out">تحديث
                الحساب</button>
        </form>
        <div>
            <button data-modal-target="pw-modal" data-modal-toggle="pw-modal"
                class="w-fit text-white bg-red-400 hover:bg-red-500 p-2 rounded-lg mt-4 me-2 flex justify-center items-center gap-2">
                <x-icons.key class="w-4 h-4 text-white" />
                <p>تغيير كلمة المرور</p>
            </button>
        </div>
    </div>
    <div class="w-full h-[2px] rounded-lg bg-gray-600 mt-8"></div>
    <div>
        <button data-modal-target="new-admin-modal" data-modal-toggle="new-admin-modal"
            class="w-fit text-white bg-blue-400 hover:bg-blue-500 p-2 rounded-lg mt-4 me-2 flex justify-center items-center gap-2">
            <x-icons.admin class="w-4 h-4 text-white" />
            <p> إضافة مشرف </p>
        </button>
        <div class="mt-8">
            @forelse($admins as $admin)
                <form class="w-full  rounded-2xl bg-slate-300 shadow-xl px-4 pb-6 pt-4 border-2 border-blue-500">
                    <div class="w-full flex justify-end items-center">
                        <x-delete-confirmation url="{{ route('admin.account.delete-admin', $admin->id) }}"
                                :params="['admin_id' => $admin->id]" elementName="المشرف" class="bg-red-400 text-white px-4 py-2 rounded-lg hover:bg-red-500">
                                حذف المشرف
                            </x-delete-confirmation>

                    </div>
                    <div class="flex justify-start items-start py-2  gap-4">
                        <x-form.input-light type="text" name="name" label="الاسم" placeholder="الاسم" class="w-full"
                            value="{{ $admin->name }}" />
                        <x-form.input-light type="text" name="email" label="البريد الإلكتروني"
                            placeholder="البريد الإلكتروني" class="w-full" value="{{ $admin->email }}" />
                    </div>
                    <button type="submit"
                        class="w-full text-center mt-3 text-white bg-indigo-400 hover:bg-indigo-500 rounded-2xl py-2 px-4 hover:font-bold transition-all duration-300 ease-in-out">تحديث
                        الحساب</button>
                </form>
            @empty
                <p class="text-center text-gray-500 text-sm">لا يوجد مشرفين إضافيين حالياً</p>
            @endforelse
        </div>
    </div>




    <!-- Update main account password modal -->
    <div id="pw-modal" tabindex="-1" aria-hidden="true"
        class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 max-h-full bg-gray-800/70">
        <div class="relative p-4 w-full max-w-md max-h-full">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow  ">
                <!-- Modal header -->
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t ">
                    <h3 class="text-lg font-semibold text-gray-900 ">
                        تغيير كلمة المرور
                    </h3>
                    <button type="button"
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center "
                        data-modal-toggle="pw-modal">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <!-- Modal body -->
                <form action="{{ route('admin.account.update-main-password') }}" method="POST" class="flex flex-col gap-4 p-4 md:p-5">
                    @csrf
                        <x-form.input-light type="text" name="old_password" label="" placeholder="الكلمة السابقة"
                            class="w-[90%]" value="{{ old('password') }}" />
                        <x-form.input-light type="text" name="new_password" label=""
                            placeholder="كلمة المرور الجديدة" class="w-[90%]" value="{{ old('password') }}" />
                        <x-form.input-light type="text" name="confirm_password" label=""
                            placeholder="تأكيد كلمة المرور الجديدة" class="w-[90%]" value="{{ old('password') }}" />


                    <button type="submit"
                        class="w-[90%] text-white bg-red-400 hover:bg-red-500 p-2 rounded-lg mt-4 me-2 flex justify-center items-center gap-2">
                        <x-icons.key class="w-4 h-4 text-white" />
                        <p>تغيير كلمة المرور</p>
                    </button>
                </form>
            </div>
        </div>
    </div>
    <!-- Add new admin modal -->
    <div id="new-admin-modal" tabindex="-1" aria-hidden="true"
        class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 max-h-full bg-gray-800/70">
        <div class="relative p-4 w-full max-w-md max-h-full">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow  ">
                <!-- Modal header -->
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t ">
                    <h3 class="text-lg font-semibold text-gray-900 ">
                        إضافة مشرف جديد
                    </h3>
                    <button type="button"
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center "
                        data-modal-toggle="new-admin-modal">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <!-- Modal body -->
                <form action="{{ route('admin.account.create') }}" method="POST" class="flex flex-col gap-4 p-4 md:p-5">
                    @csrf
                        <x-form.input-light type="text" name="name" label="" placeholder=" الاسم"
                            class="w-[90%]" value="{{ old('name') }}" />
                        <x-form.input-light type="text" name="email" label=""
                            placeholder="البريد الإلكتروني" class="w-[90%]" value="{{ old('email') }}" />
                        <x-form.input-light type="password" name="password" label=""
                            placeholder=" كلمة المرور " class="w-[90%]" value="{{ old('password') }}" />


                    <button type="submit"
                        class="w-[90%] text-white bg-blue-400 hover:bg-blue-500 p-2 rounded-lg mt-4 me-2 flex justify-center items-center gap-2">
                        <x-icons.admin class="w-4 h-4 text-white" />
                        <p> إضافة مشرف </p>
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
