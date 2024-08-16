@extends('layouts.student')

@section('content')
<div class="flex">
    <!-- Sidebar -->
    <div class="w-1/4 bg-gray-100 p-4 overflow-y-auto h-screen">
        <h2 class="text-xl font-bold mb-4">Course Content</h2>
        <ul>
            @foreach ($course->content as $index => $content)
                <li class="mb-2">
                    <a href="#" class="content-link @if($loop->first) font-bold @endif" data-video="{{ asset('storage/'.$content->url) }}">
                        {{ $index + 1 }}. {{ $content->title }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>

    <!-- Video Player -->
    <div class="w-3/4 p-4">
        <video id="videoPlayer" src="{{ asset('storage/'.$course->content->first()->url) }}" controls class="w-full">
            <p>Your browser does not support the video tag.</p>
        </video>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const videoPlayer = document.getElementById('videoPlayer');
    const contentLinks = document.querySelectorAll('.content-link');

    contentLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            videoPlayer.src = this.dataset.video;
            videoPlayer.play();

            // Remove bold from all links and add to clicked link
            contentLinks.forEach(l => l.classList.remove('font-bold'));
            this.classList.add('font-bold');
        });
    });
});
</script>
@endsection