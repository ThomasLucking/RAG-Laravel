<?php

namespace App\Models;

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
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'embeddings' => AsVector::class,
        ];
    }

    /**
     * The text sent to the embedding model: the Document title and section headers give the chunk its context.
     */
    public function embeddingText(string $documentTitle): string
    {
        return "Document: {$documentTitle}\n Sections: {$this->headers} \n Content: {$this->chunk_content}";
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
