<?php

namespace App\Console\Commands;

use App\Services\MarkdownSectionExtractor;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Parser\MarkdownParser;

#[Signature('app:test-markdown-section-extractor')]
#[Description('Run MarkdownSectionExtractor against every markdown file in docs/data and dump the extracted/merged sections.')]
class TestMarkdownSectionExtractor extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $environment = new Environment;
        $environment->addExtension(new CommonMarkCoreExtension);
        $parser = new MarkdownParser($environment);

        $items = glob(base_path('docs/data').'/*.md');

        foreach ($items as $item) {
            if (! is_file($item)) {
                continue;
            }

            $raw = str_replace("\r\n", "\n", file_get_contents($item));

            if (preg_match('/^---\n(.*?)\n---\n?(.*)$/s', $raw, $m)) {
                $markdown = trim($m[2]);
            } else {
                $markdown = $raw;
            }

            $this->info('=== '.basename($item).' ===');

            $sections = MarkdownSectionExtractor::extract($parser, $markdown);
            $this->line('--- extract() ---');
            $this->line((string) json_encode($sections, JSON_PRETTY_PRINT));

            $merged = MarkdownSectionExtractor::merge($sections);
            $this->line('--- merge() ---');
            $this->line((string) json_encode($merged, JSON_PRETTY_PRINT));
        }
    }
}
