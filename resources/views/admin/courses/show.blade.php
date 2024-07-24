@extends('layouts.admin')

@section('content')
    <div>
        <h1 class="text-4xl text-gray-500 mb-6">{{ $course->title }}</h1>
        <div class="flex lg:flex-row flex-col justify-center items-center gap-6">
            <div
                class="lg:w-1/2 w-full flex px-8 py-3 justify-between items-center bg-gray-800 text-gray-200 text-2xl rounded-xl border border-indigo-500 ">
                <div>
                    <x-icons.students class="w-8 h-8 text-gray-50" />
                    <p>مجموع الطلاب</p>
                </div>
                <div>
                    <p>{{ $course->students->count() }}</p>
                </div>
            </div>
            <div
                class="lg:w-1/2 w-full flex px-8 py-3 justify-between items-center bg-gray-800 text-gray-200 text-2xl rounded-xl border border-indigo-500 ">
                <div>
                    <x-icons.payments class="w-8 h-8 text-gray-50" />
                    <p>مجموع المداخيل</p>
                </div>
                <div>
                    <p>{{ $course->students->count() }}</p>
                </div>
            </div>
        </div>
        {{-- students table --}}
        <div class="mt-8">
            <livewire:course-students-table :courseId="$course->id" />
        </div>
    </div>
    <script>
        function showDeleteConfirmationModal(studentId, courseId) {
            Swal.fire({
                title: 'تأكيد الحذف',
                text: 'هل أنت متأكد أنك تريد إزالة هذا الطالب؟',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'نعم، قم بالحذف',
                cancelButtonText: 'إلغاء'
            }).then((result) => {
                if (result.isConfirmed) {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    axios.post(`/admin-dashboard/courses/detach/${courseId}/${studentId}`, {
                            _method: 'DELETE'
                        }, {
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Content-Type': 'application/json'
                            }
                        })
                        .then(response => {
                            Swal.fire('Success', 'تم إزالة الطالب بنجاح !', 'success');
                            // Refresh the PowerGrid table
                            Livewire.dispatch('pg:eventRefresh-' + window.livewire_table_id);
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire('Error', 'حدث خطأ أثناء حذف الطالب', 'error');
                        });
                }
            });
        }
    </script>
@endsection
