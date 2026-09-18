<?php

namespace App\Services;

use App\Enums\DocumentOrigin;
use App\Models\Document;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DocumentIndexer
{
    /**
     * Save a Document and sync its tags from validated input.
     *
     * @param  array{title: string, summary: string, content: string, updated: string, tags?: list<string>}  $data
     */
    public function save(Document $document, array $data, ?DocumentOrigin $origin = null): Document
    {
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

            return $document;
        });
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
