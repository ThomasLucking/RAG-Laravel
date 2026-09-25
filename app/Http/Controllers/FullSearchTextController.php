<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Models\Chunk;
use Illuminate\View\View;

class FullSearchTextController extends Controller
{
    public function index(SearchRequest $request): View
    {
        // load only the documents only for the matching fragment
        $results = $request->filled('query')
            ? Chunk::fullTextSearch($request->validated('query'))->with('document:id,slug,title,origin')->get()
            : collect();

        $documents = $results->pluck('document')->filter()->sortBy('title')->values();

        return view('search', compact('documents', 'results'));
    }
}
