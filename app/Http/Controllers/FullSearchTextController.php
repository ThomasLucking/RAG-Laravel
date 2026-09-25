<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Models\Document;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FullSearchTextController extends Controller
{
    public function index(): View
    {
        $documents = Document::query()->orderBy('title')->get(['slug', 'title', 'origin']);

        return view('search', compact('documents'));
    }

    public function userQuery(SearchRequest $query): RedirectResponse
    {
        $query->validated();

        return redirect()->back();
    }
}
