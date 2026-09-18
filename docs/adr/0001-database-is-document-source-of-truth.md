# ADR 0001 — The database is the Document source of truth

**Status:** Accepted · 2026-09-18

## Context

Documents lived in two places: `docs/data/*.md` (title, summary, tags, content as YAML frontmatter + markdown) and the `documents` table (id, origin, tags, chunks). Every Workshop request had to sync them by hand. Reads re-parsed files, writes wrote the file and then shelled into `app:ingest-corpus` (a full-corpus sweep), and deletes bypassed ingestion. The two parsers disagreed on line endings, and creating a Document could silently overwrite an existing file.

## Decision

- The `documents` table owns every Document field, including `content`. The app never writes to `docs/data`.
- `docs/data` is a **corpus seed**. `app:ingest-corpus` imports it one way and only creates slugs that don't exist yet (including soft-deleted ones). The DB wins.
- Saving a Document is synchronous: one transaction writes the row and syncs its tags.
- Documents are soft-deleted (a tombstone blocks re-import), and their chunks are hard-deleted.
- A slug is fixed at creation and unique across live and deleted Documents.

## Scope

This decision covers ingesting the corpus seed and create/read/edit/delete of Documents. Indexing (chunking with `MarkdownSectionExtractor`, embeddings, search) is out of scope and comes after CRUD. It should hook in after a Save without changing the CRUD flow.

## Consequences

- Workshop CRUD is plain Eloquent behind resource routes. YAML parsing exists only in the importer.
- Edits made in the Workshop don't flow back to git. If that's ever needed, add a one-way export command. Don't make files canonical again.