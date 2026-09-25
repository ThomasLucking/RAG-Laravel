<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsVector;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chunk extends Model
{
    protected $fillable = [
        'document_id',
        'headers',
        'chunk_content',
        'embeddings',
        'search_vector',
    ];

    public function scopeFullTextSearch(Builder $query, string $input, int $limit = 20): void
    {
        // selects everything then ranks the search query based on parameters provided by the controller.
        // this not only does the filtering and it also does the ranking with ts_rank_cd.
        // ts_rank_cd is cover density ranking, and websearch_to_tsquery basically inputs raw text and turns it into a ts_query.
        // DISTINCT ON keeps only the best ranked chunk of each document, so a document is listed once.
        $bestChunkPerDocument = static::query()
            ->selectRaw('DISTINCT ON (document_id) *')
            ->selectRaw("ts_rank_cd(search_vector, websearch_to_tsquery('english', ?), 1) AS rank", [$input])
            ->whereRaw("search_vector @@ websearch_to_tsquery('english', ?)", [$input])
            ->orderBy('document_id')
            ->orderByDesc('rank');

        $query
            ->fromSub($bestChunkPerDocument, 'chunks')
            ->orderByDesc('rank')
            ->limit($limit);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'embeddings' => AsVector::class,
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
