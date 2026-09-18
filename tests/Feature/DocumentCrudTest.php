<?php

use App\Enums\DocumentOrigin;
use App\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the workshop index renders the document list', function () {
    Document::factory()->count(2)->create();

    $this->get('/documents')->assertOk()->assertViewIs('formulaire');
});

test('storing a document creates a manual document and returns 201', function () {
    $response = $this->postJson('/documents', [
        'title' => 'Pest Manual Doc',
        'summary' => 'Created through the workshop.',
        'tags' => 'rust, cli',
        'updated' => '2026-09-18',
        'content' => '## Intro

Body.',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.title', 'Pest Manual Doc');
    $response->assertJsonPath('data.origin', DocumentOrigin::Manual->value);
    $response->assertJsonPath('data.tags', ['rust', 'cli']);

    $document = Document::where('slug', 'pest-manual-doc')->firstOrFail();
    expect($document->origin)->toBe(DocumentOrigin::Manual);
});

test('storing a document whose slug belongs to a soft-deleted document returns a 422 on title', function () {
    $trashed = Document::factory()->create(['title' => 'Pest Trashed Doc', 'slug' => 'pest-trashed-doc']);
    $trashed->delete();

    $response = $this->postJson('/documents', [
        'title' => 'Pest Trashed Doc',
        'summary' => 'Summary.',
        'updated' => '2026-09-18',
        'content' => 'Body.',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors('title');
    expect($response->json('errors.title.0'))->toBe('A document (possibly deleted) already uses this title.');
});

test('updating a document keeps its slug and origin', function () {
    $document = Document::factory()->imported()->create(['slug' => 'pest-imported-doc']);

    $response = $this->putJson("/documents/{$document->slug}", [
        'title' => 'A Completely Different Title',
        'summary' => 'Updated summary.',
        'updated' => '2026-09-18',
        'content' => 'Updated body.',
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.slug', 'pest-imported-doc');
    $response->assertJsonPath('data.origin', DocumentOrigin::Imported->value);

    expect($document->refresh()->slug)->toBe('pest-imported-doc');
    expect($document->origin)->toBe(DocumentOrigin::Imported);
});

test('show returns the document resource', function () {
    $document = Document::factory()->create();

    $response = $this->getJson("/documents/{$document->slug}");

    $response->assertOk();
    $response->assertJsonPath('data.slug', $document->slug);
    $response->assertJsonPath('data.title', $document->title);
});

test('destroy soft-deletes the document, removes its chunks, and returns 204', function () {
    $document = Document::factory()->create();
    $document->chunks()->create([
        'headers' => 'Intro',
        'chunk_content' => 'Body.',
    ]);

    $response = $this->deleteJson("/documents/{$document->slug}");

    $response->assertNoContent();
    expect(Document::withTrashed()->find($document->id)->trashed())->toBeTrue();
    expect($document->chunks()->count())->toBe(0);
});

test('an unknown document slug returns 404', function () {
    $this->getJson('/documents/does-not-exist')->assertNotFound();
});
