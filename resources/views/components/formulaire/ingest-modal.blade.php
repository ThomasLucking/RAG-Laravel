<div id="ingest-modal" class="fixed inset-0 z-50 {{ $errors->any() || session('status') ? 'flex' : 'hidden' }} items-center justify-center bg-black/60 px-4">
    <div class="w-full max-w-xl bg-[#12161C] border border-[#262D38] rounded-lg shadow-2xl shadow-black/40 flex overflow-hidden max-h-[85vh]">
        <div class="w-1 shrink-0 bg-[#3C82C4]"></div>

        <div class="flex-1 p-8 overflow-y-auto">
            <div class="flex items-start justify-between mb-8">
                <div>
                    <h2 class="text-lg font-semibold text-[#E4E7EB]">Ingest document</h2>
                    <p class="mt-1 text-sm text-[#838E9C]">Add a record to the corpus.</p>
                </div>
                <button type="button" id="ingest-close" class="text-[#838E9C] hover:text-[#E4E7EB] text-xl leading-none">&times;</button>
            </div>

            @if (session('status'))
                <div class="mb-6 rounded-md border border-[#2E6B4A] bg-[#12241C] px-3 py-2 text-sm text-[#6BC79A]">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('documents.store') }}" class="space-y-6">
                @csrf
                <div>
                    <label for="title" class="block text-xs text-[#838E9C] mb-1.5">Title</label>
                    <input type="text" id="title" name="title" value="{{ old('title') }}"
                        placeholder="A command-line todo list in Rust"
                        class="w-full rounded-md border border-[#262D38] bg-[#0D1117] px-3 py-2 text-sm text-[#E4E7EB] placeholder-[#4A5261] focus:outline-none focus:ring-2 focus:ring-[#3C82C4]/60 focus:border-[#3C82C4]/60">
                    @error('title')
                        <p class="mt-1.5 text-xs text-[#F06B6B]">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="summary" class="block text-xs text-[#838E9C] mb-1.5">Summary</label>
                    <textarea id="summary" name="summary" rows="2"
                        placeholder="Short description of the document"
                        class="w-full rounded-md border border-[#262D38] bg-[#0D1117] px-3 py-2 text-sm text-[#E4E7EB] placeholder-[#4A5261] resize-none focus:outline-none focus:ring-2 focus:ring-[#3C82C4]/60 focus:border-[#3C82C4]/60">{{ old('summary') }}</textarea>
                    @error('summary')
                        <p class="mt-1.5 text-xs text-[#F06B6B]">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="tags" class="block text-xs text-[#838E9C] mb-1.5">Tags</label>
                        <input type="text" id="tags" name="tags" value="{{ old('tags') }}"
                            placeholder="rust, cli, cargo"
                            class="w-full rounded-md border border-[#262D38] bg-[#0D1117] px-3 py-2 text-sm text-[#E4E7EB] placeholder-[#4A5261] focus:outline-none focus:ring-2 focus:ring-[#3C82C4]/60 focus:border-[#3C82C4]/60">
                        <p class="mt-1.5 text-[11px] text-[#4A5261]">Comma-separated</p>
                        @error('tags')
                            <p class="mt-1.5 text-xs text-[#F06B6B]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="updated" class="block text-xs text-[#838E9C] mb-1.5">Updated date</label>
                        <input type="date" id="updated" name="updated" value="{{ old('updated') }}"
                            class="w-full rounded-md border border-[#262D38] bg-[#0D1117] px-3 py-2 text-sm text-[#E4E7EB] [scheme:dark] focus:outline-none focus:ring-2 focus:ring-[#3C82C4]/60 focus:border-[#3C82C4]/60">
                        @error('updated')
                            <p class="mt-1.5 text-xs text-[#F06B6B]">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="pt-5 border-t border-[#262D38]">
                    <div class="flex items-baseline justify-between mb-1.5">
                        <label for="content" class="block text-xs text-[#838E9C]">Content (Markdown)</label>
                        <span id="word-count" class="font-mono text-[11px] text-[#4A5261]">0 words</span>
                    </div>
                    <textarea id="content" name="content" rows="8"
                        placeholder="## Section title&#10;&#10;Paragraph..."
                        class="w-full rounded-md border border-[#262D38] bg-[#0D1117] px-3 py-2 text-sm text-[#E4E7EB] placeholder-[#4A5261] font-mono leading-relaxed resize-y focus:outline-none focus:ring-2 focus:ring-[#3C82C4]/60 focus:border-[#3C82C4]/60">{{ old('content') }}</textarea>
                    @error('content')
                        <p class="mt-1.5 text-xs text-[#F06B6B]">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                    class="w-full bg-[#3C82C4] text-[#0B0E12] text-sm font-medium rounded-md py-2.5 hover:bg-[#5A9AD6] active:bg-[#2E6BA8] transition-colors focus:outline-none focus:ring-2 focus:ring-[#3C82C4]/60 focus:ring-offset-2 focus:ring-offset-[#12161C]">
                    Add to corpus
                </button>
            </form>
        </div>
    </div>
</div>
