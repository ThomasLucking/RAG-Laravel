# ADR 0002 — Inertia-native writes, JSON only for chat

**Status:** Accepted · 2026-09-25

## Context

The UI was Blade plus vanilla JS. Document create, read, update and delete went through `fetch()` against resource routes that returned JSON `DocumentResource`s. The JS rendered validation errors and DOM updates by hand. We are moving the frontend to Vue 3 + Inertia + shadcn-vue, one page at a time.

## Decision

- Pages are Inertia responses. Document writes (`store`, `update`, `destroy`) use Inertia's `useForm` / `router` and return redirects. Validation errors come through Inertia's shared `errors`.
- Opening a Document uses an optional, lazily evaluated `document` prop keyed by `?document=<slug>` on the current page, not a JSON `show` call.
- The chat query is the one sanctioned JSON endpoint. A chat thread is ephemeral client state, not page state.
- During the incremental migration, write actions return JSON when `$request->expectsJson()` (remaining Blade pages) and redirect otherwise. The final cleanup slice removes the JSON branch.

## Consequences

- `api.js`, `ValidationError` and the hand-written error rendering go away.
- The server owns page state. The client never keeps its own copy of the Document list.
- Adding another JSON endpoint needs a reason as strong as the chat's. The default is an Inertia prop or a partial reload.
