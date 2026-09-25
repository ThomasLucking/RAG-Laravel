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
        'search_vector',
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

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
