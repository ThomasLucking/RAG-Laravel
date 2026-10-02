<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Full Text Search Language
    |--------------------------------------------------------------------------
    |
    | The PostgreSQL text search configuration used to build the chunks
    | search vector and to parse user queries. The corpus is in English.
    |
    */

    'language' => env('SEARCH_LANGUAGE', 'english'),

    /*
    |--------------------------------------------------------------------------
    | Meaning Search Minimum Similarity
    |--------------------------------------------------------------------------
    |
    | The minimum cosine similarity (1 - cosine distance) a chunk must reach
    | to be returned by meaning search. Chunks below it are excluded.
    |
    */

    'min_similarity' => (float) env('SEARCH_MIN_SIMILARITY', 0.5),

    /*
    |--------------------------------------------------------------------------
    | Meaning Search Rate Limit
    |--------------------------------------------------------------------------
    |
    | The maximum number of meaning search queries a single client may send
    | per minute. Each query calls the embedding provider, so keep it within
    | the provider's capacity.
    |
    */

    'queries_per_minute' => (int) env('SEARCH_QUERIES_PER_MINUTE', 20),

];
