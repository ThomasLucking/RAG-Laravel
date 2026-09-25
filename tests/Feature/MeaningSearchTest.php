<?php

use App\Models\Chunk;
use App\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Embeddings;

uses(RefreshDatabase::class);

/**
 * Build a 768-dimension unit vector whose cosine similarity to the query vector (the first axis) is exactly `$similarity`.
 *
 * @return list<float>
 */
function vectorWithSimilarity(float $similarity): array
{
    $vector = array_fill(0, 768, 0.0);
    $vector[0] = $similarity;
    $vector[1] = sqrt(1 - $similarity ** 2);

    return $vector;
}

/**
 * Fake the embedding provider so the next embedded query becomes the first-axis vector.
 */
function fakeQueryEmbedding(): void
{
    Embeddings::fake([[vectorWithSimilarity(1.0)]]);
}

function chunkWithSimilarity(Document $document, float $similarity, string $content = 'Some fragment content.'): Chunk
{
    return $document->chunks()->create([
        'headers' => 'Section',
        'chunk_content' => $content,
        'embeddings' => vectorWithSimilarity($similarity),
    ]);
}

beforeEach(function () {
    config(['search.min_similarity' => 0.7]);
});

test('meaning search returns the closest fragments first', function () {
    fakeQueryEmbedding();

    $farther = Document::factory()->create(['title' => 'Farther document']);
    $closest = Document::factory()->create(['title' => 'Closest document']);
    chunkWithSimilarity($farther, 0.8);
    chunkWithSimilarity($closest, 0.95);

    $response = $this->postJson(route('user.query'), ['query' => 'anything']);

    $response->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.document.title', 'Closest document')
        ->assertJsonPath('data.1.document.title', 'Farther document');
});

test('each result shows its distance, similarity and document', function () {
    fakeQueryEmbedding();

    $document = Document::factory()->create();
    chunkWithSimilarity($document, 0.8);

    $response = $this->postJson(route('user.query'), ['query' => 'anything']);

    $response->assertOk()
        ->assertJsonPath('data.0.document.slug', $document->slug)
        ->assertJsonPath('data.0.document.title', $document->title)
        ->assertJsonStructure(['data' => [['document' => ['slug', 'title'], 'headers', 'content', 'distance', 'similarity']]]);

    expect($response->json('data.0.distance'))->toEqualWithDelta(0.2, 0.001)
        ->and($response->json('data.0.similarity'))->toEqualWithDelta(0.8, 0.001);
});

test('fragments below the similarity threshold are excluded', function () {
    fakeQueryEmbedding();

    $document = Document::factory()->create();
    chunkWithSimilarity($document, 0.9, 'Close enough');
    chunkWithSimilarity($document, 0.5, 'Too far');

    $response = $this->postJson(route('user.query'), ['query' => 'anything']);

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.content', 'Close enough');
});

test('the similarity threshold is read from configuration', function () {
    fakeQueryEmbedding();
    config(['search.min_similarity' => 0.85]);

    $document = Document::factory()->create();
    chunkWithSimilarity($document, 0.9, 'Above the configured threshold');
    chunkWithSimilarity($document, 0.8, 'Below the configured threshold');

    $response = $this->postJson(route('user.query'), ['query' => 'anything']);

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.content', 'Above the configured threshold');
});

test('a query with no relevant fragment returns an explicit nothing found state', function () {
    fakeQueryEmbedding();

    chunkWithSimilarity(Document::factory()->create(), 0.3);

    $response = $this->postJson(route('user.query'), ['query' => 'anything']);

    $response->assertOk()
        ->assertJsonMissingPath('data')
        ->assertJsonStructure(['message']);
});

test('meaning search requires a query', function () {
    Embeddings::fake();

    $this->postJson(route('user.query'), ['query' => ''])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('query');

    Embeddings::assertNothingGenerated();
});

test('meaning search finds a fragment sharing no word with the query while word search does not', function () {
    fakeQueryEmbedding();

    $document = Document::factory()->create(['title' => 'Pet care']);
    chunkWithSimilarity($document, 0.9, 'The cat slept on the rug all afternoon.');

    $query = 'feline napping';

    $this->get(route('search.index', ['query' => $query]))
        ->assertOk()
        ->assertSee('No chunks match this query.');

    $this->postJson(route('user.query'), ['query' => $query])
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.content', 'The cat slept on the rug all afternoon.');
});

test('both search modes are reachable from the same page', function () {
    $this->get(route('documents.index'))
        ->assertOk()
        ->assertSee(route('user.query'), escape: false)
        ->assertSee(route('search.index'), escape: false);
});
