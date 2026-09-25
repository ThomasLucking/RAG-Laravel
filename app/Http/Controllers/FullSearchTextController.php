<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Models\Chunk;
use App\Models\Document;
use Illuminate\View\View;

class FullSearchTextController extends Controller
{
    public function index(SearchRequest $request): View
    {
        $documents = Document::query()->orderBy('title')->get(['slug', 'title', 'origin']);

        $results = $request->filled('query')
            ? Chunk::fullTextSearch($request->validated('query'))->with('document')->get()
            : collect();

        return view('search', compact('documents', 'results'));
    }

    
}
