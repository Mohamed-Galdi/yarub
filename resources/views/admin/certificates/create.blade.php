@extends('layouts.admin')
@section('panel-own-title', true)

@section('content')
    <div class="container">
        {{-- <x-form.errors :errors="$errors" /> --}}
        <div class="flex justify-between">
            <h1 class="panel-page-title">منح شهادة جديدة</h1>
            {{-- // back button --}}
            <x-btn.back route="admin.certificates" />
        </div>
        <form action="{{ route('admin.certificates.store') }}" method="POST" class="panel-surface space-y-4">
            @csrf
            <div class="grid lg:grid-cols-3 grid-cols-1 gap-3 py-4">
                <div class="w-full">
                    <label for="student_id" class="text-gray-800 font-judur ms-3 mb-1 font-semibold">إختار
                        الطالب(ة) </label>
                    <select name="student_id" id="student_id"
                        class=" block w-full h-[3.1rem] rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        required>
                        <option value="">إختار</option>
                        @foreach ($students as $student)
                            <option value="{{ $student->id }}">{{ $student->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="content_type" class="text-gray-800 font-judur ms-3 mb-1 font-semibold">المواد </label>
                    <select name="content_type" id="content_type"
                        class=" block w-full h-[3.1rem] rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        required>
                        <option value="">إختار</option>
                        <option value="course"> الدورات</option>
                        <option value="lesson"> الشروحات</option>
                    </select>
                </div>
                <div class="form-group">
                    {{-- <label for="content_id">Content</label>
                    <select name="content_id" id="content_id" class="form-control" required disabled>
                        <option value="">Select content</option>
                    </select> --}}
                    <label for="content_id" class="text-gray-800 font-judur ms-3 mb-1 font-semibold">الدورات / الشروحات
                    </label>
                    <select name="content_id" id="content_id" required disabled
                        class=" block w-full h-[3.1rem] rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        required>
                        <option value="">فارغة</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="panel-button panel-button--primary w-full my-4 p-3 rounded-lg">
                منح الشهادة
            </button>
        </form>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                $('#student_id, #content_type').change(function() {
                    const studentId = $('#student_id').val();
                    const contentType = $('#content_type').val();

                    if (studentId && contentType) {
                        $.ajax({
                            url: '{{ route('admin.certificates.get-student-content') }}',
                            method: 'GET',
                            data: {
                                student_id: studentId,
                                content_type: contentType
                            },
                            success: function(data) {
                                $('#content_id').empty().append(
                                    '<option value="">إختار</option>');
                                $.each(data, function(id, title) {
                                    $('#content_id').append($('<option>', {
                                        value: id,
                                        text: title
                                    }));
                                });
                                $('#content_id').prop('disabled', false);
                            },
                            failure: function(data) {
                                console.log(data);
                            }
                        });
                    } else {
                        $('#content_id').prop('disabled', true);
                    }
                });
            });
        </script>
    @endpush
@endsection
