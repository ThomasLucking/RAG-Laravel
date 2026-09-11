<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;
use App\Models\Document;
use Illuminate\Support\Facades\File;
use Symfony\Component\Yaml\Yaml;
use Illuminate\Support\Arr;
class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('formulaire');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDocumentRequest $request)
    {
        $dataEverything = $request->validated();

        $dataToBeParseByYaml = Arr::except($dataEverything, ['content']);

        $content = $dataEverything['content'];

        $formattedYaml = Yaml::dump($dataToBeParseByYaml);

        $combinedData = "---\n{$formattedYaml}---\n"  . $content;

        $directory = base_path('docs/data');

        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $path = "{$directory}/{$dataEverything['title']}.md";

        if (File::put($path, $combinedData) === false) {
            abort(500, 'Failed to write document file.');
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(Document $document)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Document $document)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDocumentRequest $request, Document $document)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Document $document)
    {
        //
    }
}
