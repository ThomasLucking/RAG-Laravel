<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Models\Tag;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

#[Signature('app:ingest-corpus')]
#[Description('Command description')]
class IngestCorpus extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {

        $directory = 'docs/data';

        $items = glob(base_path($directory).'/*.md');

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($items as $item) {
            if (! is_file($item)) {
                continue;
            }
            $raw = file_get_contents($item);

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
            // will be split and stored as text fragments later.
            $markdown = trim($m[2]); // the pure markdown

            $storedDocument = Document::updateOrCreate(
                ['source_path' => basename($item)],
                [
                    'title' => $frontmatter['title'],
                    'summary' => $frontmatter['summary'],
                ]
            );

            $storedDocument->wasRecentlyCreated ? $created++ : $updated++;

            $tagNames = $frontmatter['tags'] ?? [];

            if (is_string($tagNames)) {
                $tagNames = array_map('trim', explode(',', $tagNames));
            }
            $tagIds = collect($tagNames)->map(function ($tagName) { // iterate each tag name since it's now an array.
                return Tag::firstOrCreate(['title' => $tagName])->id; // find or create the Tag, collect its id
            });

            $storedDocument->tags()->sync($tagIds); // attach exactly these tag ids, detaching any not listed
        }

        $this->info("Import complete: {$created} created, {$updated} updated, {$skipped} skipped.");

    }
}
