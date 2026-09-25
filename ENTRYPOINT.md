# Entry point — Vue migration

Start here. Run one slice at a time, in order: **0 → 1 → 2 → 3 → 4**.

| Read for context | |
|---|---|
| What gets built | [docs/vue-migration-plan.md](docs/vue-migration-plan.md) |
| How agents build it | [docs/agentic-strategy.md](docs/agentic-strategy.md) |
| UI rules | [COMPONENTS.md](COMPONENTS.md) |
| Vocabulary / decisions | [CONTEXT.md](CONTEXT.md), [docs/adr/](docs/adr/) |

## 1. One-time setup (before Slice 0)

1. Merge PR #21 (`user_query/embedding`) into `main`.
2. Get the planning docs onto `main`: open a PR from `migratation/from-blade-to-vue` (docs, agents, skills only) and merge it.
3. Complete the Sail bootstrap in the plan ([Environment (Sail)](docs/vue-migration-plan.md#environment-sail)):
   ```shell
   composer install --ignore-platform-reqs     # once, only to create vendor/
   # .env: APP_PORT=8000, DB_HOST=pgsql
   sail up -d && sail composer install && sail pnpm install && sail artisan migrate
   sail artisan test --compact                  # baseline must be green
   ```

## 2. Per slice

```shell
git switch main && git pull
git switch -c migration/slice-<N>
sail up -d
```

No worktree. Stay in the main checkout, and don't edit files while the agents run (see [strategy](docs/agentic-strategy.md#worktrees-no)).

Then start `claude` in the repo root and paste:

```
Use the migration-planner agent for Slice <N> of docs/vue-migration-plan.md.
Follow docs/agentic-strategy.md: write the handoff to docs/migration/slice-<N>.md,
delegate to one migration-implementer (two in parallel only for Slice 2),
review their diffs, run the slice's "Done when" checks through Sail,
and report back in [Thing][Action][Summary] format. Do not commit.
```

## 3. After the planner reports

1. Read the report. If there's a `[Blocked]` line, decide on it and re-run the planner with your answer.
2. Review the diff yourself: `git diff`, and open `http://localhost:8000`.
3. Commit, push, open the PR into `main`, and merge it.
4. Move to the next slice.

## Slice checklist

- [ ] Slice 0 — Characterisation tests
- [ ] Slice 1 — Infrastructure (Inertia, Vue, TS, shadcn-vue, Wayfinder, `.ai/rules/`)
- [ ] Slice 2 — Workshop page
- [ ] Slice 3 — Search page
- [ ] Slice 4 — Cleanup
