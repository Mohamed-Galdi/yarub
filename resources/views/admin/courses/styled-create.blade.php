@extends('layouts.admin')

@section('content')
    <div class="">
        <h1 class="text-4xl text-indigo-700 mb-4">إنشاء درس جديد</h1>
        <div class="">
            <form action="{{ route('admin.courses.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                {{-- ///////////////////// Course Info ///////////////////// --}}
                <div class="space-y-6">
                    <div class="flex gap-4 ">
                        <x-form.input-light name="title" label="العنوان"
                            placeholder="مقدمة يَعرُب في التأسيس للقدرات - اللفظي" type="text" required class="w-3/4" />
                        <x-form.input-light name="price" label="السعر (ريال سعودي)" placeholder="30 " type="number"
                            required class="w-1/4" />
                    </div>
                    <x-form.textarea-light name="description" label="الوصف"
                        placeholder="دورة تفصيلية وتعريفية باختبارات القدرات حسب اشتراطات قياس " type="text" required
                        class="w-full " />
                </div>

                {{-- ///////////////////// Course Contents ///////////////////// --}}

                <h2 class="text-2xl text-indigo-500 mt-8 mb-2">محتوى الدورة </h2>
                <div class="w-full h-1 bg-gray-400 rounded-md mb-6"></div>

                <div id="content-forms" class="space-y-6">
                    <div
                        class="content-form bg-white rounded-xl border-gray-700 border-2 shadow-lg overflow-hidden pb-6 space-y-6">
                        <div class="w-full bg-gray-800 py-2">
                            <h3 class="w-full text-center text-gray-200 text-2xl">الدرس 1</h3>
                        </div>
                        <div class="form-group" class="px-4">
                            <x-form.input-light name="contents[0][title]" label="عنوان المحتوى"
                                placeholder="مقدمة يَعرُب في التأسيس للقدرات - اللفظي" type="text" required
                                class="px-4" />
                        </div>
                        <div class="flex flex-col items-center justify-center w-full px-4">
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
                                    <div class="upload-status text-green-500"></div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
                <button type="button"
                    class="w-36 bg-indigo-500 text-white flex gap-2 items-center justify-center rounded-lg p-2 mt-2"
                    id="add-content">
                    <p>إضافة درس</p>
                    <x-icons.plus class="w-4 h-4 mr-2" />
                </button>
                <button type="submit" class="w-full bg-green-500 my-4 p-3 rounded-lg text-white  hover:bg-green-700">إنشاء
                    الدرس</button>

            </form>

        </div>
    </div>
@endsection

@push('scripts')
    {{-- /////////////////// Form Repeater /////////////////// --}}
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    <script>
        let contentIndex = 0;

        function createContentForm() {
            contentIndex++;
            return `
           <div
                        class="content-form bg-white rounded-xl border-gray-700 border-2 shadow-lg overflow-hidden pb-6 space-y-6">
                        <div class="w-full bg-gray-800 py-2 flex justify-start items-center">
                            <button type="button" class="w-[47%]   flex justify-start  ">
                                <div class="bg-red-400 text-white rounded-lg p-3 hover:bg-red-500 ms-4 remove-content" data-id="${contentIndex}">
                                    <x-icons.trash class="w-4 h-4 " />
                                    </div>
                            </button>
                            <h3 class="w-[53%] text-start text-gray-200 text-2xl ">الدرس  ${contentIndex +1}</h3>

                        </div>
                        <div class="form-group" class="px-4">
                            <x-form.input-light name="contents[0][title]" label="عنوان المحتوى"
                                placeholder="مقدمة يَعرُب في التأسيس للقدرات - اللفظي" type="text" required
                                class="px-4" />
                        </div>
                        <div class="flex flex-col items-center justify-center w-full px-4">
                            <label for="dropzone-file"
                                class="text-gray-800 font-judur ms-6 mb-1 font-semibold w-full text-start"> فيديو الدرس
                            </label>
                            <label for="dropzone-file"
                                class="flex flex-col items-center justify-center w-full h-24 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50  hover:bg-gray-100 ">
                                <div class="flex items-center justify-center pt-5 pb-6 gap-4">
                                    <x-icons.uplaod class="w-8 h-8 mb-4 text-indigo-500 " />
                                    <p class="text-indigo-500">أنقر للتحميل </p>
                                    <input id="dropzone-file-${contentIndex}" type="file" class="form-control-file video-upload hidden"
                                        name="contents[0][video]" accept="video/*" required>
                                    <div class="upload-status text-green-500"></div>
                                </div>
                            </label>
                        </div>
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
                const statusDiv = event.target.nextElementSibling;

                if (file) {
                    const formData = new FormData();
                    formData.append('video', file);

                    statusDiv.innerHTML = 'Uploading...';

                    axios.post('/upload-video', formData, {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        },
                        onUploadProgress: function(progressEvent) {
                            const percentCompleted = Math.round((progressEvent.loaded * 100) /
                                progressEvent.total);
                            statusDiv.innerHTML = `جاري التحميل...: ${percentCompleted}%`;
                        }
                    }).then(function(response) {
                        statusDiv.innerHTML = `تم التحميل: ${file.name}`;
                        event.target.nextElementSibling.value = response.data.path;
                    }).catch(function(error) {
                        statusDiv.innerHTML = 'التحميل فشل. حاول مرة اخرى.';
                        console.error('Upload error:', error);
                    });
                }
            }
        });

        document.getElementById('courseForm').addEventListener('submit', function(event) {
            event.preventDefault();
            // Here you would typically validate that all videos have been uploaded
            // For simplicity, we're just submitting the form
            this.submit();
        });
    </script>
@endpush
