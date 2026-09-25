<x-layout title="Full text search">
    <div class="flex h-screen bg-[#0B0E12]">
        <x-formulaire.sidebar :documents="$documents" />

        <main class="flex-1 min-w-0 flex flex-col">
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
            </div>
        </main>
    </div>
</x-layout>
