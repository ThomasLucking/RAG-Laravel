@props(['documents'])

<aside class="w-72 shrink-0 border-r border-[#262D38] bg-[#0D1117] flex flex-col">
    <div class="p-4 border-b border-[#262D38] flex items-center gap-2">
        <button type="button" id="ingest-open"
            class="flex-1 text-center bg-black text-[#E4E7EB] text-sm font-medium rounded-md py-2.5 border border-[#262D38] hover:bg-[#12161C] transition-colors">
            + New document
        </button>
        <a href="{{ request()->routeIs('search.*') ? route('documents.formulaire') : route('search.index') }}"
            class="shrink-0 flex items-center justify-center size-[42px] rounded-md border border-[#262D38] text-[#C3CAD3] hover:bg-[#12161C] hover:text-[#E4E7EB] transition-colors"
            aria-label="{{ request()->routeIs('search.*') ? 'Back to chat' : 'Full text search' }}"
            title="{{ request()->routeIs('search.*') ? 'Back to chat' : 'Full text search' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-5">
                <circle cx="11" cy="11" r="7" />
                <path d="m21 21-4.3-4.3" />
            </svg>
        </a>
    </div>
    <div class="flex-1 overflow-y-auto px-2 py-3 space-y-1" id="document-list">
        @forelse ($documents as $doc)
            <button type="button" data-slug="{{ $doc['slug'] }}"
                class="document-item w-full flex items-center justify-between gap-2 text-left px-3 py-2 rounded-md text-sm text-[#C3CAD3] hover:bg-[#12161C] hover:text-[#E4E7EB] transition-colors">
                <span class="truncate">{{ $doc['title'] }}</span>
                <span class="document-item-origin shrink-0 rounded-full px-2 py-0.5 text-[10px] font-medium {{ $doc['origin']->badgeClasses() }}">
                    {{ $doc['origin']->label() }}
                </span>
            </button>
        @empty
            <p class="px-3 py-2 text-xs text-[#4A5261]">No documents yet.</p>
        @endforelse
    </div>
</aside>
