<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChunkResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'document' => [
                'slug' => $this->document->slug,
                'title' => $this->document->title,
            ],
            'headers' => $this->headers,
            'content' => $this->chunk_content,
            'distance' => (float) $this->distance,
            'similarity' => 1 - (float) $this->distance,
        ];
    }
}
