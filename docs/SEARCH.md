Chunking strategy: I parse the markdown into an AST and walk the tree to find headings and their content. I split into one chunk per H2 heading, since that's where the real content boundaries are. Any H3+ content inside an H2's range gets folded into that same H2 chunk (marked with a `### heading` line), rather than becoming its own chunk. Text that appears before the first H2 (a doc intro with no heading) gets buffered and prepended to the first H2 chunk so it doesn't ship as an orphan fragment. Re-ingesting a document deletes its old chunks and rebuilds them, so re-splitting replaces instead of piling up duplicates.

Size/overlap: there's no fixed target size just whatever the algorithm finds like H2's

Review findings: sampled 13 fragments across 11 docs — mostly clean, starts/ends on proper sentence boundaries. Two issues found, both fixed:
- Code blocks lost their fence markers and ran directly into the preceding sentence with no separator (seen in chunk 628, `querying-data-with-sql`). Fixed in `MarkdownSectionExtractor::extract()` — `FencedCode`/`IndentedCode` nodes are re-wrapped in ` ``` ` fences with blank lines around them before being appended to the chunk.
- Two junk test files (`dawd.md`, `dwadaw.md`) were sitting in the real corpus and produced garbage chunks (577, 585). Deleted both files and force-deleted their `Document`/`Chunk` rows, then re-ran the ingest. Corpus is now 38 real documents, 131 chunks.

You can run `app/Console/Commands/TestMarkdownSectionExtractor.php` to test the extractor/merge/split logic.

for the tsvectors, I chose for the ranking ts_rank_cs, and for the function I choose websearch_to_tsquery, since it takes a raw input as the query which is nicer.

ts_rank_cs is similiar to ts_rank, which basically means it checks the count of how many query words are present and it ignores the position.

however ts_rank_cs, not only counts the words but it also includes the position of the vector and then decides which one is close.

ts_rank_cs works in the following, it scores each other cover using the following formula

cover score = word weight / (1 + number of other words inside the cover)

the word weight is what we choose like weight, otherwise the default weight D.

and the ranking is then eventually chosen base on everything.

