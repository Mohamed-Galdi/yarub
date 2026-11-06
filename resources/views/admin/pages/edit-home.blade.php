@extends('layouts.admin')
@section('panel-own-title', true)
@section('content')
    <div class="flex justify-between">
        <h1 class="panel-page-title">تعديل الصفحة الرئيسية</h1>
        {{-- // back button --}}
        <x-btn.back route="admin.pages" />
    </div>
    <div class="mt-8">
        <div class="flex justify-start items-center gap-4">
            <h2 class="panel-section-title">الفقرة الرئيسية</h2>
            <div class="flex-1 border-b border-gray-200"></div>
        </div>
        <form action="{{ route('admin.pages.update-home') }}" class="panel-surface mt-4 space-y-4" method="post">
            @csrf
            <input type="hidden" name="form" value="header">
            <div>
                <x-form.input-light type="text" name="main_title" label="العنوان الرئيسي"
                    placeholder="العنوان الرئيسي للصفحة" value="{{ $homePage->main_title }}" required />
            </div>
            <div>
                <x-form.textarea-light name="sub_title" label="العنوان الثانوي" placeholder="{{ $homePage->sub_title }}"
                    required />
            </div>
            <button type="submit"
                class="panel-button panel-button--primary flex justify-center items-center gap-2 px-4 py-2 rounded-md">
                <x-icons.save class="w-4 h-4  scale-x-[-1] " />
                <p> حفظ</p>
            </button>
        </form>
    </div>
    <div class="mt-12">
        <div class="flex justify-start items-center gap-4">
            <h2 class="panel-section-title"> فقرة الميزات</h2>
            <div class="flex-1 border-b border-gray-200"></div>
        </div>
        <form action="{{ route('admin.pages.update-home') }}" class="panel-surface mt-4 space-y-6" method="post">
            @csrf
            <input type="hidden" name="form" value="features">
            <div class="w-full  ">
                <x-form.input-light class="lg:w-1/3 md:w-1/2 w-full mx-auto" type="text" name="our_features_title"
                    label="عنوان الفقرة" placeholder="العنوان الرئيسي للصفحة" value="{{ $homePage->our_features_title }}"
                    required />
            </div>
            <div class="grid lg:grid-cols-3 md:grid-cols-2 grid-cols-1 gap-4">
                <div class="space-y-2">
                    <x-form.input-light class="lg:w-5/6 w-[95%] ms-3" type="text" name="first_feature_title"
                        label="الميزة الاولى" placeholder="العنوان الرئيسي للصفحة"
                        value="{{ $homePage->first_feature_title }}" required />
                    <x-form.textarea-light class="lg:w-5/6 w-[95%]" name="first_feature_content" label=""
                        placeholder="{{ $homePage->first_feature_content }}" required />
                </div>
                <div class="space-y-2">
                    <x-form.input-light class="lg:w-5/6 w-[95%] ms-3" type="text" name="second_feature_title"
                        label="الميزة الثانية" placeholder="العنوان الرئيسي للصفحة"
                        value="{{ $homePage->second_feature_title }}" required />
                    <x-form.textarea-light class="lg:w-5/6 w-[95%]" name="second_feature_content" label=""
                        placeholder="{{ $homePage->second_feature_content }}" required />
                </div>
                <div class="space-y-2">
                    <x-form.input-light class="lg:w-5/6 w-[95%] ms-3" type="text" name="third_feature_title"
                        label="الميزة الثالثة" placeholder="العنوان الرئيسي للصفحة"
                        value="{{ $homePage->third_feature_title }}" required />
                    <x-form.textarea-light class="lg:w-5/6 w-[95%]" name="third_feature_content" label=""
                        placeholder="{{ $homePage->third_feature_content }}" required />
                </div>

            </div>
            <button type="submit"
                class="panel-button panel-button--primary flex justify-center items-center gap-2 px-4 py-2 rounded-md">
                <x-icons.save class="w-4 h-4  scale-x-[-1] " />
                <p> حفظ</p>
            </button>
        </form>
    </div>
    <div class="mt-12">
        <div class="flex justify-start items-center gap-4">
            <h2 class="panel-section-title"> فقرة التقييمات</h2>
            <div class="flex-1 border-b border-gray-200"></div>
        </div>

        <div class="w-full grid lg:grid-cols-3 grid-cols-1 gap-8 place-items-center mt-4">
            @foreach ($homePageReviews as $review)
                <form action="{{ route('admin.pages.update-review', $review->id) }}" enctype="multipart/form-data"
                    class="panel-surface space-y-4 w-full" method="post">
                    @csrf
                    <input type="hidden" name="form" value="reviews">
                    <x-form.input-light class="w-full" type="text" name="reviewer_name" label=" "
                        value="{{ $review->reviewer_name }}" required />
                    <x-form.textarea-light class="w-full" name="review_content" label=""
                        placeholder="{{ $review->review }}" required />
                    <x-form.input-light class="w-full ms-3" type="number" name="stars" label=""
                        value="{{ $review->stars }}" required />
                    <div class="w-full flex justify-start items-center gap-4">
                        <div class="w-1/4 flex justify-center">
                            <img id="reviewerImage{{ $review->id }}" src="{{ asset($review->reviewer_image) }}"
                                alt="review image" class="w-1/2 h-1/2 object-cover rounded-full" />
                        </div>
                        <label for="reviewerImageInput{{ $review->id }}"
                            class="panel-button panel-button--secondary">
                            صورة المستخدم
                            <x-icons.uplaod class="w-4 h-4 text-slate-200 " />
                        </label>
                        <input id="reviewerImageInput{{ $review->id }}" type="file" name="reviewer_image"
                            class="hidden reviewerImageInput" accept="image/*" data-review-id="{{ $review->id }}">
                    </div>
                    <button
                        class="panel-button panel-button--secondary w-full flex justify-center items-center gap-2 px-4 py-2 rounded-md">
                        <x-icons.save class="w-4 h-4  scale-x-[-1] " />
                        <p> حفظ</p>
                    </button>
                </form>
            @endforeach
        </div>
    </div>
    <div class="mt-12">
        <div class="flex justify-start items-center gap-4">
            <h2 class="panel-section-title">الفقرة الاخيرة</h2>
            <div class="flex-1 border-b border-gray-200"></div>
        </div>
        <form action="{{ route('admin.pages.update-home') }}" class="panel-surface mt-4 space-y-4" method="post">
            @csrf
            <input type="hidden" name="form" value="footer">
            <div>
                <x-form.input-light type="text" name="last_section_title" label="عنوان الفقرة الاخيرة"
                    placeholder="العنوان الرئيسي للصفحة" value="{{ $homePage->last_section_title }}" required />
            </div>
            <div>
                <x-form.textarea-light name="last_section_content" label="نص الفقرة الاخيرة"
                    placeholder="{{ $homePage->last_section_content }}" required />
            </div>
            <button type="submit"
                class="panel-button panel-button--primary flex justify-center items-center gap-2 px-4 py-2 rounded-md">
                <x-icons.save class="w-4 h-4  scale-x-[-1] " />
                <p> حفظ</p>
            </button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const imageInputs = document.querySelectorAll('.reviewerImageInput');

            imageInputs.forEach(function(input) {
                input.addEventListener('change', function(event) {
                    const reviewId = this.getAttribute('data-review-id');
                    const imagePreview = document.getElementById(`reviewerImage${reviewId}`);
                    const file = event.target.files[0];

                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            imagePreview.src = e.target.result;
                        }
                        reader.readAsDataURL(file);
                    }
                });
            });
        });
    </script>
@endsection
