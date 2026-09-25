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

];
