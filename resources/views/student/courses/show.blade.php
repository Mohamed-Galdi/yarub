@extends('layouts.student')

@section('content')
    @if ($course->content->count() > 0)
        <div class="container flex bg-pr-200">
            <!-- Sidebar -->
            <div class="w-1/5 bg-indigo-900 p-4 overflow-y-auto h-screen">
                <h2 class="text-xl font-bold mb-4 text-slate-200">محتويات الدورة</h2>
                <ul>
                    @foreach ($course->content as $index => $content)
                        <li class="mb-4 bg-pr-200 hover:bg-indigo-400 py-3 px-2 rounded-xl ">
                            <a href="#" class="content-link flex gap-2 @if ($loop->first) font-bold @endif"
                                data-video="{{ $cloudFrontDomain . $content->url }}">
                                <div class="w-1/6 aspect-square h-full bg-indigo-800 rounded-full text-white text-center border-2 border-slate-100 flex justify-center items-center">
                                   <p class="inline-block align-middle">{{ $index + 1 }}</p> 
                                </div>
                                <div class="w-5/6 flex justify-start items-center">
                                    <p>
                                        {{ $content->title }}
                                    </p>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Video Player -->
            <div class="w-4/5 p-4">
                <video id="player" class="plyr-player" controls>
                    <source src="{{ $cloudFrontDomain . $course->content->first()->url }}" type="video/mp4">
                </video>
            </div>
        </div>
    @else
        <div class="w-full min-h-[calc(100vh-6rem)]  bg-pr-200 flex flex-col justify-center items-center pt-6">
            <p class="text-5xl text-center text-slate-300"> لا يوجد محتوى لهذه الدورة بعد ! </p>
            <img src="{{ asset('assets/images/empty.svg') }}" alt="صورة فارغة" class="w-1/3" />
        </div>
    @endif

    <script src="https://cdn.plyr.io/3.7.8/plyr.polyfilled.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const videoPlayer = document.getElementById('player');
            const contentLinks = document.querySelectorAll('.content-link');
            const container = document.querySelector('.container');

            contentLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    videoPlayer.src = this.dataset.video;
                    // videoPlayer.play();
                    
                    container.scrollIntoView({ behavior: 'smooth'});

                    // Remove bold from all links and add to clicked link
                    contentLinks.forEach(l => l.classList.remove('font-bold text-red-500'));
                    this.classList.add('font-bold text-red-500');
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
@endsection
