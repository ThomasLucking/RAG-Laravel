<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FullSearchTextController extends Controller
{
    public function index(): View
    {
        $documents = Document::query()->orderBy('title')->get(['slug', 'title', 'origin']);

        return view('search', compact('documents'));
    }

    public function userQuery(Request $request): RedirectResponse
    {
        return redirect()->back();
    }
}
