<?php

namespace App\Console\Commands;

use App\Models\Chunk;
use App\Services\EmbeddingService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

#[Signature('app:store-embeddings')]
#[Description('Generate and store a vector for every chunk that does not have one yet; failed chunks are skipped and picked up on the next run.')]
class StoreEmbeddings extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $embedded = 0;
        $failed = 0;

        $chunks = Chunk::query()
            ->whereNull('embeddings')
            ->whereHas('document')
            ->with('document:id,title')
            ->select(['id', 'document_id', 'headers', 'chunk_content'])
            ->lazyById();

        foreach ($chunks as $chunk) {
            try {
                $response = EmbeddingService::embeddding($chunk->embeddingText($chunk->document->title));
            } catch (Throwable $e) {
                $this->error("Chunk {$chunk->id} failed: {$e->getMessage()}");
                Log::warning('Chunk embedding failed', ['chunk_id' => $chunk->id, 'message' => $e->getMessage()]);
                $failed++;

                continue;
            }

            $chunk->update(['embeddings' => $response->first()]);
            $embedded++;
        }

        $this->info("Embedded: {$embedded} / Failed: {$failed} / Total: ".($embedded + $failed));

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
