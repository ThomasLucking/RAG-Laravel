# Agentic strategy — Vue migration

How the slices in [vue-migration-plan.md](vue-migration-plan.md) get built by agents, with token cost kept low.

## Roles

| Agent | Model | Job | Preloaded skills |
|---|---|---|---|
| Main session (you + Claude) | — | Starts one `migration-planner` per slice, relays its report, you open the PR | — |
| `migration-planner` | Opus, high | Explores, fetches docs once, writes the slice handoff, delegates, reviews diffs, runs final verification. **Never writes app code.** | `vue-best-practices`, `shadcn-vue` |
| `migration-implementer` × 2 | Sonnet, medium | Implements one unit of the handoff, test-first, only its own files. Never commits. | `vue-best-practices`, `shadcn-vue`, `tdd` |

Definitions: `.claude/agents/migration-planner.md`, `.claude/agents/migration-implementer.md`.

> `/implement` can't be preloaded or invoked by a subagent (`disable-model-invocation: true`), and it commits. Its workflow (TDD at seams, typecheck, single test files, full suite at the end, review) is written into `migration-implementer` instead, **without the commit step**. The planner does the review, so implementers don't run `/code-review` themselves.

## Flow per slice

```
main ──► migration-planner (Opus)
            │ 1. explore + ctx7 (once)
            │ 2. write docs/migration/slice-N.md  (Unit A / Unit B, disjoint files, doc snippets, smoke list)
            ├──► migration-implementer A (Sonnet) ─┐  in parallel,
            ├──► migration-implementer B (Sonnet) ─┘  same working tree
            │ 3. review git diff → fixes via SendMessage to the same implementer
            │ 4. tests + pint + build + vue-tsc + agent-browser smoke
            ▼
main ◄── [Thing][Action][Summary] report ──► you review, commit, open the PR
```

## Slice → unit split (guideline; the planner decides)

| Slice | Unit A | Unit B |
|---|---|---|
| 0 Tests | Document CRUD feature tests | Search + redirect feature tests |
| 1 Infra | Composer side: Inertia middleware, root view, Wayfinder, `boost:install`, `.ai/rules/` | JS side: Vite + TS + Vue bootstrap, `shadcn-vue init`, `types/index.ts` |
| 2 Workshop | Controller `index` → Inertia, `expectsJson()` seam, tests, `Workshop.vue` page | `AppLayout`, `DocumentSidebar`, `IngestDialog`, `DocumentDialog`, `ChatPanel` |
| 3 Search | Controller → Inertia, tests, `Search.vue` | Chunk match card component, any shared component tweaks |
| 4 Cleanup | Remove JSON branches + JSON-only tests | Delete Blade views and `resources/js/formulaire` |

The contract between the units is `types/index.ts` plus component prop signatures. The planner writes them into the handoff so both units can work in parallel without waiting on each other.

## Why this is cheap

- **Opus plans once.** Exploration and ctx7 lookups happen once per slice in the planner. Their results go into the handoff file, so implementers don't redo them.
- **Short prompts.** "Implement Unit A of `docs/migration/slice-N.md`". The file carries the context, not the prompt.
- **Skills preloaded**, not discovered, so no exploratory Skill calls.
- **Disjoint files** mean no merge conflicts and no re-work.
- **Fixes go to the same implementer** through SendMessage, reusing its context instead of starting a fresh agent.
- **The main session stays thin**: it only sees the planner's final report.

## Guardrails

- **Backend barely changes.** Only controller return statements / prop shaping, the temporary `expectsJson()` seam, Inertia/Wayfinder wiring, route redirects and tests. Models, services, jobs, Form Requests, migrations, Resources and validation are off-limits. If a slice needs one of them, the planner reports `[Blocked]`.
- **UI** follows `COMPONENTS.md`: shadcn-vue only, theme tokens, no hex, `components/ui/` CLI-owned.
- **Dependencies** only from the approved list in the plan.
- **No agent commits, pushes or opens PRs.** You do that per slice.
- **Slice 0 goes first.** Its tests are the safety net every later slice must keep green.

## Running a slice

```
Use the migration-planner agent for Slice <N> of docs/vue-migration-plan.md.
```

Before Slice 0: merge PR #21, rebase this branch, complete the Sail bootstrap in the plan (`sail up -d`).
