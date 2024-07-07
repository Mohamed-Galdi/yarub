@extends('layouts.guest')
@section('content')
    {{-- ///////////////////////////// Hero section /////////////////////////////// --}}
    <div style="background-image: url('./assets/images/contact-bg-2.jpg')"
        class=" h-[40rem]  bg-no-repeat bg-cover bg-buttom flex  ">
        <div class="max-w-screen-xl mx-auto flex justify-end items-center w-full ">
            <div class=" w-[480px] h-[500px] rounded-xl p-8  backdrop-blur-md bg-white/20  ">
                <form class="w-full space-y-4" enctype="multipart/form-data" method="POST">
                    <div class="w-full lg:flex gap-6">
                        <div class="relative z-0 w-full mb-5 group">
                            <input type="text" name="name" id="floating_name"
                                class="block py-2.5 px-0 w-full text-lg text-gray-900 bg-transparent border-0 border-b-2 border-pr-200 focus:outline-none focus:ring-0 focus:border-pr-500 peer"
                                placeholder=" " required />

                            <label for="floating_name"
                                class="peer-focus:font-medium absolute font-sec  text-lg text-pr-500  duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto peer-focus:soft_black peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">الإسم
                                الكامل</label>
                        </div>

                    </div>
                    <div class="w-full lg:flex gap-6">
                        <div class="relative z-0 w-full mb-5 group">
                            <input type="text" name="email" id="floating_email"
                                class="block py-2.5 px-0 w-full text-lg text-gray-900 bg-transparent border-0 border-b-2 border-pr-200 focus:outline-none focus:ring-0 focus:border-pr-500 peer"
                                placeholder=" " required />

                            <label for="floating_email"
                                class="peer-focus:font-medium absolute font-sec  text-lg text-pr-500  duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto peer-focus:soft_black peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">
                                البريد الإلكتروني</label>
                        </div>

                    </div>
                    <div class="w-full lg:flex gap-6">
                        <div class="mb-5 w-full ">
                            <label for="message" class="block mb-2 text-2xl text-pr-800  ">رسالتك</label>
                            <textarea name="message" id="message" rows="5"
                                class="bg-gray-50/30 border-2 border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5
                              focus:outline-none focus:ring-0 focus:border-pr-200 focus:border-2
                                "
                                required></textarea>
                        </div>
                    </div>
                    <x-btn.scale-light class="flex gap-8 w-full">
                        <p>ارسال الرسالة</p>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="w-6 h-6 text-pr-500">
                            <path class="fill-current"
                                d="M498.1 5.6c10.1 7 15.4 19.1 13.5 31.2l-64 416c-1.5 9.7-7.4 18.2-16 23s-18.9 5.4-28 1.6L284 427.7l-68.5 74.1c-8.9 9.7-22.9 12.9-35.2 8.1S160 493.2 160 480V396.4c0-4 1.5-7.8 4.2-10.7L331.8 202.8c5.8-6.3 5.6-16-.4-22s-15.7-6.4-22-.7L106 360.8 17.7 316.6C7.1 311.3 .3 300.7 0 288.9s5.9-22.8 16.1-28.7l448-256c10.7-6.1 23.9-5.5 34 1.4z" />
                        </svg>
                    </x-btn.scale-light>
                </form>
            </div>
        </div>

    </div>
    {{-- ///////////////////////////// Hero section /////////////////////////////// --}}
    <div class="bg-pr-500 py-12 px-8 md:px-4 lg:px-0">
        <div class="mx-auto max-w-screen-xl">

            <h2 class="text-gray-200 text-center text-5xl mb-12">أو تواصل معنا مباشرة من هنا </h2>
            <div class="flex md:flex-row flex-col jusfify-between items-center gap-8 ">
                <div
                    class="text-white flex flex-col md:items-start items-center md:text-start text-center gap-4 py-4 pe-12 ps-4 md:w-1/3 w-full">
                    <div
                        class="w-16 h-16 bg-gradient-to-bl from-indigo-300 to-indigo-700 rounded-lg flex items-center justify-center transition-all duration-300 ease-in-out hover:scale-105 cursor-pointer shadow-blue-400 shadow-2xl ">
                        <x-icons.email class="w-[2.5rem] h-[2.5rem] " />
                    </div>
                    <div class="flex gap-4 items-end ">
                        <h3 class="underline font-judur underline-offset-8"> البريد الإلكتروني </h3>
                        {{-- clcik to copy --}}
                        <button class=" flex justify-start items-center gap-2 group " onclick="copyEmail()">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"
                                class=" text-white w-6 h-6 group-hover:scale-[0.95] transition-all duration-300 ease-in-out ">
                                <path class="fill-current"
                                    d="M208 0H332.1c12.7 0 24.9 5.1 33.9 14.1l67.9 67.9c9 9 14.1 21.2 14.1 33.9V336c0 26.5-21.5 48-48 48H208c-26.5 0-48-21.5-48-48V48c0-26.5 21.5-48 48-48zM48 128h80v64H64V448H256V416h64v48c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V176c0-26.5 21.5-48 48-48z" />
                            </svg>
                            <p class=" text-sm hidden" id="copyEmail">تم النسخ !</p>
                        </button>
                    </div>
                    <p class="text-lg font-nitaqat">yarubsa25@gmail.com</p>
                </div>
                <div
                    class="text-white flex flex-col md:items-start items-center md:text-start text-center  gap-4 py-4 pe-12 ps-4 md:w-1/3 w-full">
                    <div
                        class="w-16 h-16 bg-gradient-to-bl from-indigo-300 to-indigo-700 rounded-lg flex items-center justify-center transition-all duration-300 ease-in-out hover:scale-105 cursor-pointer shadow-blue-400 shadow-2xl ">
                        <x-icons.tele class="w-[2.5rem] h-[2.5rem] " />
                    </div>
                    <div class="flex gap-4 items-end ">
                        <h3 class="underline font-judur underline-offset-8"> الهاتف </h3>
                        {{-- clcik to copy --}}
                        <button class=" flex justify-start items-center gap-2 group " onclick="copyPhone()">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"
                                class=" text-white w-6 h-6 group-hover:scale-[0.95] transition-all duration-300 ease-in-out ">
                                <path class="fill-current"
                                    d="M208 0H332.1c12.7 0 24.9 5.1 33.9 14.1l67.9 67.9c9 9 14.1 21.2 14.1 33.9V336c0 26.5-21.5 48-48 48H208c-26.5 0-48-21.5-48-48V48c0-26.5 21.5-48 48-48zM48 128h80v64H64V448H256V416h64v48c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V176c0-26.5 21.5-48 48-48z" />
                            </svg>
                            <p class=" text-sm hidden" id="copyPhone">تم النسخ !</p>
                        </button>
                    </div>
                    <p class="text-lg font-nitaqat" dir="ltr">+966 5 0004 4502</p>
                </div>
                <div
                    class="text-white flex flex-col md:items-start items-center md:text-start text-center  gap-4 py-4 pe-12 ps-4 md:w-1/3 w-full">
                    <div
                        class="w-16 h-16 bg-gradient-to-bl  from-indigo-300 to-indigo-700  rounded-lg flex items-center justify-center transition-all duration-300 ease-in-out hover:scale-105 cursor-pointer shadow-blue-400 shadow-2xl ">
                        <x-icons.address class="w-[2.5rem] h-[2.5rem]" />
                    </div>
                    <div class="flex gap-4 items-end ">
                        <h3 class="underline font-judur underline-offset-8"> موقعنا </h3>
                        {{-- clcik to copy --}}
                        <button class=" flex justify-start items-center gap-2 group " onclick="copyAddress()">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"
                                class=" text-white w-6 h-6 group-hover:scale-[0.95] transition-all duration-300 ease-in-out ">
                                <path class="fill-current"
                                    d="M208 0H332.1c12.7 0 24.9 5.1 33.9 14.1l67.9 67.9c9 9 14.1 21.2 14.1 33.9V336c0 26.5-21.5 48-48 48H208c-26.5 0-48-21.5-48-48V48c0-26.5 21.5-48 48-48zM48 128h80v64H64V448H256V416h64v48c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V176c0-26.5 21.5-48 48-48z" />
                            </svg>
                            <p class=" text-sm hidden" id="copyAddress">تم النسخ !</p>
                        </button>
                    </div>
                    <p class="text-lg font-nitaqat">الرياض، المملكة العربية السعودية</p>

                </div>
            </div>
        </div>

    </div>

    {{-- //////////////////////////// Our social media acounts /////////////////////////////// --}}
    <div class="bg-gray-100 py-8 px-8 md:px-4 lg:px-0">
        <div class="mx-auto max-w-screen-xl">
            <h2 class="text-4xl mx-auto text-center  ">لا تنسى متابعتنا عبر حساباتنا الرسمية</h2>
            <div dir="ltr" class="bg-white rounded-xl shadow-lg my-8 w-full h-auto py-8 flex items-center justify-center gap-4 flex-wrap">
                <button
                    class="w-24 h-24 flex items-center justify-center rounded-full relative overflow-hidden bg-gray-300 shadow-md shadow-gray-200 group transition-all duration-500">
                    <svg class="fill-gray-900 relative z-10 transition-all duration-500 group-hover:fill-white"
                        xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 51 51" fill="none">
                        <path
                            d="M17.4456 25.7808C17.4456 21.1786 21.1776 17.4468 25.7826 17.4468C30.3875 17.4468 34.1216 21.1786 34.1216 25.7808C34.1216 30.383 30.3875 34.1148 25.7826 34.1148C21.1776 34.1148 17.4456 30.383 17.4456 25.7808ZM12.9377 25.7808C12.9377 32.8708 18.6883 38.618 25.7826 38.618C32.8768 38.618 38.6275 32.8708 38.6275 25.7808C38.6275 18.6908 32.8768 12.9436 25.7826 12.9436C18.6883 12.9436 12.9377 18.6908 12.9377 25.7808ZM36.1342 12.4346C36.1339 13.0279 36.3098 13.608 36.6394 14.1015C36.9691 14.595 37.4377 14.9797 37.9861 15.2069C38.5346 15.4342 39.1381 15.4939 39.7204 15.3784C40.3028 15.2628 40.8378 14.9773 41.2577 14.5579C41.6777 14.1385 41.9638 13.6041 42.0799 13.0222C42.1959 12.4403 42.1367 11.8371 41.9097 11.2888C41.6828 10.7406 41.2982 10.2719 40.8047 9.94202C40.3112 9.61218 39.7309 9.436 39.1372 9.43576H39.136C38.3402 9.43613 37.5771 9.75216 37.0142 10.3144C36.4514 10.8767 36.1349 11.6392 36.1342 12.4346ZM15.6765 46.1302C13.2377 46.0192 11.9121 45.6132 11.0311 45.2702C9.86323 44.8158 9.02993 44.2746 8.15381 43.4002C7.27768 42.5258 6.73536 41.6938 6.28269 40.5266C5.93928 39.6466 5.53304 38.3214 5.42217 35.884C5.3009 33.2488 5.27668 32.4572 5.27668 25.781C5.27668 19.1048 5.3029 18.3154 5.42217 15.678C5.53324 13.2406 5.94248 11.918 6.28269 11.0354C6.73736 9.86816 7.27888 9.03536 8.15381 8.15976C9.02873 7.28416 9.86123 6.74216 11.0311 6.28976C11.9117 5.94656 13.2377 5.54056 15.6765 5.42976C18.3133 5.30856 19.1054 5.28436 25.7826 5.28436C32.4598 5.28436 33.2527 5.31056 35.8916 5.42976C38.3305 5.54076 39.6539 5.94976 40.537 6.28976C41.7049 6.74216 42.5382 7.28536 43.4144 8.15976C44.2905 9.03416 44.8308 9.86816 45.2855 11.0354C45.6289 11.9154 46.0351 13.2406 46.146 15.678C46.2673 18.3154 46.2915 19.1048 46.2915 25.781C46.2915 32.4572 46.2673 33.2466 46.146 35.884C46.0349 38.3214 45.6267 39.6462 45.2855 40.5266C44.8308 41.6938 44.2893 42.5266 43.4144 43.4002C42.5394 44.2738 41.7049 44.8158 40.537 45.2702C39.6565 45.6134 38.3305 46.0194 35.8916 46.1302C33.2549 46.2514 32.4628 46.2756 25.7826 46.2756C19.1024 46.2756 18.3125 46.2514 15.6765 46.1302ZM15.4694 0.932162C12.8064 1.05336 10.9867 1.47536 9.39755 2.09336C7.75177 2.73156 6.35853 3.58776 4.9663 4.97696C3.57406 6.36616 2.71955 7.76076 2.08097 9.40556C1.46259 10.9948 1.04034 12.8124 0.919069 15.4738C0.795795 18.1394 0.767578 18.9916 0.767578 25.7808C0.767578 32.57 0.795795 33.4222 0.919069 36.0878C1.04034 38.7494 1.46259 40.5668 2.08097 42.156C2.71955 43.7998 3.57426 45.196 4.9663 46.5846C6.35833 47.9732 7.75177 48.8282 9.39755 49.4682C10.9897 50.0862 12.8064 50.5082 15.4694 50.6294C18.138 50.7506 18.9893 50.7808 25.7826 50.7808C32.5759 50.7808 33.4286 50.7526 36.0958 50.6294C38.759 50.5082 40.5774 50.0862 42.1676 49.4682C43.8124 48.8282 45.2066 47.9738 46.5989 46.5846C47.9911 45.1954 48.8438 43.7998 49.4842 42.156C50.1026 40.5668 50.5268 38.7492 50.6461 36.0878C50.7674 33.4202 50.7956 32.57 50.7956 25.7808C50.7956 18.9916 50.7674 18.1394 50.6461 15.4738C50.5248 12.8122 50.1026 10.9938 49.4842 9.40556C48.8438 7.76176 47.9889 6.36836 46.5989 4.97696C45.2088 3.58556 43.8124 2.73156 42.1696 2.09336C40.5775 1.47536 38.7588 1.05136 36.0978 0.932162C33.4306 0.810962 32.5779 0.780762 25.7846 0.780762C18.9913 0.780762 18.138 0.808962 15.4694 0.932162Z"
                            fill="" />
                        <path
                            d="M17.4456 25.7808C17.4456 21.1786 21.1776 17.4468 25.7826 17.4468C30.3875 17.4468 34.1216 21.1786 34.1216 25.7808C34.1216 30.383 30.3875 34.1148 25.7826 34.1148C21.1776 34.1148 17.4456 30.383 17.4456 25.7808ZM12.9377 25.7808C12.9377 32.8708 18.6883 38.618 25.7826 38.618C32.8768 38.618 38.6275 32.8708 38.6275 25.7808C38.6275 18.6908 32.8768 12.9436 25.7826 12.9436C18.6883 12.9436 12.9377 18.6908 12.9377 25.7808ZM36.1342 12.4346C36.1339 13.0279 36.3098 13.608 36.6394 14.1015C36.9691 14.595 37.4377 14.9797 37.9861 15.2069C38.5346 15.4342 39.1381 15.4939 39.7204 15.3784C40.3028 15.2628 40.8378 14.9773 41.2577 14.5579C41.6777 14.1385 41.9638 13.6041 42.0799 13.0222C42.1959 12.4403 42.1367 11.8371 41.9097 11.2888C41.6828 10.7406 41.2982 10.2719 40.8047 9.94202C40.3112 9.61218 39.7309 9.436 39.1372 9.43576H39.136C38.3402 9.43613 37.5771 9.75216 37.0142 10.3144C36.4514 10.8767 36.1349 11.6392 36.1342 12.4346ZM15.6765 46.1302C13.2377 46.0192 11.9121 45.6132 11.0311 45.2702C9.86323 44.8158 9.02993 44.2746 8.15381 43.4002C7.27768 42.5258 6.73536 41.6938 6.28269 40.5266C5.93928 39.6466 5.53304 38.3214 5.42217 35.884C5.3009 33.2488 5.27668 32.4572 5.27668 25.781C5.27668 19.1048 5.3029 18.3154 5.42217 15.678C5.53324 13.2406 5.94248 11.918 6.28269 11.0354C6.73736 9.86816 7.27888 9.03536 8.15381 8.15976C9.02873 7.28416 9.86123 6.74216 11.0311 6.28976C11.9117 5.94656 13.2377 5.54056 15.6765 5.42976C18.3133 5.30856 19.1054 5.28436 25.7826 5.28436C32.4598 5.28436 33.2527 5.31056 35.8916 5.42976C38.3305 5.54076 39.6539 5.94976 40.537 6.28976C41.7049 6.74216 42.5382 7.28536 43.4144 8.15976C44.2905 9.03416 44.8308 9.86816 45.2855 11.0354C45.6289 11.9154 46.0351 13.2406 46.146 15.678C46.2673 18.3154 46.2915 19.1048 46.2915 25.781C46.2915 32.4572 46.2673 33.2466 46.146 35.884C46.0349 38.3214 45.6267 39.6462 45.2855 40.5266C44.8308 41.6938 44.2893 42.5266 43.4144 43.4002C42.5394 44.2738 41.7049 44.8158 40.537 45.2702C39.6565 45.6134 38.3305 46.0194 35.8916 46.1302C33.2549 46.2514 32.4628 46.2756 25.7826 46.2756C19.1024 46.2756 18.3125 46.2514 15.6765 46.1302ZM15.4694 0.932162C12.8064 1.05336 10.9867 1.47536 9.39755 2.09336C7.75177 2.73156 6.35853 3.58776 4.9663 4.97696C3.57406 6.36616 2.71955 7.76076 2.08097 9.40556C1.46259 10.9948 1.04034 12.8124 0.919069 15.4738C0.795795 18.1394 0.767578 18.9916 0.767578 25.7808C0.767578 32.57 0.795795 33.4222 0.919069 36.0878C1.04034 38.7494 1.46259 40.5668 2.08097 42.156C2.71955 43.7998 3.57426 45.196 4.9663 46.5846C6.35833 47.9732 7.75177 48.8282 9.39755 49.4682C10.9897 50.0862 12.8064 50.5082 15.4694 50.6294C18.138 50.7506 18.9893 50.7808 25.7826 50.7808C32.5759 50.7808 33.4286 50.7526 36.0958 50.6294C38.759 50.5082 40.5774 50.0862 42.1676 49.4682C43.8124 48.8282 45.2066 47.9738 46.5989 46.5846C47.9911 45.1954 48.8438 43.7998 49.4842 42.156C50.1026 40.5668 50.5268 38.7492 50.6461 36.0878C50.7674 33.4202 50.7956 32.57 50.7956 25.7808C50.7956 18.9916 50.7674 18.1394 50.6461 15.4738C50.5248 12.8122 50.1026 10.9938 49.4842 9.40556C48.8438 7.76176 47.9889 6.36836 46.5989 4.97696C45.2088 3.58556 43.8124 2.73156 42.1696 2.09336C40.5775 1.47536 38.7588 1.05136 36.0978 0.932162C33.4306 0.810962 32.5779 0.780762 25.7846 0.780762C18.9913 0.780762 18.138 0.808962 15.4694 0.932162Z"
                            fill="" />
                        <defs>
                            <radialGradient id="paint0_radial_7092_54404" cx="0" cy="0" r="1"
                                gradientUnits="userSpaceOnUse"
                                gradientTransform="translate(7.41436 51.017) scale(65.31 65.2708)">
                                <stop offset="0.09" stop-color="#FA8F21" />
                                <stop offset="0.78" stop-color="#D82D7E" />
                            </radialGradient>
                            <radialGradient id="paint1_radial_7092_54404" cx="0" cy="0" r="1"
                                gradientUnits="userSpaceOnUse"
                                gradientTransform="translate(31.1086 53.257) scale(51.4733 51.4424)">
                                <stop offset="0.64" stop-color="#8C3AAA" stop-opacity="0" />
                                <stop offset="1" stop-color="#8C3AAA" />
                            </radialGradient>
                        </defs>
                    </svg>
                    <div
                        class="absolute top-full left-0 w-full h-full rounded-full bg-gradient-to-bl from-purple-500 via-pink-500 to-yellow-500 z-0 transition-all duration-500 group-hover:top-0">
                    </div>
                </button>

                <button
                    class="w-24 h-24 flex items-center justify-center rounded-full relative overflow-hidden bg-gray-300 shadow-md shadow-gray-200 group transition-all duration-300">
                    <svg class="fill-black z-10 transition-all duration-300 group-hover:fill-white"
                        xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 72 72"
                        fill="none">
                        <path
                            d="M40.7568 32.1716L59.3704 11H54.9596L38.7974 29.383L25.8887 11H11L30.5205 38.7983L11 61H15.4111L32.4788 41.5869L46.1113 61H61L40.7557 32.1716H40.7568ZM34.7152 39.0433L32.7374 36.2752L17.0005 14.2492H23.7756L36.4755 32.0249L38.4533 34.7929L54.9617 57.8986H48.1865L34.7152 39.0443V39.0433Z"
                            fill="" />
                    </svg>
                    <div
                        class="absolute top-full left-0 w-full h-full rounded-full bg-black z-0 transition-all duration-500 group-hover:top-0">
                    </div>
                </button>

                <button
                    class="w-24 h-24 flex items-center relative overflow-hidden justify-center rounded-full bg-gray-300 shadow-md shadow-gray-200 group transition-all duration-300">
                    <svg class="fill-gray-900 relative z-10 transition-all duration-300 group-hover:fill-white"
                        xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 71 72"
                        fill="none">
                        <path
                            d="M12.5762 56.8405L15.8608 44.6381C13.2118 39.8847 12.3702 34.3378 13.4904 29.0154C14.6106 23.693 17.6176 18.952 21.9594 15.6624C26.3012 12.3729 31.6867 10.7554 37.1276 11.1068C42.5685 11.4582 47.6999 13.755 51.5802 17.5756C55.4604 21.3962 57.8292 26.4844 58.2519 31.9065C58.6746 37.3286 57.1228 42.7208 53.8813 47.0938C50.6399 51.4668 45.9261 54.5271 40.605 55.7133C35.284 56.8994 29.7125 56.1318 24.9131 53.5513L12.5762 56.8405ZM25.508 48.985L26.2709 49.4365C29.7473 51.4918 33.8076 52.3423 37.8191 51.8555C41.8306 51.3687 45.5681 49.5719 48.4489 46.7452C51.3298 43.9185 53.1923 40.2206 53.7463 36.2279C54.3002 32.2351 53.5143 28.1717 51.5113 24.6709C49.5082 21.1701 46.4003 18.4285 42.6721 16.8734C38.9438 15.3184 34.8045 15.0372 30.8993 16.0736C26.994 17.11 23.5422 19.4059 21.0817 22.6035C18.6212 25.801 17.2903 29.7206 17.2963 33.7514C17.293 37.0937 18.2197 40.3712 19.9732 43.2192L20.4516 44.0061L18.6153 50.8167L25.508 48.985Z"
                            fill="" />
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M44.0259 36.8847C43.5787 36.5249 43.0549 36.2716 42.4947 36.1442C41.9344 36.0168 41.3524 36.0186 40.793 36.1495C39.9524 36.4977 39.4093 37.8134 38.8661 38.4713C38.7516 38.629 38.5833 38.7396 38.3928 38.7823C38.2024 38.8251 38.0028 38.797 37.8316 38.7034C34.7543 37.5012 32.1748 35.2965 30.5122 32.4475C30.3704 32.2697 30.3033 32.044 30.325 31.8178C30.3467 31.5916 30.4555 31.3827 30.6286 31.235C31.2344 30.6368 31.6791 29.8959 31.9218 29.0809C31.9756 28.1818 31.7691 27.2863 31.3269 26.5011C30.985 25.4002 30.3344 24.42 29.4518 23.6762C28.9966 23.472 28.4919 23.4036 27.9985 23.4791C27.5052 23.5546 27.0443 23.7709 26.6715 24.1019C26.0242 24.6589 25.5104 25.3537 25.168 26.135C24.8256 26.9163 24.6632 27.7643 24.6929 28.6165C24.6949 29.0951 24.7557 29.5716 24.8739 30.0354C25.1742 31.1497 25.636 32.2144 26.2447 33.1956C26.6839 33.9473 27.163 34.6749 27.6801 35.3755C29.3607 37.6767 31.4732 39.6305 33.9003 41.1284C35.1183 41.8897 36.42 42.5086 37.7799 42.973C39.1924 43.6117 40.752 43.8568 42.2931 43.6824C43.1711 43.5499 44.003 43.2041 44.7156 42.6755C45.4281 42.1469 45.9995 41.4518 46.3795 40.6512C46.6028 40.1675 46.6705 39.6269 46.5735 39.1033C46.3407 38.0327 44.9053 37.4007 44.0259 36.8847Z"
                            fill="" />
                    </svg>
                    <div
                        class="absolute top-full left-0 w-full h-full rounded-full bg-green-400 z-0 transition-all duration-500 group-hover:top-0">
                    </div>
                </button>

                <button
                    class="w-24 h-24 flex items-center relative overflow-hidden justify-center rounded-full bg-gray-300 shadow-md shadow-gray-200 group transition-all duration-300">
                    <svg class="fill-gray-900 relative z-10 transition-all duration-300 group-hover:fill-white"
                        xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 72 72"
                        fill="none">
                        <path
                            d="M61.1026 23.7185C60.5048 21.471 58.7363 19.6981 56.4863 19.0904C52.4181 18 36.0951 18 36.0951 18C36.0951 18 19.7805 18 15.7039 19.0904C13.4622 19.6897 11.6937 21.4627 11.0876 23.7185C10 27.7971 10 36.3124 10 36.3124C10 36.3124 10 44.8276 11.0876 48.9063C11.6854 51.1537 13.4539 52.9267 15.7039 53.5343C19.7805 54.6247 36.0951 54.6247 36.0951 54.6247C36.0951 54.6247 52.4181 54.6247 56.4863 53.5343C58.728 52.935 60.4965 51.162 61.1026 48.9063C62.1902 44.8276 62.1902 36.3124 62.1902 36.3124C62.1902 36.3124 62.1902 27.7971 61.1026 23.7185Z"
                            fill="" />
                        <path class="fill-white transition-all duration-300 group-hover:fill-[#FF3000]"
                            d="M30.8811 44.1617L44.4392 36.3124L30.8811 28.463V44.1617Z" fill="white" />
                    </svg>
                    <div
                        class="absolute top-full left-0 w-full h-full rounded-full bg-[#FF3000] z-0 transition-all duration-500 group-hover:top-0">
                    </div>
                </button>
            </div>


        </div>

    </div>

    <script>
        function copyAddress() {
            navigator.clipboard.writeText('الرياض، المملكة العربية السعودية');
            document.getElementById('copyAddress').style.display = 'block';
            setTimeout(function() {
                document.getElementById('copyAddress').style.display = 'none';
            }, 2000);
        }

        function copyEmail() {
            navigator.clipboard.writeText('yarubsa25@gmail.com');
            document.getElementById('copyEmail').style.display = 'block';
            setTimeout(function() {
                document.getElementById('copyEmail').style.display = 'none';
            }, 2000);
        }

        function copyPhone() {
            navigator.clipboard.writeText('+966 5 0004 4502');
            document.getElementById('copyPhone').style.display = 'block';
            setTimeout(function() {
                document.getElementById('copyPhone').style.display = 'none';
            }, 2000);
        }
    </script>
@endsection()
