<x-layout title="Full text search">
    <div class="flex h-screen bg-[#0B0E12]">
        <x-formulaire.sidebar :documents="$documents" />

        <main class="flex-1 min-w-0 flex flex-col overflow-y-auto">
            <div class="mx-auto w-full max-w-3xl px-4 py-10">
                <h1 class="text-2xl font-semibold text-[#E4E7EB]">Full text search</h1>
                <p class="mt-2 text-sm text-[#838E9C]">Search across the ingested documents.</p>

                <form method="GET" action="{{ route('search.index') }}" class="mt-6">
                    <div class="flex items-center gap-2 rounded-3xl border border-[#262D38] bg-[#12161C] pl-5 pr-2 py-2 shadow-lg shadow-black/30 focus-within:border-[#3C82C4]/60 transition-colors">
                        <input type="text" name="query" value="{{ request('query') }}" placeholder="Search documents"
                            class="flex-1 bg-transparent py-1.5 text-sm leading-6 text-[#E4E7EB] placeholder-[#4A5261] focus:outline-none">
                        <button type="submit"
                            class="shrink-0 rounded-full bg-[#3C82C4] px-4 py-2 text-sm font-medium text-[#0B0E12] hover:bg-[#5A9AD6] transition-colors">
                            Search
                        </button>
                    </div>
                </form>

                @if (request()->filled('query'))
                    <p class="mt-6 text-xs text-[#838E9C]">
                        {{ $results->count() }} {{ Str::plural('result', $results->count()) }} for "{{ request('query') }}"
                    </p>

                    <ul class="mt-3 space-y-3">
                        @forelse ($results as $chunk)
                            <li>
                                <button type="button" data-slug="{{ $chunk->document?->slug }}"
                                    class="search-result block w-full text-left rounded-xl border border-[#262D38] bg-[#12161C] p-4 hover:border-[#3C82C4]/60 transition-colors">
                                    <span class="flex items-start justify-between gap-3">
                                        <span class="block min-w-0">
                                            <span class="block truncate text-sm font-medium text-[#E4E7EB]">{{ $chunk->document?->title }}</span>
                                            <span class="mt-0.5 block truncate text-xs text-[#838E9C]">{{ $chunk->headers }}</span>
                                        </span>
                                        <span class="shrink-0 rounded-full bg-[#3C82C4]/15 px-2 py-0.5 text-[10px] font-medium text-[#5A9AD6]">
                                            {{ number_format($chunk->rank, 3) }}
                                        </span>
                                    </span>
                                    <span class="mt-3 block text-sm leading-6 text-[#C3CAD3]">{{ Str::limit($chunk->chunk_content, 300) }}</span>
                                </button>
                            </li>
                        @empty
                            <li class="rounded-xl border border-dashed border-[#262D38] p-6 text-center text-sm text-[#4A5261]">
                                No chunks match this query.
                            </li>
                        @endforelse
                    </ul>
                @endif
            </div>
        </main>
    </div>

    <x-formulaire.ingest-modal />
    <x-formulaire.document-modal />
</x-layout>
