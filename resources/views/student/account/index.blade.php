@extends('layouts.student')
@section('content')
    <div class="py-12 min-h-[calc(100vh-6rem)] bg-gradient-to-t from-gray-300 to-slate-300 ">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6 flex lg:flex-row flex-col items-start justify-between gap-8">

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg lg:w-2/3 w-full">
                <h2 class="text-xl  font-medium text-gray-900 mb-4">الملف الشخصي</h2>
                <form id="profile-form" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data"
                    class="space-y-6">
                    @csrf
                    @method('PUT')
                    <div class="flex justify-between items-start gap-6">

                        <div class="w-1/3 space-y-6 flex flex-col items-center">
                            <label for="avatar" class="block text-lg  text-gray-700">الصورة</label>
                            <label for="avatar-upload" class="  flex justify-center ">
                                <div
                                    class=" cursor-pointer relative md:w-1/2 w-full aspect-square rounded-full  group transition-all duration-200 ease-in-out">
                                    <img id="avatar-preview"
                                        src="{{ $user->avatar ? asset($user->avatar) : asset('default-avatar.png') }}"
                                        alt="User Avatar"
                                        class=" w-full aspect-square rounded-full object-cover group-hover:brightness-50 transition-all duration-200 ease-in-out">
                                    <div
                                        class="absolute w-fit h-fit hidden group-hover:block top-1/2 right-1/2 translate-x-1/2  -translate-y-1/2 text-4xl text-white transition-all duration-200 ease-in-out ">
                                        +
                                    </div>

                                </div>

                                <input id="avatar-upload" name="avatar" type="file" accept="image/*" class="hidden">
                            </label>
                        </div>

                        <div class="w-2/3 space-y-6">
                            <x-form.input-light type="text" name="name" id="name" label="الاسم"
                                value="{{ $user->name }}" required />

                            <x-form.input-light type="email" name="email" id="email" label="البريد الإلكتروني"
                                value="{{ $user->email }}" required />
                        </div>


                    </div>


                    <div>
                        <button type="submit"
                            class="inline-flex w-full justify-center py-2 px-4 border border-transparent shadow-sm text-xl font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            تحديث الحساب
                        </button>
                    </div>
                </form>
            </div>

            <button id="change-password-btn"
                class="lg:w-1/3 w-full p-2 text-white flex justify-center items-center gap-4 rounded-lg bg-red-500 hover:bg-red-600 transition-all duration-200 ease-in-out ">
                <p>تغيير كلمة المرور</p>
                <x-icons.key class="w-5 h-5 ml-2" />
            </button>

        </div>
    </div>

    <!-- Password Update Modal -->
    <div id="password-modal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <div class="flex items-start justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                class="inline-block align-bottom bg-white rounded-lg text-right overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form id="password-form" method="POST" action="{{ route('profile.update.password') }}" class="p-6">
                    @csrf
                    @method('PUT')
                    <h3 class="text-3xl font-medium font-nitaqat text-gray-900 mb-4"> تغيير كلمة المرور</h3>
                    <p class="text-lg text-red-500 mb-4"> * سيتم تسجيل خروجك بعد تغيير كلمة المرور الخاصة بك.</p>
                    <div class="space-y-4">
                        <div>
                            
                            <x-form.input-light type="password" name="current_password" id="current_password"
                                label="كلمة المرور الحالية" required />
                        </div>
                        <div>
                            
                            <x-form.input-light type="password" name="new_password" id="new_password"
                                label="كلمة المرور الجديدة" required />
                        </div>
                       
                    </div>
                    <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                        <button type="button" id="cancel-password-change"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                            إلغاء
                        </button>
                        <button type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:col-start-2 sm:text-sm">
                            تغيير كلمة المرور
                        </button>

                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const avatarUpload = document.getElementById('avatar-upload');
                const avatarPreview = document.getElementById('avatar-preview');
                const profileForm = document.getElementById('profile-form');
                const passwordForm = document.getElementById('password-form');
                const changePasswordBtn = document.getElementById('change-password-btn');
                const passwordModal = document.getElementById('password-modal');
                const cancelPasswordChange = document.getElementById('cancel-password-change');

                avatarUpload.addEventListener('change', function(event) {
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            avatarPreview.src = e.target.result;
                        }
                        reader.readAsDataURL(file);
                    }
                });

                profileForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(profileForm);

                    fetch(profileForm.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content')
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            Swal.fire({
                                title: 'تم تحديث الحساب بنجاح',
                                icon: 'success',
                                timer: 3000,
                                timerProgressBar: true,
                                showConfirmButton: false,
                            });
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                title: 'خطأ في تحديث الحساب',
                                icon: 'error',
                                timer: 3000,
                                timerProgressBar: true,
                                showConfirmButton: false,
                            });
                        });
                });

                changePasswordBtn.addEventListener('click', function() {
                    passwordModal.classList.remove('hidden');
                });

                cancelPasswordChange.addEventListener('click', function() {
                    passwordModal.classList.add('hidden');
                });

                passwordForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(passwordForm);

                    fetch(passwordForm.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content')
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.error) {
                                Swal.fire({
                                    title: 'خطأ في تحديث كلمة المرور',
                                    icon: 'error',
                                    timer: 3000,
                                    timerProgressBar: true,
                                    showConfirmButton: false,
                                });
                            } else {
                                Swal.fire({
                                    title: 'تم تحديث كلمة المرور بنجاح',
                                    icon: 'success',
                                    timer: 3000,
                                    timerProgressBar: true,
                                    showConfirmButton: false,
                                }).then(() => {
                                    window.location.href = '/login';
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                title: 'خطأ في تحديث الحساب',
                                icon: 'error',
                                timer: 3000,
                                timerProgressBar: true,
                                showConfirmButton: false,
                            });
                        });
                });
            });
        </script>
    @endpush
@endsection()
