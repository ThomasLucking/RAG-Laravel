<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserQueryRequest;
use App\Http\Resources\ChunkResource;
use App\Models\Chunk;
use App\Services\EmbeddingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MeaningSearchController extends Controller
{
    public function __invoke(UserQueryRequest $request): AnonymousResourceCollection|JsonResponse
    {
        $queryEmbedding = EmbeddingService::embeddding($request->validated('query'))->first();

        $similiarFragments = Chunk::query()
            ->with('document:id,slug,title')
            ->select('chunks.*')
            ->selectVectorDistance('embeddings', $queryEmbedding, as: 'distance')
            ->whereVectorSimilarTo('embeddings', $queryEmbedding, minSimilarity: config('search.min_similarity'))
            ->limit(10)
            ->get();

        if ($similiarFragments->isEmpty()) {
            return response()->json([
                'message' => 'No relevant document found for your query.',
            ]);
        }

        return ChunkResource::collection($similiarFragments);
    }
}
