<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Yaml\Yaml;

class DocumentIngestionService
{
    public function store(array $data): string
    {
        $metadata = Arr::except($data, ['content']);
        $yaml = Yaml::dump($metadata);
        $combined = "---\n{$yaml}---\n{$data['content']}";

        $filename = Str::slug($data['title']).'.md';

        if (Storage::disk('local')->put($filename, $combined) === false) {
            abort(500, 'Failed to write document file.');
        }

        Artisan::call('app:ingest-corpus');

        return $filename;
    }

    public function update(string $path, array $data): void
    {
        $metadata = Arr::except($data, ['content']);
        $yaml = Yaml::dump($metadata);
        $combined = "---\n{$yaml}---\n{$data['content']}";

        if (File::put($path, $combined) === false) {
            throw new \RuntimeException("Failed to write document file: {$path}");
        }

        Artisan::call('app:ingest-corpus');
    }

    public function delete(string $path): void
    {
        if (! File::delete($path)) {
            throw new \RuntimeException("Failed to delete document file: {$path}");
        }

        Artisan::call('app:ingest-corpus');
    }
}
