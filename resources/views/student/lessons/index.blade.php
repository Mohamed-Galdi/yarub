@extends('layouts.student')

@section('content')
    <div class="panel-student-content">
        <x-panel.heading title="شروحاتي" />
        <div class="panel-section panel-grid panel-grid--courses">
            @forelse ($lessons as $lesson)
                <x-card.student-lesson :lesson="$lesson" />
            @empty
                <x-panel.card class="panel-grid-full" id="noResultsMessage">
                    <x-panel.empty title="لا توجد شروحات في حسابك بعد" message="تصفح الشروحات المتاحة للاشتراك.">
                        <x-panel.button class="mt-4" :href="route('lessons')" variant="primary">تصفح الشروحات</x-panel.button>
                    </x-panel.empty>
                </x-panel.card>
            @endforelse
        </div>
    </div>
@endsection
