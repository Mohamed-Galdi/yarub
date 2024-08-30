@extends('layouts.admin')
@section('content')
    <div class="flex flex-col justify-start items-start min-h-screen space-y-4">
        <div class="w-full flex justify-end items-center gap-2">
            <a href="{{ route('admin.students.deleted') }}"
                class="flex items-center gap-3 p-3 me-1 rounded-lg bg-gray-700 text-white border border-white hover:bg-gray-900 transition-all duration-300 ease-in-out">
                <p> قائمة المشتركين المحظورين ( <span class="text-xl font-hacen">{{ $trashedCount }}</span> )</p>
                <x-icons.trash class="w-5 h-5" />
            </a>
        </div>
        <div class="bg-white rounded-lg border border-gray-300 p-4 w-full">
            <livewire:students-table />
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const footer = document.querySelector('#pg-footer');
            if (footer) {
                // First div modifications
                const firstDiv = footer.children[0];
                if (firstDiv) {
                    firstDiv.setAttribute('dir', 'ltr');

                    const select = firstDiv.querySelector('select');
                    if (select) {
                        select.classList.remove('w-auto');
                        select.classList.add('w-12');
                    }
                }

                // Second div modifications
                const secondDiv = footer.children[1];
                if (secondDiv) {
                    const innerDiv = secondDiv.children[0].children[0].children[0];
                    if (innerDiv) {
                        innerDiv.classList.remove('mr-3');
                        innerDiv.classList.add('ml-3');
                    }

                    const nav = secondDiv.children[0].children[0].children[1];
                    if (nav) {
                        console.log(nav);
                        nav.setAttribute('dir', 'ltr');
                    }
                }
            }
        });
    </script>
@endsection()
