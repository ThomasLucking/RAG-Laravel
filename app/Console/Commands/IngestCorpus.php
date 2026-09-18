<?php

namespace App\Console\Commands;

use App\Enums\DocumentOrigin;
use App\Models\Document;
use App\Services\DocumentIndexer;
use App\Services\MarkdownSectionExtractor;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Parser\MarkdownParser;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

#[Signature('app:ingest-corpus')]
#[Description('Import the docs/data corpus seed into Documents, restoring and re-chunking slugs that already exist (including soft-deleted ones).')]
class IngestCorpus extends Command
{
    /**
     * Execute the console command.
     *
     * @var DocumentIndexer
     */
    public function handle(DocumentIndexer $documentIndexer): void
    {
        $environment = new Environment;
        $environment->addExtension(new CommonMarkCoreExtension);
        $parser = new MarkdownParser($environment);

        $directory = 'docs/data';

        $items = glob(base_path($directory).'/*.md');

        $created = 0;
        $skipped = 0;

        foreach ($items as $item) {
            if (! is_file($item)) {
                continue;
            }

            $slug = pathinfo($item, PATHINFO_FILENAME);

            $document = Document::withTrashed()->where('slug', $slug)->first();

            if ($document?->trashed()) { // slug has a unique index, so a trashed row must be restored, not left for a duplicate insert to collide with
                $document->restore();
            }

            $raw = str_replace("\r\n", "\n", file_get_contents($item));

            if (! preg_match('/^---\n(.*?)\n---\n?(.*)$/s', $raw, $m)) { // split into front matter ($m[1]) and body ($m[2])
                $this->error("Skipping {$item}: missing or malformed front matter delimiters");
                $skipped++;

                continue;
            }
            $markdown = trim($m[2]);

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

            $document ??= new Document;
            $document->slug = $slug;
            $document->source_path = basename($item);

            $sections = MarkdownSectionExtractor::merge(MarkdownSectionExtractor::extract($parser, $markdown));

            DB::transaction(function () use ($documentIndexer, $document, $frontmatter, $markdown, $tagNames, $sections) {

                // first save the document content and frontmatter inside of the documents table
                $documentIndexer->save($document, [
                    'title' => $frontmatter['title'],
                    'summary' => $frontmatter['summary'],
                    'content' => $markdown,
                    'updated' => $frontmatter['updated'] ?? now()->toDateString(),
                    'tags' => $tagNames,
                ], DocumentOrigin::Imported);

                // delete any previous chunks
                $document->chunks()->delete();

                // save the new chunks to avoid repetitions and duplicating information.
                foreach ($sections as $section) {
                    $document->chunks()->create([
                        'headers' => $section['heading'],
                        'chunk_content' => $section['content'],
                    ]);
                }
            });

            $created++;
        }

        $this->info("Import complete: {$created} created, {$skipped} skipped.");
    }
}
