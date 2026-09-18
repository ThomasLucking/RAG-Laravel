<?php

namespace App\Console\Commands;

use App\Models\Chunk;
use App\Services\EmbeddingService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:store-embeddings')]
#[Description('Command description')]
class StoreEmbeddings extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $chunks = Chunk::query()
            ->whereNull('embeddings')
            ->whereHas('document')
            ->with('document:id,title')
            ->select(['id', 'document_id', 'headers', 'chunk_content'])
            ->get();

        foreach ($chunks as $chunk) {
            $text = "Document: {$chunk->document->title}\n Sections: {$chunk->headers} \n Content: {$chunk->chunk_content}";

            $response = EmbeddingService::embeddding($text);

            $chunk->update(['embeddings' => $response->first()]);
        }
    }
}
