<?php

namespace App\Services;

use Laravel\Ai\Embeddings;
use Laravel\Ai\Enums\Lab;

class EmbeddingService
{
    public static function embeddding(string $chunk)
    {
        $response = Embeddings::for([$chunk])->dimensions(768)->generate(Lab::Ollama);

        return $response;

    }
}
