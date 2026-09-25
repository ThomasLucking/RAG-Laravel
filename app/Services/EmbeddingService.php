<?php

namespace App\Services;

use Laravel\Ai\Embeddings;
use Laravel\Ai\Responses\EmbeddingsResponse;

class EmbeddingService
{
    public static function embeddding(string $chunk): EmbeddingsResponse
    {
        $provider = config('ai.default_for_embeddings');

        return Embeddings::for([$chunk])
            ->cache()
            ->dimensions(config("ai.providers.{$provider}.models.embeddings.dimensions"))
            ->generate($provider, config("ai.providers.{$provider}.models.embeddings.default"));
    }
}
