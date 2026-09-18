<?php

namespace App\Models;

use App\Enums\DocumentOrigin;
use Database\Factories\DocumentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    /** @use HasFactory<DocumentFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'slug',
        'title',
        'summary',
        'content',
        'updated_on',
        'source_path',
        'origin',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'origin' => DocumentOrigin::class,
            'updated_on' => 'date',
        ];
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'document_tags');
    }

    public function chunks(): HasMany
    {
        return $this->hasMany(Chunk::class);
    }

    protected static function newFactory(): DocumentFactory
    {
        return DocumentFactory::new();
    }
}
