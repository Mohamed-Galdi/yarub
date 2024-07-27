@extends('layouts.admin')

@section('content')
    <div class="container">
        <h1 class="text-4xl text-indigo-700 mb-4">تعديل شرح: <span class="text-gray-500">{{ $lesson->title }}</span></h1>
        {{-- <x-form.errors :errors="$errors" /> --}}
        <form id="lessonForm" action="{{ route('admin.lessons.update', $lesson->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            <div class="form-group flex lg:flex-row flex-col justify-start items-start gap-4">

                <x-form.input-light name="title" label="العنوان" placeholder="مثال لعنوان: مقدمة يَعرُب في التأسيس للقدرات"
                    type="text" required class="lg:w-[50%] w-full lg:order-1 order-2" id="title"
                    value="{{ $lesson->title }}" required />


                <x-form.input-light type="number" id="monthly_price" name="monthly_price" label="الإشتراك الشهري"
                    currency="ريال سعودي" value="{{ $lesson->monthly_price }}" class="lg:w-[20%] w-full lg:order-2 order-3"
                    required />
                <x-form.input-light type="number" id="annual_price" name="annual_price" label="الإشتراك السنوي"
                    currency="ريال سعودي" value="{{ $lesson->annual_price }}" class="lg:w-[20%] w-full lg:order-2 order-3"
                    required />

                <x-form.toogle label="حالة النشر" name="published" value="{{ $lesson->is_published }}"
                    class="lg:w-[10%] w-full lg:items-center items-start justify-start lg:order-3 order-1" />



            </div>
            <div class="form-group">

                <x-form.textarea-light name="description" label="الوصف" placeholder="{{ $lesson->description }}"
                    type="text" required class="w-full" id="description" />
            </div>

            <div>
                <h2 class="text-2xl text-indigo-500 mt-8 mb-2">محتوى الدورة </h2>
                <div class="w-full h-1 bg-gray-400 rounded-md mb-6"></div>
            </div>

            <div id="content-forms" class="space-y-4">
                @forelse ($lesson->content as $content)
                    <div
                        class="content-form bg-white rounded-xl border-gray-700 border-2 shadow-lg overflow-hidden pb-6 space-y-6">
                        <div class="w-full bg-gray-800 py-2">
                            <h3 class="w-full text-center text-gray-200 text-2xl">الدرس {{ $loop->iteration }}</h3>
                        </div>
                        <input type="hidden" name="content_ids[]" value="{{ $content->id }}">

                        <div class="form-group">
                            <x-form.input-light name="content_titles[]" label="عنوان الدرس" placeholder="اكتب عنوان للدرس"
                                type="text" required class="px-4 form-control content-title"
                                value="{{ $content->title }}" />
                        </div>
                        <div class="form-group space-y-3 px-6">
                            <label for="" class="text-gray-800 font-judur mb-1 font-semibold w-full text-start">
                                فيديو الدرس
                            </label>
                            <div class="flex flex-col items-center gap-3 justify-center lg:w-2/3 w-full  mx-auto ">
                                <label for="video{{ $loop->iteration }}"
                                    class="p-3 w-full bg-gray-900 text-white rounded-lg cursor-pointer hover:bg-gray-700 flex gap-2 items-center justify-center">
                                    تغيير الفيديو
                                </label>
                                <input id="video{{ $loop->iteration }}" type="file"
                                    class="form-control-file video-upload hidden" accept="video/*">
                                <input type="hidden" name="content_videos[]" value="{{ $content->url }}">
                                <div class="upload-status w-full flex justify-center items-center">
                                    <video src="{{ asset('storage/' . $content->url) }}" controls
                                        class="w-full h-full"></video>
                                </div>
                            </div>



                        </div>
                        <div class="w-full flex justify-center my-2">
                            <x-delete-confirmation url="{{ route('lessons.content.delete', $content->id) }}"
                                :params="['lesson_id' => $lesson->id]" elementName="درس" class="bg-red-500 text-white px-4 py-2 rounded">
                                حذف الدرس
                            </x-delete-confirmation>
                        </div>
                    </div>
                @empty
                    <x-card.empty-state title="لا يوجد محتوى بعد" message="اضغط على زر الإضافة لإنشاء محتوى"
                        :image="false" />
                @endempty
        </div>

        <button type="button"
            class="w-36 bg-indigo-500 text-white flex gap-2 items-center justify-center rounded-lg p-2 mt-2"
            id="add-content">
            <p>إضافة درس</p>
            <x-icons.plus class="w-4 h-4 mr-2" />
        </button>
        <button type="submit"
            class="w-full bg-blue-500 my-4 p-3 rounded-lg text-white  hover:bg-blue-700 flex gap-2 items-center justify-center"
            id="submit-form">
            <p>حفظ التغييرات</p>
            <x-icons.save class="w-6 h-6 mr-2" />
        </button>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/axios/dist/axios.min.js"></script>
