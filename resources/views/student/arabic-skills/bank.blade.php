@extends('layouts.student')
@section('content')
@php($bank = $entry['bank'])
<section data-qudrat-student data-arabic-root>
    @include('student.arabic-skills._header', ['heading' => $bank['title'], 'description' => $bank['subtitle']])
    <div class="practice-sets-heading"><h2>مجموعات التدريب</h2><span>{{ count($sets) }} مجموعة</span></div>
    <div class="practice-set-grid">
        @forelse($sets as $set)
            <x-practice-set-card :set="$set" :start-url="route('student.arabic-skills.start', $bank['id'])" show-route="student.arabic-skills.show" results-route="student.arabic-skills.results" :context="$bank['title']" />
        @empty<p class="qudrat-empty">لا توجد مجموعات متاحة حاليًا.</p>@endforelse
    </div>
</section>
@endsection
