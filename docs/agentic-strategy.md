# Agentic strategy — Vue migration

How the slices in [vue-migration-plan.md](vue-migration-plan.md) get built by agents, with token cost kept low.

## Roles

| Agent | Model | Job | Preloaded skills |
|---|---|---|---|
| Main session (you + Claude) | — | Starts one `migration-planner` per slice, relays its report, you open the PR | — |
| `migration-planner` | Opus, high | Explores, fetches docs once, writes the slice handoff, delegates, reviews diffs, runs final verification. **Never writes app code.** | `vue-best-practices`, `shadcn-vue` |
| `migration-implementer` × 1 (× 2 in Slice 2 only) | Sonnet, medium | Implements one unit of the handoff, test-first, only its own files. Never commits. | `vue-best-practices`, `shadcn-vue`, `tdd` |

Definitions: `.claude/agents/migration-planner.md`, `.claude/agents/migration-implementer.md`.

> `/implement` can't be preloaded or invoked by a subagent (`disable-model-invocation: true`), and it commits. Its workflow (TDD at seams, typecheck, single test files, full suite at the end, review) is written into `migration-implementer` instead, **without the commit step**. The planner does the review, so implementers don't run `/code-review` themselves.

## Flow per slice

```
main ──► migration-planner (Opus)
            │ 1. explore + ctx7 (once)
            │ 2. write docs/migration/slice-N.md  (unit(s), doc snippets, smoke list)
            ├──► migration-implementer (Sonnet)        one unit — Slices 0, 1, 3, 4
            │    or, Slice 2 only:
            ├──► migration-implementer A (Sonnet) ─┐  in parallel,
            ├──► migration-implementer B (Sonnet) ─┘  disjoint files, same working tree
            │ 3. review git diff → fixes via SendMessage to the same implementer
            │ 4. full tests + pint + build + vue-tsc + agent-browser smoke
            ▼
main ◄── [Thing][Action][Summary] report ──► you review, commit, open the PR
```

## Implementers per slice

**Default: one implementer.** Two only where the work genuinely splits.

| Slice | Implementers | Why |
|---|---|---|
| 0 Tests | 1 | ~10 feature tests, small |
| 1 Infra | 1 | Sequential: Wayfinder's Vite plugin needs the Composer package, and `shadcn-vue init` needs Vue/TS first |
| 2 Workshop | **2** | **A**: controller `index` → Inertia, `expectsJson()` seam, tests, `Workshop.vue`. **B**: `AppLayout`, `DocumentSidebar`, `IngestDialog`, `DocumentDialog`, `ChatPanel` |
| 3 Search | 1 | Controller + `Search.vue` + one card component, reusing Slice 2 |
| 4 Cleanup | 1 | Deletions + removing the seam |

Why not two everywhere: each implementer pays the same startup cost (preloaded skills, plan, `COMPONENTS.md`, handoff), the planner reviews twice, and parallel agents collide in the shared Sail setup even with disjoint files (`pint --dirty` reformats the other agent's PHP, `pnpm add` / `shadcn-vue add` both write `package.json` / `pnpm-lock.yaml` / `components.json`, and `vue-tsc` trips on half-written files).

### Rules for Slice 2's parallel units

- The planner writes the shared contract into the handoff **before** delegating: `types/index.ts` and every component's props/emits. Unit B owns `types/index.ts`, and Unit A imports it as specified.
- All `shadcn-vue add` / `pnpm add` calls belong to **one** unit (B). If A needs a component, B adds it.
- Parallel implementers run only their own test files and `vue-tsc`. They **don't** run `pint` or the full suite. The planner runs those once, after both finish.

## Why this is cheap

- **Opus plans once.** Exploration and ctx7 lookups happen once per slice in the planner. Their results go into the handoff file, so implementers don't redo them.
- **Short prompts.** "Implement `docs/migration/slice-N.md`" (or "Unit A of …" in Slice 2). The file carries the context, not the prompt.
- **Skills preloaded**, not discovered, so no exploratory Skill calls.
- **One implementer by default**: its context loads once, and there's one diff to review. Parallelism only where it pays (Slice 2).
- **Fixes go to the same implementer** through SendMessage, reusing its context instead of starting a fresh agent.
- **The main session stays thin**: it only sees the planner's final report.

## Guardrails

- **Backend barely changes.** Only controller return statements / prop shaping, the temporary `expectsJson()` seam, Inertia/Wayfinder wiring, route redirects and tests. Models, services, jobs, Form Requests, migrations, Resources and validation are off-limits. If a slice needs one of them, the planner reports `[Blocked]`.
- **UI** follows `COMPONENTS.md`: shadcn-vue only, theme tokens, no hex, `components/ui/` CLI-owned.
- **Dependencies** only from the approved list in the plan.
- **No agent commits, pushes or opens PRs.** You do that per slice.
- **Slice 0 goes first.** Its tests are the safety net every later slice must keep green.

## Worktrees: no

Run each slice on its own branch in the **main checkout**. Don't use `isolation: "worktree"` and don't create worktrees by hand.

- Sail is set up per folder: a worktree needs its own `vendor/`, `node_modules/`, containers and database, and would compete for ports 8000 / 5173 / 5432.
- A single implementer (or Slice 2's two, on disjoint files) doesn't need isolation.
- Tests, `vue-tsc`, the build and the agent-browser smoke pass need one running app that has all the slice's changes.
- Don't edit files by hand while a slice is running.

Exception: if you want to keep working on something else in this repo while a slice runs, put *that other work* in a worktree with its own `APP_PORT`, `VITE_PORT`, `FORWARD_DB_PORT` and `FORWARD_OLLAMA_PORT` in `.env` (every host port `compose.yaml` publishes), and run `vendor/bin/sail composer install` + `vendor/bin/sail pnpm install` there.

## Running a slice

See [ENTRYPOINT.md](../ENTRYPOINT.md) for the exact steps and prompt.
