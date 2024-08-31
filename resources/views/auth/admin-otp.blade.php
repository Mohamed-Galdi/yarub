    @extends('layouts.guest')
    @section('content')
        <div class="w-full md:h-[88vh] bg-gradient-to-tr from-pr-400 to-pr-700 py-12 md:py-0 ">
            <div class="max-w-screen-xl mx-auto flex flex-col  justify-center items-center h-full px-12">
                <div class="w-full flex  items-center justify-center gap-6 h-fit py-8">
                    <h1 class=" text-gray-200 md:text-5xl text-2xl text-center text-nowrap">لوحة تحكم المشرفين </h1>
                    <img src="{{ asset('assets/illustrations/lock.svg') }}" alt="" class="md:w-24 w-12  object-cover">
                </div>
                <div
                    class="md:w-1/2 w-full bg-white/20 h-fit rounded-lg border-2 border-pr-100 overflow-hidden backdrop-blur-xl p-8">
                    <form method="POST" id="otp-form" action="{{ route('admin_otp') }}">
                        @csrf
                        <div class="text-center mb-6 space-y-2 ">
                            <h2 class="text-4xl text-gray-100">التحقق من الهاتف المحمول</h2>
                            <p class="text-gray-300 font-judur text-base">أدخل رمز التحقق المكون من 6 أرقام الذي تم
                                إرساله
                                إلى رقم هاتفك.</p>
                        </div>
                        <div>
                            {{-- error --}}
                            @if ($errors->has('verification_code'))
                                <div class="text-center text-red-500 text-sm">
                                    {{ $errors->first('verification_code') }}
                                </div>
                            @endif
                        </div>
                        <div dir="ltr" class="flex items-center justify-center gap-3">
                            <input type="text"
                                class="w-14 h-14 text-center text-2xl font-extrabold text-slate-900 bg-slate-100 border border-transparent hover:border-slate-200 appearance-none rounded p-4 outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
                                pattern="\d*" maxlength="1" />
                            <input type="text"
                                class="w-14 h-14 text-center text-2xl font-extrabold text-slate-900 bg-slate-100 border border-transparent hover:border-slate-200 appearance-none rounded p-4 outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
                                maxlength="1" />
                            <input type="text"
                                class="w-14 h-14 text-center text-2xl font-extrabold text-slate-900 bg-slate-100 border border-transparent hover:border-slate-200 appearance-none rounded p-4 outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
                                maxlength="1" />
                            <input type="text"
                                class="w-14 h-14 text-center text-2xl font-extrabold text-slate-900 bg-slate-100 border border-transparent hover:border-slate-200 appearance-none rounded p-4 outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
                                maxlength="1" />
                            <input type="text"
                                class="w-14 h-14 text-center text-2xl font-extrabold text-slate-900 bg-slate-100 border border-transparent hover:border-slate-200 appearance-none rounded p-4 outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
                                maxlength="1" />
                            <input type="text"
                                class="w-14 h-14 text-center text-2xl font-extrabold text-slate-900 bg-slate-100 border border-transparent hover:border-slate-200 appearance-none rounded p-4 outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
                                maxlength="1" />
                            <input type="hidden" id="verification_code" name="verification_code" value="" />
                        </div>
                        <div class="w-full flex justify-center">
                            <button
                                class="w-[75%] mt-6 bg-green-500 hover:bg-green-600 text-white  py-2 px-4 rounded-lg transition-all duration-200 ease-in-out "
                                type="submit">
                                <span class="text-white">تأكيد</span>
                            </button>
                        </div>


                    </form>
                </div>

            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const form = document.getElementById('otp-form')
                const inputs = [...form.querySelectorAll('input[type=text]')]
                const submit = form.querySelector('button[type=submit]')

                const handleKeyDown = (e) => {
                    if (
                        !/^[0-9]{1}$/.test(e.key) &&
                        e.key !== 'Backspace' &&
                        e.key !== 'Delete' &&
                        e.key !== 'Tab' &&
                        !e.metaKey
                    ) {
                        e.preventDefault()
                    }

                    if (e.key === 'Delete' || e.key === 'Backspace') {
                        const index = inputs.indexOf(e.target);
                        if (index > 0) {
                            inputs[index - 1].value = '';
                            inputs[index - 1].focus();
                        }
                    }
                }

                const handleInput = (e) => {
                    const {
                        target
                    } = e
                    const index = inputs.indexOf(target)
                    if (target.value) {
                        if (index < inputs.length - 1) {
                            inputs[index + 1].focus()
                        } else {
                            submit.focus()
                        }
                    }
                }

                const handleFocus = (e) => {
                    e.target.select()
                }

                const handlePaste = (e) => {
                    e.preventDefault()
                    const text = e.clipboardData.getData('text')
                    if (!new RegExp(`^[0-9]{${inputs.length}}$`).test(text)) {
                        return
                    }
                    const digits = text.split('')
                    inputs.forEach((input, index) => input.value = digits[index])
                    submit.focus()
                }

                inputs.forEach((input) => {
                    input.addEventListener('input', handleInput)
                    input.addEventListener('keydown', handleKeyDown)
                    input.addEventListener('focus', handleFocus)
                    input.addEventListener('paste', handlePaste)
                })

            })
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const form = document.getElementById('otp-form');
                const inputs = [...form.querySelectorAll('input[type=text]')];
                const hiddenInput = form.querySelector('input[name="verification_code"]');

                const updateVerificationCode = () => {
                    const code = inputs.map(input => input.value).join('');
                    hiddenInput.value = code;
                };

                inputs.forEach(input => {
                    input.addEventListener('input', updateVerificationCode);
                });

                form.addEventListener('submit', (e) => {
                    updateVerificationCode();
                });
            });
        </script>
    @endsection()
