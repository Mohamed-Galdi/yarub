@extends('layouts.admin')

@section('content')
    <div class="container">
        <h1 class="text-4xl text-indigo-700 mb-4">تعديل دورة: <span class="text-gray-500">{{ $course->title }}</span></h1>
        {{-- <x-form.validation-errors :errors="$errors" /> --}}
        <form id="courseForm" action="{{ route('admin.courses.update', $course->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            <div class="form-group flex justify-start items-start gap-4">

                <x-form.input-light name="title" label="العنوان" placeholder="مثال لعنوان: مقدمة يَعرُب في التأسيس للقدرات"
                    type="text" required class="w-3/5" id="title" value="{{ $course->title }}" required />


                <x-form.input-light type="number" id="price" name="price" label="السعر (ريال سعودي)"
                    value="{{ $course->price }}" class="w-1/6 " required />

                <x-form.toogle label="حالة النشر" name="published" value="{{ $course->published }}"
                    class="w-1/5 items-center justify-start" />



            </div>
            <div class="form-group">

                <x-form.textarea-light name="description" label="الوصف" placeholder="{{ $course->description }}"
                    type="text" required class="w-full" id="description" />
            </div>

            <div>
                <h2 class="text-2xl text-indigo-500 mt-8 mb-2">محتوى الدورة </h2>
                <div class="w-full h-1 bg-gray-400 rounded-md mb-6"></div>
            </div>

            <div id="content-forms" class="space-y-4">
                @forelse ($course->content as $content)
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
                            <div class="flex flex-col items-center gap-3 justify-center w-2/3  mx-auto ">
                                <label for="video"
                                    class="p-3 w-full bg-gray-900 text-white rounded-lg cursor-pointer hover:bg-gray-700 flex gap-2 items-center justify-center">
                                    تغيير الفيديو
                                </label>
                                <input id="video" type="file" class="form-control-file video-upload hidden"
                                    accept="video/*">
                                <input type="hidden" name="content_videos[]" value="{{ $content->url }}">
                                <div class="upload-status w-full flex justify-center items-center">
                                    <video src="{{ asset('storage/' . $content->url) }}" controls
                                        class="w-full h-full"></video>
                                </div>
                            </div>



                        </div>
                        <div class="w-full flex justify-center my-2">
                            <button type="button"
                                class="delete-content p-2 text-white bg-red-500 rounded-lg cursor-pointer hover:bg-red-700 flex gap-2 items-center justify-center"
                                data-content-id="{{ $content->id }}">
                                <p>حذف الدرس</p>
                                <x-icons.trash class="w-4 h-4 ml-2" />
                            </button>
                        </div>
                    </div>
                @empty
                    <x-card.empty-state title="لا يوجد محتوى بعد" message="اضغط على زر الإضافة لإنشاء محتوى" :image="false" />
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


    <div class="fixed z-10 inset-0 overflow-y-auto hidden" id="confirmModal" aria-labelledby="confirmModalLabel"
        aria-hidden="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div
                    class="flex flex-col h-full  items-center justify-center w-full text-center gap-3 p-3 bg-red-100 rounded-lg">
                    <x-icons.warning class="w-12 h-12 text-red-500" />
                    <p class=" text-2xl font-hacen  text-red-600">إنتباه</p>
                    <p class="text-base font-judur">هل انت متاكد من عملية حدف الدرس</p>
                </div>
                <div class="bg-white px-4 py-2 flex justify-center items-center gap-2">

                    <button type="button"
                        class="text-white p-3 w-1/2 bg-red-500 rounded-lg cursor-pointer hover:bg-red-700 flex gap-2 items-center justify-center"
                        id="confirmDelete">حدف</button>
                    <button type="button"
                        class="text-gray-800 p-3 w-1/2 bg-gray-300 rounded-lg cursor-pointer hover:bg-gray-400 flex gap-2 items-center justify-center"
                        id="cancelDelete">إلغاء</button>

                </div>
            </div>
        </div>
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

        document.getElementById('courseForm').addEventListener('submit', function(event) {
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

    {{-- Confirmation Modal --}}
    <script>
        let contentToDelete = null;

        // Function to show the modal
        function showModal() {
            document.getElementById('confirmModal').classList.remove('hidden');
        }

        // Function to hide the modal
        function hideModal() {
            document.getElementById('confirmModal').classList.add('hidden');
        }

        // Attach event listeners to delete buttons
        document.querySelectorAll('.delete-content').forEach(button => {
            button.addEventListener('click', function() {
                contentToDelete = this.dataset.contentId;
                showModal();
            });
        });

        // Handle confirm delete button click
        document.getElementById('confirmDelete').addEventListener('click', function() {
            if (contentToDelete) {
                axios.delete(`/courses/content/${contentToDelete}`, {
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        }
                    })
                    .then(function(response) {
                        if (response.data.success) {
                            const contentForm = document.querySelector(`[data-content-id="${contentToDelete}"]`)
                                .closest('.content-form');
                            contentForm.remove();
                            hideModal();
                            contentToDelete = null;
                        }
                    })
                    .catch(function(error) {
                        console.error('Delete error:', error);
                        alert('An error occurred while deleting the content. Please try again.');
                    });
            }
        });

        // Handle cancel button click
        document.getElementById('cancelDelete').addEventListener('click', function() {
            hideModal();
        });
    </script>
@endpush
