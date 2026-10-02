<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocumentRequest;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
use App\Services\DocumentIndexer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function __construct(
        private readonly DocumentIndexer $documentIndexer
    ) {}

    public function index(): View
    {
        $documents = Document::query()->orderBy('title')->get(['slug', 'title', 'origin']);

        return view('formulaire', compact('documents'));
    }

    public function store(DocumentRequest $request): JsonResponse
    {
        $document = $this->documentIndexer->save(new Document, $request->validated());

        return DocumentResource::make($document->load('tags'))->response()->setStatusCode(201);
    }

    public function show(Document $document): DocumentResource
    {
        return DocumentResource::make($document->load('tags'));
    }

    public function update(DocumentRequest $request, Document $document): DocumentResource
    {
        $document = $this->documentIndexer->save($document, $request->validated());

        return DocumentResource::make($document->load('tags'));
    }

    public function destroy(Document $document): Response
    {
        $this->documentIndexer->delete($document);

        return response()->noContent();
    }
}
