<main class="flex-1 min-w-0 flex flex-col">
    <div id="chat-thread" class="flex-1 overflow-y-auto">
        <div class="mx-auto w-full max-w-3xl min-h-full flex flex-col px-4 py-10">
            <div id="chat-empty" class="m-auto text-center">
                <h1 class="text-2xl font-semibold text-[#E4E7EB]">Ask the corpus</h1>
                <p class="mt-2 text-sm text-[#838E9C]">Query the ingested documents.</p>
            </div>
            <div id="chat-messages" class="hidden space-y-4"></div>
        </div>
    </div>

    <div class="px-4 pb-6 pt-2">
        <form action="{{ route('user.query') }}" method="POST" id="chat-form" class="mx-auto w-full max-w-3xl">
            @csrf
            <div class="flex items-end gap-2 rounded-3xl border border-[#262D38] bg-[#12161C] pl-5 pr-2 py-2 shadow-lg shadow-black/30 focus-within:border-[#3C82C4]/60 transition-colors">
                <textarea id="chat-input" name="query" rows="1"
                    placeholder="Ask anything about your documents"
                    class="flex-1 max-h-48 resize-none bg-transparent py-1.5 text-sm leading-6 text-[#E4E7EB] placeholder-[#4A5261] focus:outline-none"></textarea>
                <button type="submit" id="chat-send" disabled aria-label="Send"
                    class="size-9 shrink-0 flex items-center justify-center rounded-full bg-[#3C82C4] text-[#0B0E12] hover:bg-[#5A9AD6] disabled:bg-[#262D38] disabled:text-[#4A5261] transition-colors">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" class="size-4">
                        <path d="M12 19V5M5 12l7-7 7 7" />
                    </svg>
                </button>
            </div>
            <p class="mt-2 text-center text-[11px] text-[#4A5261]">Enter to send, Shift + Enter for a new line</p>
        </form>
    </div>
</main>
