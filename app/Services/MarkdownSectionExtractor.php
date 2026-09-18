<?php

namespace App\Services;

use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Node\Block\IndentedCode;
use League\CommonMark\Extension\CommonMark\Node\Inline\Code;
use League\CommonMark\Node\Block\HtmlBlock;
use League\CommonMark\Node\Inline\HtmlInline;
use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Parser\MarkdownParser;

class MarkdownSectionExtractor
{
    public static function extract(MarkdownParser $parser, string $markdown): array
    {
        $document = $parser->parse($markdown);
        $walker = $document->walker();
        $insideHeader = false;
        $level = null;
        $current = null;
        $sections = [];
        $headerLeveltw = [];

        while ($event = $walker->next()) {
            $node = $event->getNode();
            if ($node instanceof Heading) {
                if ($event->isEntering()) {
                    $level = $node->getLevel();
                    if ($level == 2) {
                        $headerLeveltw[] = $level;
                    }
                    $insideHeader = true;
                } else {
                    $insideHeader = false;
                }
            }

            if (! $event->isEntering()) {
                continue;
            }

            // if the node is an instance of a heading then we flush out
            if ($node instanceof Heading) {
                if ($current !== null) {
                    $sections[] = $current;
                }
                $current = ['heading' => '', 'level' => $level, 'content' => ''];
            }

            if (
                $node instanceof Text
                || $node instanceof Code
                || $node instanceof FencedCode
                || $node instanceof IndentedCode
                || $node instanceof HtmlBlock
                || $node instanceof HtmlInline
            ) {
                $text = $node->getLiteral();

                if ($node instanceof FencedCode) {
                    $text = "\n\n```{$node->getInfo()}\n{$text}```\n\n";
                } elseif ($node instanceof IndentedCode) {
                    $text = "\n\n```\n{$text}```\n\n";
                }

                if ($current === null) {
                    $current = ['heading' => '', 'level' => null, 'content' => ''];
                }
                if ($insideHeader) {
                    $current['heading'] .= $text;
                } else {
                    $current['content'] .= $text;
                }
            }
        }

        if ($current !== null) {
            $sections[] = $current;
        }

        return $sections;
    }

    /**
     * Collapse extract()'s per-heading sections into one chunk per H2.
     *
     * Every H2 starts a new chunk; anything below it (H3+) gets folded into
     * that chunk's content as a "### heading" block instead of staying its
     * own section. Text found before the first H2 (no heading of its own)
     * is buffered in $pending and prepended to the first H2 chunk once it
     * shows up, so it never ships as an orphan headingless chunk.
     */
    public static function merge(array $allSections): array
    {
        $merged = [];
        $current = null;
        $pending = '';

        foreach ($allSections as $section) {
            if ($section['level'] == 2) {
                // Flush the chunk we were building and start a new one.
                if ($current !== null) {
                    $merged[] = $current;
                }
                $current = $section;

                // Attach any pre-H2 preamble to this, the first H2 chunk.
                if ($pending !== '') {
                    $current['content'] = $pending.$current['content'];
                    $pending = '';
                }
            } elseif ($current !== null) {
                // Sub-heading under the current H2: fold into its content.
                $current['content'] .= "\n\n### {$section['heading']}\n\n".$section['content'];
            } else {
                // No H2 seen yet: hold this until the first one arrives.
                $pending .= ($section['heading'] !== '' ? "\n\n### {$section['heading']}\n\n" : "\n\n").$section['content'];
            }
        }

        if ($current !== null) {
            $merged[] = $current;
        } elseif ($pending !== '') {
            // Document had no H2 at all: ship the buffered text as-is.
            $merged[] = ['heading' => '', 'level' => null, 'content' => $pending];
        }

        return $merged;
    }
}
