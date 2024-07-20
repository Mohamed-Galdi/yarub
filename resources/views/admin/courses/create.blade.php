@extends('layouts.admin')

@section('content')
<div class="container">
    <h1>Create New Course</h1>
    <form id="courseForm" action="{{ route('admin.courses.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="title">Course Title</label>
            <input type="text" class="form-control" id="title" name="title" required>
        </div>
        <div class="form-group">
            <label for="description">Course Description</label>
            <textarea class="form-control" id="description" name="description" required></textarea>
        </div>
        <div class="form-group">
            <label for="price">Price</label>
            <input type="number" class="form-control" id="price" name="price" step="0.01" required>
        </div>

        <h2>Course Contents</h2>
        <div id="content-forms">
            <div class="content-form mb-3">
                <div class="form-group">
                    <label>Content Title</label>
                    <input type="text" class="form-control content-title" name="content_titles[]" required>
                </div>
                <div class="form-group">
                    <label>Video File</label>
                    <input type="file" class="form-control-file video-upload" accept="video/*" >
                    <input type="hidden" name="content_videos[]">
                    <div class="upload-status"></div>
                </div>
            </div>
        </div>
        <button type="button" class="btn btn-secondary" id="add-content">Add Another Video</button>
        <button type="submit" class="btn btn-primary" id="submit-form">Create Course</button>
    </form>
</div>

@endsection

@push('scripts')
<script src="https://unpkg.com/axios/dist/axios.min.js"></script>
<script>
    function createContentForm() {
        const formCount = document.querySelectorAll('.content-form').length;
        return `
            <div class="content-form mb-3">
                <div class="form-group">
                    <label>Content Title</label>
                    <input type="text" class="form-control content-title" name="content_titles[]" required>
                </div>
                <div class="form-group">
                    <label>Video File</label>
                    <input type="file" class="form-control-file video-upload" accept="video/*" required>
                    <input type="hidden" name="content_videos[]">
                    <div class="upload-status"></div>
                </div>
                <button type="button" class="btn btn-danger remove-content">Remove</button>
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

                statusDiv.innerHTML = 'Uploading...';

                axios.post('{{ route("upload.video") }}', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    },
                    onUploadProgress: function(progressEvent) {
                        const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                        statusDiv.innerHTML = `Uploading: ${percentCompleted}%`;
                    }
                }).then(function (response) {
                    statusDiv.innerHTML = `Uploaded: ${file.name}`;
                    hiddenInput.value = response.data.path;
                }).catch(function (error) {
                    statusDiv.innerHTML = 'Upload failed. Please try again.';
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
@endpush