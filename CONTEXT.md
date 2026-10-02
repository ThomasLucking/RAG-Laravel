# Domain glossary

**Document** — a knowledge article (title, summary, tags, updated date, markdown content). The `documents` table is its single source of truth.

**Corpus seed** — the `docs/data/*.md` files with YAML frontmatter. They are imported one way into Documents by `app:ingest-corpus` and are never written by the app. The import only creates Documents whose slug does not exist yet; an existing Document is never overwritten by its seed file (the DB wins).

**Slug** — a Document's permanent URL key. It is derived from the title (or the seed filename) at creation and never changes, even when the title is edited. Slugs are unique across all Documents, including Deleted Documents, so creating a Document whose slug is taken (live or deleted) is rejected.

**Origin** — where a Document came from: `imported` (from the corpus seed) or `manual` (created in the workshop UI).

**Save** — writing a Document row and syncing its tags from validated input, in one transaction.

**Deleted Document** — a soft-deleted Document. Its chunks are hard-deleted, but the row stays as a tombstone so the corpus seed never re-imports its slug.

**Chunk** — one indexed section of a Document, produced by splitting its content on markdown headings. "Fragment" is a retired synonym.

**Chunk match** — a Chunk returned by a search, together with its score (full-text rank or vector similarity/distance).

**Workshop** — the UI where users create, edit, and delete Documents (formerly called "formulaire"; that name is retired).
