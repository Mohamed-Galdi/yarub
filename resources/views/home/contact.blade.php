@extends('layouts.guest')
@section('content')
    {{-- ///////////////////////////// Hero section /////////////////////////////// --}}
    <div style="background-image: url('./assets/images/contact-bg-2.jpg')"
        class=" h-[40rem]  bg-no-repeat bg-cover md:bg-buttom bg-right flex px-6 md:px-0">
        <div class="max-w-screen-xl mx-auto flex justify-end items-center w-full ">
            {{-- <x-errors :errors="$errors" /> --}}
            <div class=" w-[480px] h-[500px] rounded-xl p-8  backdrop-blur-md bg-white/20  ">
                <form action="{{ route('admin.guest_messages.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="source" value="contact_page">
                    <div class="w-full lg:flex gap-6">
                        <div class="relative z-0 w-full mb-5 group">
                            <input type="text" name="name" id="floating_name"
                                class="block py-3.5 px-5 w-full text-lg text-gray-900 bg-transparent border-0 border-b-2 border-pr-200 focus:outline-none focus:ring-0 focus:border-pr-500 peer"
                                placeholder=" " required />

                            <label for="floating_name"
                                class="peer-focus:font-medium absolute font-sec  text-lg text-pr-500  duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto peer-focus:soft_black peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">الإسم
                                الكامل</label>
                        </div>

                    </div>
                    <div class="w-full lg:flex gap-6">
                        <div class="relative z-0 w-full mb-5 group">
                            <input type="email" name="email" id="floating_email"
                                class="block py-3.5 px-5 w-full text-lg text-gray-900 bg-transparent border-0 border-b-2 border-pr-200 focus:outline-none focus:ring-0 focus:border-pr-500 peer"
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
                    <x-btn.scale-light type="submit" class="flex gap-8 w-full">
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
    {{-- ///////////////////////////// Contact infos section /////////////////////////////// --}}
    <div class="bg-pr-500 py-12 px-8 md:px-4 lg:px-0 ">
        <div class="mx-auto max-w-screen-xl">

            <h2 class="text-gray-200 text-center text-5xl mb-12">أو تواصل معنا مباشرة من هنا </h2>
            <div class="flex md:flex-row flex-col jusfify-between items-center gap-8 ">
                <div
                    class="text-white flex flex-col md:items-start items-center md:text-start text-center gap-4 py-4 md:pe-12 ps-4 md:w-1/3 w-full">
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
                    class="text-white flex flex-col md:items-start items-center md:text-start text-center  gap-4 py-4 md:pe-12 ps-4 md:w-1/3 w-full">
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
                    class="text-white flex flex-col md:items-start items-center md:text-start text-center  gap-4 py-4 md:pe-12 ps-4 md:w-1/3 w-full">
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
                    <p class="text-lg font-nitaqat">جدة، المملكة العربية السعودية</p>

                </div>
            </div>
        </div>

    </div>

    {{-- //////////////////////////// Our social media acounts /////////////////////////////// --}}
    <div dir="ltr" class="overflow-hidden bg-gray-100 py-8 px-8 md:px-4 lg:px-0">
        <div class="max-w-[85rem] mx-auto px-4 sm:px-6 lg:px-8 md:py-20 py-12">
            <div class="relative mx-auto max-w-4xl grid space-y-12 sm:space-y-10">

                <h2 class="text-4xl mx-auto text-center  ">لا تنسى متابعتنا عبر حساباتنا الرسمية</h2>
                <div class="bg-white w-full h-auto py-8 flex items-center justify-center gap-4 flex-wrap">
                    {{-- Icons Sourse: https://pagedone.io/docs/social-media-icons --}}
                    {{-- instagram --}}
                    <a href="https://www.instagram.com/yarub_ar" target="_blank"><button
                            class="w-20 h-20 flex items-center justify-center rounded-full relative overflow-hidden bg-slate-200 shadow-md shadow-gray-200 group transition-all duration-500">
                            <x-icons.instagram
                                class="fill-gray-900 relative z-10 transition-all duration-300 group-hover:text-white w-10 h-10" />
                            <div
                                class="absolute top-full left-0 w-full h-full rounded-full bg-gradient-to-bl from-purple-500 via-pink-500 to-yellow-500 z-0 transition-all duration-500 group-hover:top-0">
                            </div>
                        </button>
                    </a>

                    {{-- tiktok --}}
                    <a href="https://www.tiktok.com/@yarub_ar" target="_blank"><button
                            class="w-20 h-20 flex items-center justify-center rounded-full relative overflow-hidden bg-slate-200 shadow-md shadow-gray-200 group transition-all duration-500">
                            <x-icons.tiktok
                                class="fill-gray-900 relative z-10 transition-all duration-300 group-hover:text-white w-10 h-10" />
                            <div
                                class="absolute top-full left-0 w-full h-full rounded-full bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-black via-black to-red-600 z-0 transition-all duration-500 group-hover:top-0">
                            </div>
                        </button></a>

                    {{-- whatsapp --}}
                    <a href="https://wa.me/0539867197" target="_blank">
                        <button
                            class="w-20 h-20 flex items-center justify-center rounded-full relative overflow-hidden bg-slate-200 shadow-md shadow-gray-200 group transition-all duration-500">
                            <x-icons.whatsapp
                                class="fill-gray-900 relative z-10 transition-all duration-300 group-hover:text-white w-10 h-10" />
                            <div
                                class="absolute top-full left-0 w-full h-full rounded-full bg-green-400 z-0 transition-all duration-500 group-hover:top-0">
                            </div>
                        </button></a>

                    {{-- snapchat --}}
                    <a href="https://www.snapchat.com/add/yarub_ar" target="_blank">
                        <button
                            class="w-20 h-20 flex items-center justify-center rounded-full relative overflow-hidden bg-slate-200 shadow-md shadow-gray-200 group transition-all duration-500">
                            <x-icons.snapchat
                                class="fill-gray-900 relative z-10 transition-all duration-300 group-hover:text-white w-10 h-10" />
                            <div
                                class="absolute top-full left-0 w-full h-full rounded-full bg-warning-300 z-0 transition-all duration-500 group-hover:top-0">
                            </div>
                        </button></a>
                </div>
                <!-- SVG Element -->
                <div class="scale-50 md:scale-100 absolute md:top-1/4 top-1/4 start-8 md:start-0 transform -translate-y-2/4 -translate-x-40 md:block lg:-translate-x-80"
                    aria-hidden="true">
                    <svg class="w-52 h-auto" width="717" height="653" viewBox="0 0 717 653" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M170.176 228.357C177.176 230.924 184.932 227.329 187.498 220.329C190.064 213.329 186.47 205.574 179.47 203.007L170.176 228.357ZM98.6819 71.4156L85.9724 66.8638L85.8472 67.2136L85.7413 67.5698L98.6819 71.4156ZM336.169 77.9736L328.106 88.801L328.288 88.9365L328.475 89.0659L336.169 77.9736ZM616.192 128.685C620.658 122.715 619.439 114.254 613.469 109.788L516.183 37.0035C510.213 32.5371 501.753 33.756 497.286 39.726C492.82 45.696 494.039 54.1563 500.009 58.6227L586.485 123.32L521.788 209.797C517.322 215.767 518.541 224.227 524.511 228.694C530.481 233.16 538.941 231.941 543.407 225.971L616.192 128.685ZM174.823 215.682C179.47 203.007 179.475 203.009 179.48 203.011C179.482 203.012 179.486 203.013 179.489 203.014C179.493 203.016 179.496 203.017 179.498 203.018C179.501 203.019 179.498 203.018 179.488 203.014C179.469 203.007 179.425 202.99 179.357 202.964C179.222 202.912 178.991 202.822 178.673 202.694C178.035 202.437 177.047 202.026 175.768 201.456C173.206 200.314 169.498 198.543 165.106 196.099C156.27 191.182 144.942 183.693 134.609 173.352C114.397 153.124 97.7311 122.004 111.623 75.2614L85.7413 67.5698C68.4512 125.748 89.856 166.762 115.51 192.436C128.11 205.047 141.663 213.953 151.976 219.692C157.158 222.575 161.591 224.698 164.777 226.118C166.371 226.828 167.659 227.365 168.578 227.736C169.038 227.921 169.406 228.065 169.675 228.168C169.809 228.22 169.919 228.261 170.002 228.293C170.044 228.309 170.08 228.322 170.109 228.333C170.123 228.338 170.136 228.343 170.147 228.347C170.153 228.349 170.16 228.352 170.163 228.353C170.17 228.355 170.176 228.357 174.823 215.682ZM111.391 75.9674C118.596 55.8511 137.372 33.9214 170.517 28.6833C204.135 23.3705 255.531 34.7533 328.106 88.801L344.233 67.1462C268.876 11.0269 210.14 -4.91361 166.303 2.01428C121.993 9.01681 95.9904 38.8917 85.9724 66.8638L111.391 75.9674ZM328.475 89.0659C398.364 137.549 474.018 153.163 607.307 133.96L603.457 107.236C474.34 125.837 406.316 110.204 343.864 66.8813L328.475 89.0659Z"
                            fill="currentColor" class="fill-gray-800" />
                        <path
                            d="M17.863 238.22C10.4785 237.191 3.6581 242.344 2.62917 249.728C1.60024 257.113 6.75246 263.933 14.137 264.962L17.863 238.22ZM117.548 265.74L119.421 252.371L119.411 252.37L117.548 265.74ZM120.011 466.653L132.605 471.516L132.747 471.147L132.868 470.771L120.011 466.653ZM285.991 553.767C291.813 549.109 292.756 540.613 288.098 534.792L212.193 439.92C207.536 434.098 199.04 433.154 193.218 437.812C187.396 442.47 186.453 450.965 191.111 456.787L258.582 541.118L174.251 608.589C168.429 613.247 167.486 621.742 172.143 627.564C176.801 633.386 185.297 634.329 191.119 629.672L285.991 553.767ZM14.137 264.962L115.685 279.111L119.411 252.37L17.863 238.22L14.137 264.962ZM115.675 279.11C124.838 280.393 137.255 284.582 145.467 291.97C149.386 295.495 152.093 299.505 153.39 304.121C154.673 308.691 154.864 314.873 152.117 323.271L177.779 331.665C181.924 318.993 182.328 307.301 179.383 296.818C176.451 286.381 170.485 278.159 163.524 271.897C149.977 259.71 131.801 254.105 119.421 252.371L115.675 279.11ZM152.117 323.271C138.318 365.454 116.39 433.697 107.154 462.535L132.868 470.771C142.103 441.936 164.009 373.762 177.779 331.665L152.117 323.271ZM107.417 461.79C103.048 473.105 100.107 491.199 107.229 508.197C114.878 526.454 132.585 539.935 162.404 543.488L165.599 516.678C143.043 513.99 135.175 505.027 132.132 497.764C128.562 489.244 129.814 478.743 132.605 471.516L107.417 461.79ZM162.404 543.488C214.816 549.734 260.003 554.859 276.067 556.643L279.047 529.808C263.054 528.032 217.939 522.915 165.599 516.678L162.404 543.488Z"
                            fill="currentColor" class="fill-orange-500" />
                        <path
                            d="M229.298 165.61C225.217 159.371 216.85 157.621 210.61 161.702C204.371 165.783 202.621 174.15 206.702 180.39L229.298 165.61ZM703.921 410.871C711.364 410.433 717.042 404.045 716.605 396.602L709.47 275.311C709.032 267.868 702.643 262.189 695.2 262.627C687.757 263.065 682.079 269.454 682.516 276.897L688.858 384.71L581.045 391.052C573.602 391.49 567.923 397.879 568.361 405.322C568.799 412.765 575.187 418.444 582.63 418.006L703.921 410.871ZM206.702 180.39C239.898 231.14 343.567 329.577 496.595 322.758L495.394 295.785C354.802 302.049 259.09 211.158 229.298 165.61L206.702 180.39ZM496.595 322.758C567.523 319.598 610.272 335.61 637.959 353.957C651.944 363.225 662.493 373.355 671.17 382.695C675.584 387.447 679.351 391.81 683.115 396.047C686.719 400.103 690.432 404.172 694.159 407.484L712.097 387.304C709.691 385.166 706.92 382.189 703.298 378.113C699.837 374.217 695.636 369.362 690.951 364.319C681.43 354.07 669.255 342.306 652.874 331.451C619.829 309.553 571.276 292.404 495.394 295.785L496.595 322.758Z"
                            fill="currentColor" class="fill-cyan-500" />
                    </svg>
                </div>
                <!-- End SVG Element -->

                <!-- SVG Element -->
                <div class="scale-50 md:scale-100 absolute md:top-1/3 top-1/4 end-0 transform -translate-y-2/4 translate-x-40 md:block lg:translate-x-80"
                    aria-hidden="true">
                    <svg class="w-72 h-auto" width="1115" height="636" viewBox="0 0 1115 636" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M0.990203 279.321C-1.11035 287.334 3.68307 295.534 11.6966 297.634L142.285 331.865C150.298 333.965 158.497 329.172 160.598 321.158C162.699 313.145 157.905 304.946 149.892 302.845L33.8132 272.418L64.2403 156.339C66.3409 148.326 61.5475 140.127 53.5339 138.026C45.5204 135.926 37.3213 140.719 35.2207 148.733L0.990203 279.321ZM424.31 252.289C431.581 256.26 440.694 253.585 444.664 246.314C448.635 239.044 445.961 229.931 438.69 225.96L424.31 252.289ZM23.0706 296.074C72.7581 267.025 123.056 230.059 187.043 212.864C249.583 196.057 325.63 198.393 424.31 252.289L438.69 225.96C333.77 168.656 249.817 164.929 179.257 183.892C110.144 202.465 54.2419 243.099 7.92943 270.175L23.0706 296.074Z"
                            fill="currentColor" class="fill-orange-500" />
                        <path
                            d="M451.609 382.417C446.219 388.708 446.95 398.178 453.241 403.567L555.763 491.398C562.054 496.788 571.524 496.057 576.913 489.766C582.303 483.474 581.572 474.005 575.281 468.615L484.15 390.544L562.222 299.413C567.612 293.122 566.881 283.652 560.59 278.263C554.299 272.873 544.829 273.604 539.44 279.895L451.609 382.417ZM837.202 559.655C841.706 566.608 850.994 568.593 857.947 564.09C864.9 559.586 866.885 550.298 862.381 543.345L837.202 559.655ZM464.154 407.131C508.387 403.718 570.802 395.25 638.136 410.928C704.591 426.401 776.318 465.66 837.202 559.655L862.381 543.345C797.144 442.631 718.724 398.89 644.939 381.709C572.033 364.734 504.114 373.958 461.846 377.22L464.154 407.131Z"
                            fill="currentColor" class="fill-cyan-500" />
                        <path
                            d="M447.448 0.194357C439.203 -0.605554 431.87 5.43034 431.07 13.6759L418.035 148.045C417.235 156.291 423.271 163.623 431.516 164.423C439.762 165.223 447.095 159.187 447.895 150.942L459.482 31.5025L578.921 43.0895C587.166 43.8894 594.499 37.8535 595.299 29.6079C596.099 21.3624 590.063 14.0296 581.818 13.2297L447.448 0.194357ZM1086.03 431.727C1089.68 439.166 1098.66 442.239 1106.1 438.593C1113.54 434.946 1116.62 425.96 1112.97 418.521L1086.03 431.727ZM434.419 24.6572C449.463 42.934 474.586 81.0463 521.375 116.908C568.556 153.07 637.546 187.063 742.018 200.993L745.982 171.256C646.454 157.985 582.444 125.917 539.625 93.0974C496.414 59.978 474.537 26.1903 457.581 5.59138L434.419 24.6572ZM742.018 200.993C939.862 227.372 1054.15 366.703 1086.03 431.727L1112.97 418.521C1077.85 346.879 956.138 199.277 745.982 171.256L742.018 200.993Z"
                            fill="currentColor" class="fill-gray-800" />
                    </svg>
                </div>
                <!-- End SVG Element -->
            </div>
        </div>
    </div>
    <!-- End Hero -->

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
