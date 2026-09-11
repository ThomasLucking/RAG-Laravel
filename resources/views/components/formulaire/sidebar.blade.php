@props(['documents'])

<aside class="w-72 shrink-0 border-r border-[#262D38] bg-[#0D1117] flex flex-col">
    <div class="p-4 border-b border-[#262D38]">
        <button type="button" id="ingest-open"
            class="block w-full text-center bg-black text-[#E4E7EB] text-sm font-medium rounded-md py-2.5 border border-[#262D38] hover:bg-[#12161C] transition-colors">
            + New document
        </button>
    </div>
    <div class="flex-1 overflow-y-auto px-2 py-3 space-y-1" id="document-list">
        @forelse ($documents as $doc)
            <button type="button" data-slug="{{ $doc['slug'] }}"
                class="document-item w-full text-left px-3 py-2 rounded-md text-sm text-[#C3CAD3] hover:bg-[#12161C] hover:text-[#E4E7EB] transition-colors truncate">
                {{ $doc['title'] }}
            </button>
        @empty
            <p class="px-3 py-2 text-xs text-[#4A5261]">No documents yet.</p>
        @endforelse
    </div>
</aside>
