<?php

use App\Models\Chunk;
use App\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Embeddings;
use Laravel\Ai\Prompts\EmbeddingsPrompt;

uses(RefreshDatabase::class);

beforeEach(function () {
    config([
        'ai.default_for_embeddings' => 'ollama',
        'ai.providers.ollama.models.embeddings.document_prefix' => 'search_document: ',
    ]);
});

/**
 * @param  list<float>|null  $embeddings
 */
function storedChunk(string $content, ?array $embeddings = null): Chunk
{
    return Document::factory()->create()->chunks()->create([
        'headers' => 'Section',
        'chunk_content' => $content,
        'embeddings' => $embeddings,
    ]);
}

test('chunks are embedded with the configured document prefix', function () {
    Embeddings::fake();
    storedChunk('The cat slept on the rug.');

    $this->artisan('app:store-embeddings')->assertSuccessful();

    Embeddings::assertGenerated(fn (EmbeddingsPrompt $prompt): bool => str_starts_with($prompt->inputs[0], 'search_document: ')
        && $prompt->contains('The cat slept on the rug.'));
});

test('chunks that already have a vector are skipped by default', function () {
    Embeddings::fake();
    storedChunk('Already embedded', array_fill(0, 768, 0.1));

    $this->artisan('app:store-embeddings')->assertSuccessful();

    Embeddings::assertNothingGenerated();
});

test('the fresh option re-embeds chunks that already have a vector', function () {
    Embeddings::fake();
    storedChunk('Already embedded', array_fill(0, 768, 0.1));

    $this->artisan('app:store-embeddings', ['--fresh' => true])->assertSuccessful();

    Embeddings::assertGenerated(fn (EmbeddingsPrompt $prompt): bool => $prompt->contains('search_document: ')
        && $prompt->contains('Already embedded'));
});
