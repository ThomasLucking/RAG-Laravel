<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'title' => $this->title,
            'summary' => $this->summary,
            'tags' => $this->tags->pluck('title')->all(),
            'updated' => $this->updated_on?->format('Y-m-d'),
            'content' => $this->content,
            'origin' => $this->origin,
        ];
    }
}
