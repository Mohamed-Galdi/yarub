@extends('layouts.student')

@section('content')
    @if ($course->content->count() > 0)
        <div class="area-container flex bg-pr-200 w-full min-h-screen">
            {{-- toggle sidebar --}}
            <button id="openButton" class="lg:hidden w-8 p-2   bg-gray-200 ">
                <x-icons.bars class="w-6 h-6" />
            </button>
            <!-- Sidebar -->
            <div id="sidebar"
                class=" z-10 translate-x-full hidden lg:block lg:relative absolute lg:translate-x-0 transision-all duration-300 ease-in-out lg:w-1/5 w-1/2 bg-pr-900 p-4 overflow-y-auto lg:h-[calc(100vh+6px)] h-screen">
                <div class="flex justify-between items-center mt-10">
                    <h2 class="text-xl font-bold mb-4 text-slate-200">محتويات الدورة</h2>
                    <div id="closeButton" class="text-sm text-slate-300 mb-4 cursor-pointer lg:hidden block">
                        <x-icons.x class="w-6 h-6" />
                    </div>
                </div>
                <button data-modal-target="review_modal" data-modal-toggle="review_modal"
                    class="absolute flex justify-center items-center gap-2 w-full top-0 h-10 right-0 p-2 bg-slate-200 hover:bg-indigo-400 hover:font-bold transition-all duration-300 ease-in-out">
                    <p>{{ $review ? 'تعديل التقييم' : 'تقييم الدورة' }}</p>
                    <x-icons.star class="w-6 h-6 text-warning-400" />
                </button>
                <ul>
                    @foreach ($course->content as $index => $content)
                        <li class="mb-4   ">
                            <a href="#"
                                class="content-link   py-3 px-2 rounded-xl max-h-20 overflow-hidden flex items-center gap-2 transition-all duration-100 ease-in-out {{ $loop->first ? 'bg-indigo-500 text-slate-200' : 'bg-pr-200 text-slate-300' }}"
                                data-video="{{ $cloudFrontDomain . $content->url }}">
                                <div
                                    class="index-div lg:w-12 w-8 lg:h-12 h-8 aspect-square rounded-full  text-center border-2 border-slate-100 flex justify-center items-center {{ $loop->first ? 'bg-indigo-600 text-white' : ' bg-pr-300 text-gray-300' }}">
                                    <p class="inline-block align-middle lg:text-lg text-sm">{{ $index + 1 }}</p>
                                </div>
                                <div class="w-5/6 flex justify-start items-center">
                                    <p class="lg:text-lg text-sm">
                                        {{ Str::words($content->title, 8) }}
                                    </p>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
                
            </div>

            <!-- Video Player -->
            <div class="lg:w-4/5 w-[calc(100vw-2rem)] flex justify-end items-start p-4">
                <video id="player" class="plyr-player" controls>
                    <source src="{{ $cloudFrontDomain . $course->content->first()->url }}" type="video/mp4">
                </video>
            </div>
        </div>
    @else
        <div class="w-full min-h-[calc(100vh-6rem)]   bg-gradient-to-tr from-gray-400 to-slate-100 flex flex-col justify-center items-center pt-6">
            <p class="lg:text-5xl text-3xl text-center text-slate-500"> لا يوجد محتوى لهذه الدورة بعد ! </p>
            <img src="{{ asset('assets/images/empty.svg') }}" alt="صورة فارغة" class="lg:w-1/3 w-full" />
        </div>
    @endif

    <!-- Course Review modal -->
    <div id="review_modal" tabindex="-1" aria-hidden="true"
        class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 max-h-full bg-gray-800/70">
        <div class="relative p-4 w-full max-w-md max-h-full">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow  ">
                <!-- Modal header -->
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t ">
                    <h3 class="text-lg font-semibold text-gray-900 ">
                        نشكركم على تقييم الدورة 🙏
                    </h3>
                    <button type="button"
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center "
                        data-modal-toggle="review_modal">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <!-- Modal body -->
                <form action="{{ route('student.courses.rating', $course->id) }}" method="POST"
                    class="flex flex-col gap-4 p-4">
                    @csrf
                    <input type="hidden" name="type" value="course" />
                    <div class="w-full flex justify-center">
                        <x-form.rating-stars :current-val="$review ? $review->rating : 0" />
                    </div>
                    <x-form.textarea-light label="وصف التقييم" name="comment"
                        placeholder="{{ $review ? $review->comment : 'أكتب تقييمك هنا...' }}" />


                    <button type="submit"
                        class="w-full text-white bg-warning-400 hover:bg-warning-500 p-2 rounded-lg mt-4 me-2 flex justify-center items-center gap-2">
                        <p>{{ $review ? 'تعديل التقييم' : 'تقييم الدورة' }}</p>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.plyr.io/3.7.8/plyr.polyfilled.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const videoPlayer = document.getElementById('player');
            const contentLinks = document.querySelectorAll('.content-link');
            const container = document.querySelector('.area-container');
            const indexDivs = document.querySelectorAll('.index-div');

            // Set the initial active state
            contentLinks[0].classList.add('bg-indigo-500', 'text-slate-200', 'border-2',
                'border-white');
            indexDivs[0].classList.add('bg-indigo-600', 'text-white');

            // Add hover effect to non-active links
            contentLinks.forEach(link => {
                link.classList.add('hover:bg-indigo-400');
            });

            contentLinks.forEach((link, index) => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    videoPlayer.src = this.dataset.video;
                    container.scrollIntoView({
                        behavior: 'smooth'
                    });

                    // Remove active state from all links and index divs
                    contentLinks.forEach((l, i) => {
                        l.classList.remove('bg-indigo-500', 'text-slate-200',
                            'border-2', 'border-white');
                        l.classList.add('bg-pr-200', 'text-slate-300',
                            'hover:bg-indigo-400');
                        indexDivs[i].classList.remove('bg-indigo-600', 'text-white');
                        indexDivs[i].classList.add('bg-pr-300', 'text-gray-300');
                    });

                    // Add active state to the clicked link and index div
                    this.classList.add('bg-indigo-500', 'text-slate-200', 'border-2',
                        'border-white');
                    this.classList.remove('bg-pr-200', 'text-slate-300', 'hover:bg-indigo-400');
                    indexDivs[index].classList.add('bg-indigo-600', 'text-white');
                    indexDivs[index].classList.remove('bg-pr-300', 'text-gray-300');
                });
            });
        });
    </script>

    {{-- plyr --}}
    <script>
        const players = Plyr.setup('.plyr-player', {
            autoplay: false,
        });
    </script>

    {{-- Toggle sidebar --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const openButton = document.getElementById('openButton');
            const closeButton = document.getElementById('closeButton');
            const sidebar = document.getElementById('sidebar');

            openButton.addEventListener('click', function() {
                // toggle translate-x-full and hidden
                sidebar.classList.toggle('translate-x-full');
                sidebar.classList.toggle('hidden');
            });

            closeButton.addEventListener('click', function() {
                sidebar.classList.toggle('translate-x-full');
                sidebar.classList.toggle('hidden');
            });

        });
    </script>
@endsection
