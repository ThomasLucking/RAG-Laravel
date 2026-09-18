<x-layout title="New document">
    <div class="flex h-screen bg-[#0B0E12]">
        <x-formulaire.sidebar :documents="$documents" />
        <x-formulaire.chat-panel />
    </div>

    <x-formulaire.ingest-modal />
    <x-formulaire.document-modal />
</x-layout>
