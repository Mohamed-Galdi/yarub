@extends('layouts.admin')

@section('content')
    <div class="relative">
        <div class="flex justify-between">
            <h1 class="lg:text-4xl text-2xl text-nowrap truncate text-indigo-700 mb-4">إنشاء دورة جديدة</h1>
            </h1>
            {{-- // back button --}}
            <x-btn.back route="admin.courses" />
        </div>

        <form id="courseForm" action="{{ route('admin.courses.store') }}" method="POST" class="space-y-6">
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
                <h2 class="text-2xl text-indigo-500 mt-8 mb-2">محتوى الدورة </h2>
                <div class="w-full h-1 bg-gray-400 rounded-md mb-6"></div>
            </div>

            <div id="content-sections">
                <div
                    class="content-form bg-white rounded-xl border-gray-700 border-2 shadow-lg overflow-hidden pb-6 space-y-6">
                    <div class="w-full bg-gray-800 py-2">
                        <h3 class="w-full text-center text-gray-200 text-2xl">الدرس 1</h3>
                    </div>
                    <div class="form-group">
                        <x-form.input-light name="content_titles[]" label="عنوان الدرس" placeholder="اكتب عنوان للدورة"
                            type="text" required class="px-4 form-control content-title" />
                    </div>
                    <div class="form-group px-4">
                        <label for="dropzone-file"
                            class="text-gray-800 font-judur ms-6 mb-1 font-semibold w-full text-start"> فيديو الدرس
                        </label>
                        <label for="dropzone-file"
                            class="flex flex-col items-center justify-center w-full h-24 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50  hover:bg-gray-100 ">
                            <div class="flex items-center justify-center pt-5 pb-6 gap-4">
                                <x-icons.uplaod class="w-8 h-8 mb-4 text-indigo-500 " />
                                <p class="text-indigo-500">أنقر للتحميل </p>
                                <input id="dropzone-file" type="file" class="form-control-file video-upload hidden"
                                    name="contents[0][video]" accept="video/*" required>
                                <input type="hidden" name="content_videos[]">
                                <div class="upload-status text-green-500"></div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
            <button type="button"
                class="w-36 bg-indigo-400 text-white flex gap-2 items-center justify-center rounded-lg p-2 mt-2"
                id="add-content">
                <p>إضافة درس</p>
                <x-icons.plus class="w-4 h-4 mr-2" />
            </button>
            <button type="submit" class="w-full bg-green-500 my-4 p-3 rounded-lg text-white  hover:bg-green-700"
                id="submit-form">إنشاء
                الدورة</button>
        </form>
        <div id="loader" style="display: none;"
            class="absolute  w-full h-screen  flex flex-col items-center justify-center">
            <p class="text-indigo-600 text-5xl mb-2">جاري إنشاء الدورة...</p>
            <lottie-player src="https://lottie.host/25165b5d-de71-47e1-b452-1a7daf8a6aec/6b0mvv9d9K.json" class="mb-24"
                background="##ffffff" speed="1" style="width: 400px; height: 400px" loop autoplay direction="1"
                mode="normal"></lottie-player>
        </div>

    </div>


    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let contentCount = 1;
            const addContentBtn = document.getElementById('add-content');
            const contentSections = document.getElementById('content-sections');

            addContentBtn.addEventListener('click', function() {
                contentCount++;
                const newSection = document.createElement('div');
                newSection.className = 'content-section mb-4';
                newSection.innerHTML = `
            <div
                    class="content-form bg-white rounded-xl border-gray-700 border-2 shadow-lg overflow-hidden pb-6 space-y-6 mt-4">
                    <div class="w-full bg-gray-800  py-2 flex justify-start items-center">
                        <p class="remove-content px-3 py-1 bg-red-500 text-white text-center rounded-xl ms-5 cursor-pointer hover:bg-red-600  font-hacen">X</p>
                        <h3 class="w-full text-center text-gray-200 text-2xl">الدرس ${contentCount }</h3>

                    </div>
                    <div class="form-group">
                        
                        <x-form.input-light name="content_titles[]" label="عنوان الدرس"
                            placeholder="مقدمة يَعرُب في التأسيس للقدرات - اللفظي" type="text" required class="px-4" />
                    </div>
                    <div class="form-group px-4">
                      

                        <label for="dropzone-file-2"
                            class="text-gray-800 font-judur ms-6 mb-1 font-semibold w-full text-start"> فيديو الدرس
                        </label>
                        <label for="dropzone-file-${contentCount}"
                            class="flex flex-col items-center justify-center w-full h-24 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50  hover:bg-gray-100 ">
                            <div class="flex items-center justify-center pt-5 pb-6 gap-4">
                                <x-icons.uplaod class="w-8 h-8 mb-4 text-indigo-500 " />
                                <p class="text-indigo-500">أنقر للتحميل </p>
                                <input id="dropzone-file-${contentCount}" type="file" class="form-control-file video-upload hidden"
                                     accept="video/*" required>
                                <input type="hidden" name="content_videos[]">
                                <div class="upload-status text-green-500"></div>
                            </div>
                        </label>
                    </div>
        `;
                contentSections.appendChild(newSection);
            });
            document.getElementById('content-sections').addEventListener('click', function(event) {
                if (event.target.classList.contains('remove-content')) {
                    event.target.closest('.content-form').remove();
                }
            });
            document.getElementById('content-sections').addEventListener('change', function(event) {
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
                                const percentCompleted = Math.round((progressEvent.loaded *
                                        100) /
                                    progressEvent.total);
                                statusDiv.innerHTML = `جاري التحميل: ${percentCompleted}%`;
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
        });
    </script>
    <script>
        $(document).ready(function() {
            $('#courseForm').on('submit', function(e) {
                e.preventDefault();

                var formData = new FormData(this);

                $.ajax({
                    url: '{{ route('admin.courses.store') }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        $('#loader').show();
                        $('#submitBtn').prop('disabled', true);
                        $('#courseForm').hide();
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: response.message,
                                icon: 'success',
                                timer: 3000,
                                timerProgressBar: true,
                                showConfirmButton: false,
                            });
                            setTimeout(() => {
                                window.location.href = response.redirect;
                            }, 2500);
                        } else {
                            Swal.fire({
                                title: response.message,
                                icon: 'error',
                                timer: 3000,
                                timerProgressBar: true,
                                showConfirmButton: false,
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'حدث خطأ أثناء تحديث الدورة !',
                            icon: 'error',
                            timer: 3000,
                            timerProgressBar: true,
                            showConfirmButton: false,
                        });
                        console.error(xhr.responseText);
                    },
                    complete: function() {
                        $('#loader').hide();
                        $('#submitBtn').prop('disabled', false);
                    }
                });
            });
        });
    </script>
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
@endsection
