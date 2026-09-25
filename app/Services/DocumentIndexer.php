<?php

namespace App\Services;

use App\Enums\DocumentOrigin;
use App\Jobs\EmbedDocumentChunks;
use App\Models\Document;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Parser\MarkdownParser;

class DocumentIndexer
{
    /**
     * Save a Document, sync its tags and rebuild its chunks from validated input.
     *
     * @param  array{title: string, summary: string, content: string, updated: string, tags?: list<string>}  $data
     */
    public function save(Document $document, array $data, ?DocumentOrigin $origin = null): Document
    {
        // basically resource decouples the apu out from the db columns and schema changes wont break the response shape
        return DB::transaction(function () use ($document, $data, $origin) {
            if (! $document->exists) {
                $document->slug ??= Str::slug($data['title']);
                $document->origin = $origin ?? DocumentOrigin::Manual;
            }

            $document->title = $data['title'];
            $document->summary = $data['summary'];
            $document->content = $data['content'];
            $document->updated_on = $data['updated'];
            $document->save();

            $tagIds = collect($data['tags'] ?? [])
                ->map(fn (string $tagName) => Tag::firstOrCreate(['title' => $tagName])->id);

            $document->tags()->sync($tagIds);

            $this->rebuildChunks($document);

            // afterCommit so the worker never runs before the new chunks are visible
            EmbedDocumentChunks::dispatch($document)->afterCommit();

            return $document;
        });
    }

    /**
     * Replace a Document's chunks with one chunk per H2 section of its content, so re-saving never piles up duplicates.
     */
    private function rebuildChunks(Document $document): void
    {
        $environment = new Environment;
        $environment->addExtension(new CommonMarkCoreExtension);

        $sections = MarkdownSectionExtractor::merge(
            MarkdownSectionExtractor::extract(new MarkdownParser($environment), $document->content)
        );

        $document->chunks()->delete();

        foreach ($sections as $section) {
            $document->chunks()->create([
                'headers' => $section['heading'],
                'chunk_content' => $section['content'],
            ]);
        }
    }

    /**
     * Hard-delete a Document's chunks and soft-delete the Document itself.
     */
    public function delete(Document $document): void
    {
        DB::transaction(function () use ($document) {
            $document->chunks()->delete();
            $document->delete();
        });
    }
}
