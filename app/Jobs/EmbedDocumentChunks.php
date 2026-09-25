<?php

namespace App\Jobs;

use App\Models\Document;
use App\Services\EmbeddingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class EmbedDocumentChunks implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * @var list<int>
     */
    public array $backoff = [10, 30, 60];

    /**
     * Drop the job instead of failing it when the Document was deleted before the worker picked it up.
     */
    public bool $deleteWhenMissingModels = true;

    /**
     * Create a new job instance.
     */
    public function __construct(public Document $document) {}

    /**
     * Embed every chunk of the Document that has no vector yet; a retry only picks up the chunks still missing one.
     */
    public function handle(): void
    {
        $chunks = $this->document->chunks()
            ->whereNull('embeddings')
            ->select(['id', 'document_id', 'headers', 'chunk_content'])
            ->lazyById();

        foreach ($chunks as $chunk) {
            $chunk->update(['embeddings' => EmbeddingService::embeddding($chunk->embeddingText($this->document->title))->first()]);
        }
    }
}
