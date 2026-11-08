@extends('layouts.student')

@section('content')
    <div class="panel-student-content">
        <x-panel.heading title="دوراتي" />
        <div class="panel-section panel-grid panel-learning-grid">
            @forelse ($courses as $course)
                <x-card.student-course :course="$course" />
            @empty
                <x-panel.card class="panel-grid-full" id="noResultsMessage">
                    <x-panel.empty title="لا توجد دورات في حسابك بعد" message="تصفح الدورات المتاحة واختر ما يناسبك.">
                        <x-panel.button class="mt-4" :href="route('courses')" variant="primary">تصفح الدورات</x-panel.button>
                    </x-panel.empty>
                </x-panel.card>
            @endforelse
        </div>
    </div>
@endsection
