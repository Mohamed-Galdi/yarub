@extends('layouts.admin')

@section('content')
    <div class="">
        <h1 class="text-4xl text-indigo-700 mb-4">إنشاء درس جديد</h1>
        <div class="bg-gray-50 p-4 rounded-lg border-2 border-white">
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
                    <div class="w-full h-1 bg-gray-400 rounded-md"></div>
                </div>

                {{-- ///////////////////// Course Contents ///////////////////// --}}

                <h2 class="text-2xl text-indigo-500 m-4">محتوى الدورة </h2>
                <div id="content-forms" class="space-y-6">
                    <div id="accordion-collapse" data-accordion="open" class=" content-form">
                        <h2 id="accordion-collapse-heading-1">
                            <button type="button"
                                class="flex items-center justify-between w-full p-5 font-medium rtl:text-right text-white bg-indigo-500 border border-b-0 border-gray-200 rounded-t-xl focus:ring-4 focus:ring-gray-200  hover:bg-indigo-700  gap-3"
                                data-accordion-target="#accordion-collapse-body-1" aria-expanded="true"
                                aria-controls="accordion-collapse-body-1">
                                What is Flowbite?


                            </button>
                        </h2>
                        <div id="accordion-collapse-body-1" class="hidden" aria-labelledby="accordion-collapse-heading-1">
                            <div class="p-5 border border-b-0 border-gray-200 bg-white">
                                <div class="form-group">
                                    <label>Content Type</label>
                                    <div>
                                        <label><input type="radio" name="contents[0][type]" value="video" checked>
                                            Video</label>
                                        <label><input type="radio" name="contents[0][type]" value="pdf"> PDF</label>
                                        <label><input type="radio" name="contents[0][type]" value="text"> Text</label>
                                    </div>
                                </div>
                                <div class="form-group content-input">
                                    <label>Content</label>
                                    <input type="file" class="form-control" name="contents[0][file]" accept="video/*">
                                </div>

                            </div>
                        </div>

                    </div>

                </div>
                <button type="button" class="btn btn-secondary" id="add-content">Add Another Content</button>
                <button type="submit" class="btn btn-primary">Create Course</button>
            </form>

        </div>
    </div>
@endsection

@push('scripts')
    {{-- /////////////////// Form Repeater /////////////////// --}}
    <script>
        let contentIndex = 0;

        function updateContentInput(radioButton) {
            const contentForm = radioButton.closest('.content-form');
            const contentInput = contentForm.querySelector('.content-input');
            const inputType = radioButton.value;

            let inputHtml = '';
            if (inputType === 'video') {
                inputHtml =
                    `<input type="file" class="form-control" name="contents[${contentForm.dataset.index}][file]" accept="video/*">`;
            } else if (inputType === 'pdf') {
                inputHtml =
                    `<input type="file" class="form-control" name="contents[${contentForm.dataset.index}][file]" accept="application/pdf">`;
            } else if (inputType === 'text') {
                inputHtml =
                    `<textarea class="form-control" name="contents[${contentForm.dataset.index}][text]"></textarea>`;
            }

            contentInput.innerHTML = `<label>Content</label>${inputHtml}`;
        }

        function addContentForm() {
            contentIndex++;
            const contentForms = document.getElementById('content-forms');
            const newContentForm = document.querySelector('.content-form').cloneNode(true);

            newContentForm.dataset.index = contentIndex;

            newContentForm.querySelectorAll('input[type="radio"]').forEach(radio => {
                radio.name = `contents[${contentIndex}][type]`;
                radio.checked = radio.value === 'video';
            });

            newContentForm.querySelector('.content-input').innerHTML = `
            <label>Content</label>
            <input type="file" class="form-control" name="contents[${contentIndex}][file]" accept="video/*">
        `;

            // Add remove button
            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.className = 'btn btn-danger remove-content';
            removeButton.textContent = 'Remove';
            removeButton.onclick = function() {
                this.closest('.content-form').remove();
            };
            newContentForm.appendChild(removeButton);

            contentForms.appendChild(newContentForm);
        }

        document.getElementById('add-content').addEventListener('click', addContentForm);

        document.getElementById('content-forms').addEventListener('change', function(event) {
            if (event.target.type === 'radio') {
                updateContentInput(event.target);
            }
        });

        // Initial setup
        document.querySelector('.content-form').dataset.index = contentIndex;
    </script>
@endpush