<script>
    function createContentForm() {
        const formCount = document.querySelectorAll('.content-form').length;
        return `
           <div
                    class="content-form bg-white rounded-xl border-gray-700 border-2 shadow-lg overflow-hidden pb-6 space-y-6 mt-4">
                    <div class="w-full bg-gray-800  py-2 flex justify-start items-center">
                        <p class="remove-content px-3 py-1 bg-red-500 text-white text-center rounded-xl ms-5 cursor-pointer hover:bg-red-600  font-hacen">X</p>
                        <h3 class="w-full text-center text-gray-200 text-2xl">الدرس ${formCount +1}</h3>

                    </div>
                    <input type="hidden" name="content_ids[]" value="">
                    <div class="form-group">
                        
                        <x-form.input-light name="content_titles[]" label="عنوان الدرس"
                            placeholder="مقدمة يَعرُب في التأسيس للقدرات - اللفظي" type="text" required class="px-4" />
                    </div>
                    <div class="form-group px-4">
                      

                        <label for="dropzone-file-2"
                            class="text-gray-800 font-judur ms-6 mb-1 font-semibold w-full text-start"> فيديو الدرس
                        </label>
                        <label for="dropzone-file-${formCount+1}"
                            class="flex flex-col items-center justify-center w-full h-24 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50  hover:bg-gray-100 ">
                            <div class="flex items-center justify-center pt-5 pb-6 gap-4">
                                <x-icons.uplaod class="w-8 h-8 mb-4 text-indigo-500 " />
                                <p class="text-indigo-500">أنقر للتحميل </p>
                                <input id="dropzone-file-${formCount+1}" type="file" class="form-control-file video-upload hidden"
                                     accept="video/*" required>
                                <input type="hidden" name="content_videos[]">
                                <div class="upload-status text-green-500"></div>
                            </div>
                        </label>
                    </div>
        `;
    }

    document.getElementById('add-content').addEventListener('click', function() {
        const contentForms = document.getElementById('content-forms');
        contentForms.insertAdjacentHTML('beforeend', createContentForm());
    });

    document.getElementById('content-forms').addEventListener('click', function(event) {
        if (event.target.classList.contains('remove-content')) {
            event.target.closest('.content-form').remove();
        }
    });

    document.getElementById('content-forms').addEventListener('change', function(event) {
        if (event.target.classList.contains('video-upload')) {
            const file = event.target.files[0];
            const statusDiv = event.target.nextElementSibling.nextElementSibling;
            const hiddenInput = event.target.nextElementSibling;

            if (file) {
                const formData = new FormData();
                formData.append('video', file);

                statusDiv.innerHTML = 'جاري التحميل...';

                axios.post('{{ route('upload.video') }}', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    },
                    onUploadProgress: function(progressEvent) {
                        const percentCompleted = Math.round((progressEvent.loaded * 100) /
                            progressEvent.total);
                        statusDiv.innerHTML = ` جاري التحميل: ${percentCompleted}%`;
                    }
                }).then(function(response) {
                    statusDiv.innerHTML = `تم التحميل: ${file.name}`;
                    hiddenInput.value = response.data.path;
                }).catch(function(error) {
                    statusDiv.innerHTML = 'فشل التحميل. يرجى المحاولة مرة أخرى.';
                    console.error('Upload error:', error);
                });
            }
        }
    });

    document.getElementById('lessonForm').addEventListener('submit', function(event) {
        event.preventDefault();
        const videoInputs = document.querySelectorAll('input[name="content_videos[]"]');
        let allUploaded = true;
        videoInputs.forEach(input => {
            if (!input.value) {
                allUploaded = false;
            }
        });
        if (allUploaded) {
            this.submit();
        } else {
            alert('Please wait for all videos to finish uploading before submitting the form.');
        }
    });
</script>
@endpush
