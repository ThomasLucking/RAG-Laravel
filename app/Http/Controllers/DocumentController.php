<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;
use App\Services\DocumentIngestionService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Symfony\Component\Yaml\Yaml;

class DocumentController extends Controller
{
    public function __construct(
        private readonly DocumentIngestionService $documentIngestionService
    ) {}

    public function create()
    {
        $documents = $this->listDocuments();

        return view('formulaire', compact('documents'));
    }

    public function store(StoreDocumentRequest $request)
    {
        $data = $request->validated();

        $this->documentIngestionService->store($data);

        return redirect()->route('documents.create')
            ->with('status', "\"{$data['title']}\" was added to the corpus.");
    }

    public function show(string $document)
    {
        $parsed = $this->readDocumentFile($document);

        return response()->json([
            'slug' => $document,
            'title' => $parsed['frontmatter']['title'] ?? null,
            'summary' => $parsed['frontmatter']['summary'] ?? null,
            'tags' => Arr::get($parsed['frontmatter'], 'tags', []),
            'updated' => $parsed['frontmatter']['updated'] ?? null,
            'content' => $parsed['content'],
        ]);
    }

    public function edit(string $document)
    {

        $parsed = $this->readDocumentFile($document);

        return view('formulaire', [
            'document' => $document,
            'title' => $parsed['frontmatter']['title'] ?? null,
            'summary' => $parsed['frontmatter']['summary'] ?? null,
            'tags' => Arr::get($parsed['frontmatter'], 'tags', []),
            'content' => $parsed['content'],
        ]);

    }

    public function update(UpdateDocumentRequest $request, string $document)
    {
        $path = $this->resolveDocumentPath($document);
        $data = $request->validated();

        $this->documentIngestionService->update($path, $data);

        return response()->json([
            'slug' => $document,
            'title' => $data['title'],
            'summary' => $data['summary'],
            'tags' => $data['tags'] ?? [],
            'updated' => $data['updated'],
            'content' => $data['content'],
        ]);
    }

    public function destroy(string $document)
    {

        $path = $this->resolveDocumentPath($document);

        $this->documentIngestionService->delete($path);

        return response()->json(['slug' => $document, 'deleted' => true]);

    }

    private function listDocuments(): array
    {
        $files = File::glob(base_path('docs/data').'/*.md');

        $documents = collect($files)->map(function (string $path) {
            $slug = pathinfo($path, PATHINFO_FILENAME);
            $parsed = $this->readDocumentFile($slug);

            return [
                'slug' => $slug,
                'title' => $parsed['frontmatter']['title'] ?? $slug,
            ];
        })->sortBy('title')->values()->all();

        return $documents;
    }

    private function resolveDocumentPath(string $document): string
    {
        $slug = basename($document);

        $path = base_path('docs/data/'.$slug.'.md');

        if (! File::exists($path)) {
            abort(404, 'Document file not found.');
        }

        return $path;
    }

    private function readDocumentFile(string $document): array
    {
        $path = $this->resolveDocumentPath($document);

        $raw = File::get($path);

        if (! preg_match('/^---\r?\n(.*?)\r?\n---\r?\n?(.*)$/s', $raw, $matches)) {
            return [
                'frontmatter' => [],
                'content' => trim($raw),
            ];
        }

        $frontmatter = Yaml::parse($matches[1]) ?? [];

        return [
            'frontmatter' => $frontmatter,
            'content' => trim($matches[2]),
        ];
    }
}
