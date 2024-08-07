@extends('layouts.admin')
@section('content')
    <div class="flex justify-between">
        <h1 class="lg:text-4xl text-2xl text-nowrap truncate text-indigo-700 mb-4">تعديل صفحة من نحن</h1>
        </h1>
        {{-- // back button --}}
        <x-btn.back route="admin.pages" />
    </div>
    <div class="mt-8">
        <div class="flex justify-start items-center gap-4">
            <h2 class="w-fit text-nowrap text-warning-500 text-2xl ">الفقرة الرئيسية</h2>
            <div class="w-full h-1 bg-warning-500"></div>
        </div>
        <form action="{{ route('admin.pages.update-about') }}" class="mt-4 space-y-2" method="post">
            @csrf
            <div class="flex md:flex-row flex-col justify-start items-center gap-4 w-full">
                <x-form.textarea-light class="md:w-1/2 w-full " label="فريقنا" name="our_team_content"
                    placeholder="{{ $aboutPage->our_team_content }}" required />
                <x-form.textarea-light class="md:w-1/2 w-full " label="هدفنا" name="our_goal_content"
                    placeholder="{{ $aboutPage->our_goal_content }}" required />
            </div>
            <button type="submit"
                class="w-1/5 flex justify-center items-center gap-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md ">
                <x-icons.save class="w-4 h-4  scale-x-[-1] " />
                <p> حفظ</p>
            </button>
        </form>
    </div>
    <div class="mt-8">
        <div class="flex justify-start items-center gap-4">
            <h2 class="w-fit text-nowrap text-warning-500 text-2xl ">شركاء النجاح</h2>
            <div class="w-full h-1 bg-warning-500"></div>
        </div>
        <button data-modal-target="partners-modal" data-modal-toggle="partners-modal"
            class="w-fit text-white bg-green-400 hover:bg-green-500 p-2 rounded-lg mt-4 me-2 flex justify-center items-center gap-2">
            <p>إضافة شريك</p>
        </button>
        <div
            class="grid lg:grid-cols-3 md:grid-cols-2 grid-cols-1  mt-8 w-full place-items-center gap-12 text-2xl text-nowrap">
            @foreach ($partners as $partner)
                <form action="{{ route('admin.pages.update-partner', $partner->id) }}" method="POST"
                    class="w-full px-8 space-y-2 bg-slate-300 pt-2 pb-4 rounded-lg border-2 border-white ps-3 ">
                    @csrf
                    @method('PUT')
                    <div class="w-full flex justify-end items-center">
                        <x-delete-confirmation url="{{ route('admin.pages.delete-partner', $partner->id) }}"
                            :params="['partner_id' => $partner->id]" elementName="الشريك"
                            class="bg-red-500 text-white w-10 h-6 flex justify-center items-center text-sm rounded-lg hover:bg-red-600">
                            <x-icons.trash class="w-4 h-4  scale-x-[-1] " />
                        </x-delete-confirmation>

                    </div>
                    <x-form.input-light type="text" name="name" label="الاسم" placeholder="الاسم"
                        class="w-full flex flex-col ms-3" value="{{ $partner->name }}" />


                    <x-form.input-light type="text" name="url" label="الرابط" placeholder="الرابط"
                        class="w-full flex flex-col ms-3" value="{{ $partner->url }}" />
                    <button
                        class="w-full h-8 ms-3 flex justify-center items-center gap-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-1 rounded-md ">
                        <x-icons.save class="w-4 h-4  scale-x-[-1] " />
                        <p> حفظ</p>
                    </button>
                </form>
            @endforeach

        </div>

    </div>
    <div class="mt-8">
        <div class="flex justify-start items-center gap-4">
            <h2 class="w-fit text-nowrap text-warning-500 text-2xl "> الأسئلة المتكررة</h2>
            <div class="w-full h-1 bg-warning-500"></div>
        </div>
        <button data-modal-target="faqs-modal" data-modal-toggle="faqs-modal"
            class="w-fit text-white bg-green-400 hover:bg-green-500 p-2 rounded-lg mt-4 me-2 flex justify-center items-center gap-2">
            <p>إضافة سؤال</p>
        </button>
        <div class="flex flex-col  mt-8 w-full place-items-center gap-12 text-2xl text-nowrap">
            @foreach ($faqs as $faq)
                <form action="{{ route('admin.pages.update-faq', $faq->id) }}" method="POST"
                    class="w-full px-8 space-y-2 bg-slate-300 pt-2 pb-4 rounded-lg border-2 border-white ps-3 ">
                    @csrf
                    @method('PUT')
                    <div class="w-full flex justify-end items-center">
                        <x-delete-confirmation url="{{ route('admin.pages.delete-faq', $faq->id) }}" :params="['faq_id' => $faq->id]"
                            elementName="السؤال"
                            class="bg-red-500 text-white w-10 h-6 flex justify-center items-center text-sm rounded-lg hover:bg-red-600">
                            <x-icons.trash class="w-4 h-4  scale-x-[-1] " />
                        </x-delete-confirmation>
                    </div>
                    <x-form.input-light type="text" name="question" label="السؤال {{ $loop->index + 1}} " placeholder="السؤال"
                        class="w-full flex flex-col ms-3" value="{{ $faq->question }}" />
                    <x-form.textarea-light class="w-full" name="answer" label="" placeholder="{{ $faq->answer }}"
                        required />
                    <button
                        class="w-full h-8 ms-3 flex justify-center items-center gap-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-1 rounded-md ">
                        <x-icons.save class="w-4 h-4  scale-x-[-1] " />
                        <p> حفظ</p>
                    </button>
                </form>
            @endforeach

        </div>

    </div>

    {{-- add partner modal --}}
    <div id="partners-modal" tabindex="-1" aria-hidden="true"
        class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 max-h-full bg-gray-800/70">
        <div class="relative p-4 w-full max-w-md max-h-full">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow  ">
                <!-- Modal header -->
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t ">
                    <h3 class="text-lg font-semibold text-gray-900 ">
                        إضافة شريك
                    </h3>
                    <button type="button"
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center "
                        data-modal-toggle="partners-modal">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <!-- Modal body -->
                <form action="{{ route('admin.pages.add-partner') }}" method="POST"
                    class="flex flex-col gap-4 p-4 md:p-5">
                    @csrf
                    <x-form.input-light type="text" name="name" label="" placeholder="الاسم"
                        class="w-[90%]" />
                    <x-form.input-light type="text" name="url" label="" placeholder="الرابط"
                        class="w-[90%]" />
                    <button type="submit"
                        class="w-[90%] text-white bg-green-400 hover:bg-green-500 p-2 rounded-lg mt-4 me-2 flex justify-center items-center gap-2">
                        <p>إضافة شريك</p>
                    </button>
                </form>
            </div>
        </div>
    </div>
    {{-- add faq modal --}}
    <div id="faqs-modal" tabindex="-1" aria-hidden="true"
        class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 max-h-full bg-gray-800/70">
        <div class="relative p-4 w-full max-w-md max-h-full">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow  ">
                <!-- Modal header -->
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t ">
                    <h3 class="text-lg font-semibold text-gray-900 ">
                        إضافة سؤال
                    </h3>
                    <button type="button"
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center "
                        data-modal-toggle="faqs-modal">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <!-- Modal body -->
                <form action="{{ route('admin.pages.add-faq') }}" method="POST"
                    class="flex flex-col gap-4 p-4 md:p-5">
                    @csrf
                    <x-form.input-light type="text" name="question" label="" placeholder="السؤال"   class="w-[90%]" />
                    <x-form.textarea-light class="w-[90%]" name="answer" label="" placeholder="الإجابة" required />
                    <button type="submit"
                        class="w-[90%] text-white bg-green-400 hover:bg-green-500 p-2 rounded-lg mt-4 me-2 flex justify-center items-center gap-2">
                        <p>إضافة سؤال</p>
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
