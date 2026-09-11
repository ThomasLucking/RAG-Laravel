<div id="document-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 px-4">
    <div class="w-full max-w-xl bg-[#12161C] border border-[#262D38] rounded-lg shadow-2xl shadow-black/40 flex overflow-hidden max-h-[85vh]">
        <div class="w-1 shrink-0 bg-[#3C82C4]"></div>
        <div class="flex-1 p-8 overflow-y-auto">
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h2 id="modal-title-display" class="text-lg font-semibold text-[#E4E7EB]"></h2>
                    <p class="mt-1 text-sm text-[#838E9C]">Document details.</p>
                </div>
                <button type="button" id="modal-close" class="text-[#838E9C] hover:text-[#E4E7EB] text-xl leading-none">&times;</button>
            </div>

            <div id="modal-error" class="hidden mb-4 rounded-md border border-[#6B2E2E] bg-[#241212] px-3 py-2 text-sm text-[#F06B6B]"></div>
            <div id="modal-success" class="hidden mb-4 rounded-md border border-[#2E6B4A] bg-[#12241C] px-3 py-2 text-sm text-[#6BC79A]">Saved and re-ingested.</div>

            <form id="modal-form" class="space-y-6">
                <div>
                    <label class="block text-xs text-[#838E9C] mb-1.5">Title</label>
                    <input type="text" name="title" disabled
                        class="modal-field w-full rounded-md border border-[#262D38] bg-[#0D1117] px-3 py-2 text-sm text-[#E4E7EB] disabled:opacity-60 focus:outline-none focus:ring-2 focus:ring-[#3C82C4]/60 focus:border-[#3C82C4]/60">
                    <p class="modal-field-error mt-1.5 text-xs text-[#F06B6B]" data-field="title"></p>
                </div>

                <div>
                    <label class="block text-xs text-[#838E9C] mb-1.5">Summary</label>
                    <textarea name="summary" rows="2" disabled
                        class="modal-field w-full rounded-md border border-[#262D38] bg-[#0D1117] px-3 py-2 text-sm text-[#E4E7EB] resize-none disabled:opacity-60 focus:outline-none focus:ring-2 focus:ring-[#3C82C4]/60 focus:border-[#3C82C4]/60"></textarea>
                    <p class="modal-field-error mt-1.5 text-xs text-[#F06B6B]" data-field="summary"></p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-[#838E9C] mb-1.5">Tags</label>
                        <input type="text" name="tags" disabled
                            class="modal-field w-full rounded-md border border-[#262D38] bg-[#0D1117] px-3 py-2 text-sm text-[#E4E7EB] disabled:opacity-60 focus:outline-none focus:ring-2 focus:ring-[#3C82C4]/60 focus:border-[#3C82C4]/60">
                        <p class="mt-1.5 text-[11px] text-[#4A5261]">Comma-separated</p>
                        <p class="modal-field-error mt-1.5 text-xs text-[#F06B6B]" data-field="tags"></p>
                    </div>
                    <div>
                        <label class="block text-xs text-[#838E9C] mb-1.5">Updated date</label>
                        <input type="date" name="updated" disabled
                            class="modal-field w-full rounded-md border border-[#262D38] bg-[#0D1117] px-3 py-2 text-sm text-[#E4E7EB] [scheme:dark] disabled:opacity-60 focus:outline-none focus:ring-2 focus:ring-[#3C82C4]/60 focus:border-[#3C82C4]/60">
                        <p class="modal-field-error mt-1.5 text-xs text-[#F06B6B]" data-field="updated"></p>
                    </div>
                </div>

                <div class="pt-5 border-t border-[#262D38]">
                    <label class="block text-xs text-[#838E9C] mb-1.5">Content (Markdown)</label>
                    <textarea name="content" rows="10" disabled
                        class="modal-field w-full rounded-md border border-[#262D38] bg-[#0D1117] px-3 py-2 text-sm text-[#E4E7EB] font-mono leading-relaxed resize-y disabled:opacity-60 focus:outline-none focus:ring-2 focus:ring-[#3C82C4]/60 focus:border-[#3C82C4]/60"></textarea>
                    <p class="modal-field-error mt-1.5 text-xs text-[#F06B6B]" data-field="content"></p>
                </div>

                <div class="flex gap-3">
                    <button type="button" id="modal-edit-toggle"
                        class="flex-1 bg-[#12161C] text-[#E4E7EB] text-sm font-medium rounded-md py-2.5 border border-[#262D38] hover:bg-[#1A1F27] transition-colors">
                        Edit
                    </button>
                    <button type="submit" id="modal-save" disabled
                        class="hidden flex-1 bg-[#3C82C4] text-[#0B0E12] text-sm font-medium rounded-md py-2.5 hover:bg-[#5A9AD6] active:bg-[#2E6BA8] transition-colors disabled:opacity-60">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
