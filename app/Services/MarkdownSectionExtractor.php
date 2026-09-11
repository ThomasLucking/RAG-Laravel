<?php

namespace App\Services;

use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Node\Block\IndentedCode;
use League\CommonMark\Extension\CommonMark\Node\Inline\Code;
use League\CommonMark\Node\Block\AbstractBlock;
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

    public static function merge(array $allSections): array
    {
        $merged = [];
        $current = null;

        foreach ($allSections as $section) {
            if ($section['level'] == 2) {
                if ($current !== null) {
                    $merged[] = $current;
                }
                $current = $section;
            } elseif ($current !== null) {
                $current['content'] .= $section['content'];
            } else {
                $current = $section;
            }
        }

        if ($current !== null) {
            $merged[] = $current;
        }

        return $merged;
    }
}
