<?php

use App\Enums\DocumentOrigin;
use App\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Symfony\Component\Yaml\Yaml;

uses(RefreshDatabase::class);

function ingestTestDocumentPath(string $slug): string
{
    return base_path("docs/data/{$slug}.md");
}

/**
 * @param  array<string, mixed>  $frontmatter
 */
function writeIngestTestDocument(string $slug, array $frontmatter, string $content = 'Body.'): void
{
    $yaml = Yaml::dump($frontmatter);
    File::put(ingestTestDocumentPath($slug), "---\n{$yaml}---\n{$content}");
}

afterEach(function () {
    collect(File::glob(base_path('docs/data/pest-ingest-*.md')))
        ->each(fn (string $path) => File::delete($path));
});

test('it creates a document for a new seed file', function () {
    $slug = 'pest-ingest-new-'.uniqid();
    writeIngestTestDocument($slug, ['title' => 'Pest Ingest New', 'summary' => 'A new seed doc.', 'tags' => 'rust, cli']);

    $this->artisan('app:ingest-corpus')->assertSuccessful();

    $document = Document::where('slug', $slug)->firstOrFail();
    expect($document->origin)->toBe(DocumentOrigin::Imported);
    expect($document->tags->pluck('title')->all())->toBe(['rust', 'cli']);
});

test('it skips a seed file whose slug already exists', function () {
    $slug = 'pest-ingest-existing-'.uniqid();
    Document::factory()->create(['slug' => $slug, 'title' => 'Existing Title']);
    writeIngestTestDocument($slug, ['title' => 'Should Not Overwrite', 'summary' => 'Seed summary.']);

    $this->artisan('app:ingest-corpus')->assertSuccessful();

    expect(Document::where('slug', $slug)->first()->title)->toBe('Existing Title');
    expect(Document::where('slug', $slug)->count())->toBe(1);
});

test('it skips a seed file whose slug belongs to a soft-deleted document', function () {
    $slug = 'pest-ingest-trashed-'.uniqid();
    $trashed = Document::factory()->create(['slug' => $slug]);
    $trashed->delete();
    writeIngestTestDocument($slug, ['title' => 'Should Not Revive', 'summary' => 'Seed summary.']);

    $this->artisan('app:ingest-corpus')->assertSuccessful();

    expect(Document::withTrashed()->where('slug', $slug)->count())->toBe(1);
    expect(Document::where('slug', $slug)->exists())->toBeFalse();
});
