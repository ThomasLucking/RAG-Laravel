<?php

namespace App\Services;

use Laravel\Ai\Embeddings;
use Laravel\Ai\Responses\EmbeddingsResponse;

class EmbeddingService
{
    /**
     * Embed a user query, prefixed with the task instruction the model was trained to expect for queries.
     */
    public static function embedQuery(string $query): EmbeddingsResponse
    {
        return self::embed(self::prefix('query_prefix').$query);
    }

    /**
     * Embed a chunk of a document, prefixed with the task instruction the model was trained to expect for documents.
     */
    public static function embedDocument(string $chunk): EmbeddingsResponse
    {
        return self::embed(self::prefix('document_prefix').$chunk);
    }

    private static function embed(string $input): EmbeddingsResponse
    {
        $provider = config('ai.default_for_embeddings');

        return Embeddings::for([$input])
            ->cache()
            ->dimensions(config("ai.providers.{$provider}.models.embeddings.dimensions"))
            ->generate($provider, config("ai.providers.{$provider}.models.embeddings.default"));
    }

    /**
     * Get the configured prefix of the embeddings provider, or an empty string when the model needs none.
     */
    private static function prefix(string $key): string
    {
        $provider = config('ai.default_for_embeddings');

        return (string) config("ai.providers.{$provider}.models.embeddings.{$key}", '');
    }
}
