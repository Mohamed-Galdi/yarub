<!-- dynamic-tab-navigation.blade.php -->
@props(['tabs' => []])

<div x-data="{ selectedTab: '{{ $tabs[0]['id'] ?? '' }}' }" class="w-full">
    <div
        @keydown.right.prevent="$focus.wrap().next()"
        @keydown.left.prevent="$focus.wrap().previous()"
        class="flex gap-2 overflow-x-auto border-b border-slate-300 w-full"
        role="tablist"
        aria-label="tab options"
    >
        @foreach ($tabs as $tab)
            <button
                @click="selectedTab = '{{ $tab['id'] }}'"
                :aria-selected="selectedTab === '{{ $tab['id'] }}'"
                :tabindex="selectedTab === '{{ $tab['id'] }}' ? '0' : '-1'"
                :class="selectedTab === '{{ $tab['id'] }}' ?
                    'font-bold text-blue-700 border-b-2 border-blue-700' :
                    'text-slate-700 font-medium hover:border-b-2 hover:border-b-slate-800 hover:text-black'"
                class="flex h-min items-center gap-2 px-4 py-2 text-sm flex-grow "
                type="button"
                role="tab"
                aria-controls="tabpanel{{ $tab['id'] }}"
            >
                <div class="text-xl flex items-center justify-start gap-2">
                    <p>{{ $tab['label'] }}</p>
                    <x-dynamic-component :component="$tab['icon']" class="w-5 h-5 text-blue" />
                    @if ($tab['count'] || $tab['count'] === 0)
                    <span
                        :class="selectedTab === '{{ $tab['id'] }}' ?
                            'border-indigo-500' :
                            'border-gray-500'"
                        class="border-2 p-1 rounded-full min-w-6 text-xs"
                    >
                        {{ $tab['count'] ?? '0' }}
                    </span>
                    @endif
                </div>
            </button>
        @endforeach
    </div>
    
    <div class="p-2">
        @foreach ($tabs as $tab)
            <div
                x-show="selectedTab === '{{ $tab['id'] }}'"
                id="tabpanel{{ $tab['id'] }}"
                role="tabpanel"
                aria-label="{{ $tab['id'] }}"
            >
                <div class="mt-4 space-y-6">
                    {{ ${$tab['id']} ?? '' }}
                </div>
            </div>
        @endforeach
    </div>
</div>