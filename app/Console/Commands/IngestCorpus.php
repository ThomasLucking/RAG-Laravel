<?php

namespace App\Console\Commands;

use App\Enums\DocumentOrigin;
use App\Models\Document;
use App\Services\DocumentIndexer;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

#[Signature('app:ingest-corpus')]
#[Description('Import the docs/data corpus seed into Documents, skipping slugs that already exist (including soft-deleted ones).')]
class IngestCorpus extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(DocumentIndexer $documentIndexer): void
    {
        $directory = 'docs/data';

        $items = glob(base_path($directory).'/*.md');

        $created = 0;
        $skipped = 0;

        foreach ($items as $item) {
            if (! is_file($item)) {
                continue;
            }

            $slug = pathinfo($item, PATHINFO_FILENAME);

            if (Document::withTrashed()->where('slug', $slug)->exists()) {
                $skipped++;

                continue;
            }

            $raw = str_replace("\r\n", "\n", file_get_contents($item));

            if (! preg_match('/^---\n(.*?)\n---\n?(.*)$/s', $raw, $m)) { // split into front matter ($m[1]) and body ($m[2])
                $this->error("Skipping {$item}: missing or malformed front matter delimiters");
                $skipped++;

                continue;
            }

            try {
                $frontmatter = Yaml::parse($m[1]); // parse the YAML front matter into an array
            } catch (ParseException $e) {
                $this->error("Skipping {$item}: invalid YAML front matter {$e->getMessage()}");
                $skipped++;

                continue;
            }

            if (empty($frontmatter['title']) || empty($frontmatter['summary'])) { // require both fields present
                $this->error("Skipping {$item}: missing required 'title' or 'summary' in front matter");
                $skipped++;

                continue;
            }

            $markdown = trim($m[2]); // the pure markdown, split and stored as chunks by the indexing job

            $tagNames = $frontmatter['tags'] ?? [];

            if (is_string($tagNames)) {
                $tagNames = array_map('trim', explode(',', $tagNames));
            }

            $document = new Document;
            $document->slug = $slug;
            $document->source_path = basename($item);

            $documentIndexer->save($document, [
                'title' => $frontmatter['title'],
                'summary' => $frontmatter['summary'],
                'content' => $markdown,
                'updated' => $frontmatter['updated'] ?? now()->toDateString(),
                'tags' => $tagNames,
            ], DocumentOrigin::Imported);

            $created++;
        }

        $this->info("Import complete: {$created} created, {$skipped} skipped.");
    }
}
