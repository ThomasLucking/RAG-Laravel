# Search statistics

Benchmarks for full-text search (FTS) and meaning search (RAG) across two embedding models, run on the same corpus and the same six questions.

## Machine

| | |
| --- | --- |
| Model | MacBook Pro (Mac16,1) |
| Chip | Apple M4 — 10-core CPU (4 performance + 6 efficiency), 10-core GPU, Metal 4 |
| Memory | 16 GB unified |
| OS | macOS 27.0.1 |
| Docker | Colima (Docker 29.5.2), Ubuntu 24.04 VM, **2 vCPU / 2 GB RAM** |

## Software

| Component | Version |
| --- | --- |
| PHP | 8.5.11 |
| Laravel | 13.30.1 |
| laravel/ai | 0.11.2 |
| PostgreSQL | 18.6 (aarch64) |
| pgvector | 0.8.7 |
| Ollama | 0.35.0 (container and host) |
| Corpus | 38 documents, 131 chunks |

## Embedding models

| | nomic-embed-text | qwen3-embedding:4b |
| --- | --- | --- |
| Architecture | nomic-bert | qwen3 |
| Parameters | 137M | 4.0B |
| Quantization | F16 | Q4_K_M |
| Size on disk | 274 MB | 2.5 GB |
| Native dimensions | 768 | 2560 |
| Stored dimensions | 768 | 768 (Matryoshka truncation, no migration) |
| Context length | 2048 | 40960 |
| Runs on | `ollama` container (CPU, 2 vCPU) | host Ollama (Apple M4 GPU, Metal) |
| Why there | default setup | does not fit in the 2 GB Docker VM (`ggml_aligned_malloc: insufficient memory`) |
| `OLLAMA_URL` | `http://ollama:11434` | `http://host.docker.internal:11434` |
| Query prefix | `search_query: ` | `Instruct: Given a web search query, retrieve relevant passages that answer the query\nQuery: ` |
| Document prefix | `search_document: ` | none |

> The two models run on different hardware (container CPU vs host GPU), so embedding latency compares setups, not models alone.

## Summary

| Metric | nomic-embed-text | qwen3-embedding:4b |
| --- | --- | --- |
| Single embedding (`evaluate.js`) | 13.45 ms | — |
| Query embedding, warm (uncached) | 19.6–30.4 ms | 110.4–114.9 ms (model pinned, `keep_alive: -1`) |
| Query embedding, first call | 81 ms (cold model) | 265.4 ms (load); 438 ms after re-pin |
| Query embedding, Laravel cache hit | — | 0.3–16.7 ms |
| Repeated identical input (Ollama prompt cache) | — | 35–63 ms |
| Re-embed corpus (131 chunks) | — | 1 min 35 s |
| RAG SQL execution | 0.280–0.591 ms | 0.221–0.662 ms |
| Chunks passing `min_similarity` 0.5 | 78–130 of 131 | 2–5 of 131 |
| Best cosine distance | — | 0.3065–0.4557 |
| Top-1 on the same document as the other model | 6 / 6 | 6 / 6 |

## Results per question

`qwen3 embed` is the first `EmbeddingService::embedQuery` call per question from the app container, with the Laravel cache cleared (`php artisan cache:clear`) and the model already loaded on the host. Re-measured 2026-10-02; nomic timings are from the earlier run.

| # | FTS rows matched / removed | FTS exec | nomic rows kept / removed | nomic exec | nomic embed | qwen3 rows kept / removed | qwen3 exec | qwen3 embed |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| 1 | 41 / 90 | 0.708 ms | 118 / 13 | 0.591 ms | 81 ms (cold) | 2 / 129 | 0.221 ms | 111.8 ms |
| 2 | 41 / 90 | 0.645 ms | 110 / 21 | 0.448 ms | 19.6 ms | 4 / 127 | 0.396 ms | 114.9 ms |
| 3 | 82 / 49 | 0.462 ms | 130 / 1 | 0.299 ms | 20.8 ms | 5 / 126 | 0.662 ms | 113.4 ms |
| 4 | 106 / 25 | 0.794 ms | 78 / 53 | 0.280 ms | 30.4 ms | 2 / 129 | 0.544 ms | 110.4 ms |
| 5 | 71 / 60 | 0.519 ms | 123 / 8 | 0.328 ms | 20.9 ms | 4 / 127 | 0.531 ms | 113.6 ms |
| 6 | 33 / 98 | 0.278 ms | 116 / 15 | 0.296 ms | 20 ms | 2 / 129 | 0.416 ms | 112.0 ms |

FTS tsquery per question (after stemming + `&` → `|`):

| # | tsquery |
| --- | --- |
| 1 | `effici \| present \| program \| languag` |
| 2 | `import \| element \| creat \| good \| continu \| integr` |
| 3 | `best \| method \| work \| team \| program \| project` |
| 4 | `differ \| virtual \| machin \| docker \| contain \| use \| one` |
| 5 | `favor \| use \| typescript \| laravel \| build \| full \| stack \| applic` |
| 6 | `test-driven-develop <-> test <-> driven <-> develop \| advantag \| use` |

qwen3-embedding:4b top 3 chunks (document → section, cosine distance):

| # | Chunks |
| --- | --- |
| 1 | `presenting-a-technical-subject-to-an-audience` → Narrowing the subject and finding sources (0.4557)<br>`presenting-a-technical-subject-to-an-audience` → Adapting depth to the audience (0.4659) |
| 2 | `continuous-integration-and-continuous-delivery` → The integration problem CI was invented to solve (0.3808)<br>`continuous-integration-and-continuous-delivery` → From build to artifact (0.4419)<br>`continuous-integration-and-continuous-delivery` → The pipeline as an assembly line (0.4564) |
| 3 | `working-well-inside-a-development-team` → Communication and attitude (0.4179)<br>`working-well-inside-a-development-team` → Asking for help and sharing knowledge (0.4378)<br>`delivering-a-laravel-application-as-a-scrum-team` → Collaborating on one codebase (0.4486) |
| 4 | `running-applications-in-containers-with-docker` → Containers versus virtual machines (0.3065)<br>`running-applications-in-containers-with-docker` → Building and running a container (0.4763) |
| 5 | `building-a-full-stack-typescript-application` → From exercise to a usable product (0.4067)<br>`building-a-full-stack-typescript-application` → Layered architecture on both sides (0.4253)<br>`delivering-a-laravel-application-as-a-scrum-team` → A capstone built around a real stakeholder (0.4418) |
| 6 | `practising-test-driven-development-with-katas` → The red-green-refactor cycle (0.4325)<br>`practising-test-driven-development-with-katas` → TDD as a design technique (0.4712) |

## Observations

- **Top-1 document:** both models land on the same document for every question; qwen3 picks a different section for questions 1 and 6 (narrowing a subject and choosing sources instead of adapting depth; the red-green-refactor cycle instead of the TDD intro).
- **Similarity threshold:** at `SEARCH_MIN_SIMILARITY=0.5`, nomic keeps almost the whole corpus, so the filter barely filters. qwen3 spreads distances wider, so the same threshold keeps only 2–5 relevant chunks.
- **Latency:** SQL stays sub-millisecond for both (same sequential scan). The cost is in embedding: qwen3 is ~4–5× slower per new query even on the GPU (4.0B vs 137M parameters, 2560 native dimensions truncated to 768, a longer instruct prefix, and a hop from the Colima VM to the host), and needs more RAM than the Docker VM provides.
- **Caching:** `EmbeddingService` calls `->cache()`, so a repeated query is served from the Laravel cache (database store) in about 1 ms, whatever `AI_CACHE_EMBEDDINGS` is set to. Clear it with `php artisan cache:clear` before benchmarking. Ollama also reuses its prompt cache when the exact same input is sent again (35–63 ms), so timings must use new queries.
- **FTS vs RAG:** FTS misses the expected document on questions 1 and 6, where both RAG models find it. On question 4 no mode returns `virtual-machines-and-when-to-use-them`; both RAG models return the Docker document instead.

## Questions

### 1. How can I efficiently do a presentation for a programming language?

Expected document: `presenting-a-technical-subject-to-an-audiene`

| Mode | Top result |
| --- | --- |
| FTS | Inside a modern development environment |
| RAG · nomic-embed-text | Presenting a technical subject to an audience |
| RAG · qwen3-embedding:4b (distance 0.4557) | Presenting a technical subject to an audience |

<details><summary>Chunk text</summary>

**FTS** — A formatter rearranges code without changing what it does: indentation,line breaks, spacing, alignment. It never renames anything, adds logic,or fixes a bug, and typically runs on save so a project's style staysconsistent as long as every contributor uses the same pinned version. Alinter asks a different question: does this code contain a riskypattern, an unused binding, or a style violation worth flagging? Somelinters can apply a fix automatically, but their primary role is tosurface a problem, not to reformat correct code. A squiggle under aline can come from a language server, a linter, or a compiler, and eachpoints to a different next step.

**RAG · nomic-embed-text** — The same subject is presented differently to peers who already work in thedomain than to an audience with no background in it. Presenting to fellowapprentices usually means assuming basic programming knowledge but notfamiliarity with the specific subject, which shapes how much foundationalexplanation opens the talk versus how quickly it moves to comparison andnuance.Depth also interacts with time: a short slot forces choices about whichconcepts get a full explanation, which get a one-line mention, and whichare cut entirely. Slides carry illustrations and key terms while the spokenexplanation carries the reasoning; the two should complement rather thanrepeat each other.

**RAG · qwen3-embedding:4b** — A technical subject — a programming language, a tool, an architecturestyle, a development practice — is almost always too wide for a short talk.The first task is deciding which angles deserve airtime: origin andcontext, core mechanics, typical use cases, trade-offs againstalternatives. A subject narrowed to a handful of angles, explored withconcrete detail, gives an audience something to retain; one coveredend-to-end tends to stay shallow everywhere.Research then draws on several kinds of sources, each useful for adifferent angle: official documentation for accuracy on how somethingworks, encyclopedic overviews for context and history, blog posts andconference talks for practical or opinionated perspectives, and public coderepositories for evidence of real-world usage. None of these should betrusted blindly — documentation can lag behind the latest release, blogposts reflect one author's experience, and a project's own site emphasizesstrengths over weaknesses. Cross-checking a claim across independentsources matters most for anything time-sensitive, since technicalecosystems move fast and older material goes stale quickly.

</details>

### 2. What are the important elements to create a good Continuous integration?

Expected document: `continuous-integration-and-continuous-delivery`

| Mode | Top result |
| --- | --- |
| FTS | Continuous integration and continuous delivery |
| RAG · nomic-embed-text | Continuous integration and continuous delivery |
| RAG · qwen3-embedding:4b (distance 0.3808) | Continuous integration and continuous delivery |

<details><summary>Chunk text</summary>

**FTS** — Every commit is a promise that the project still works. Continuousintegration and continuous delivery, together shortened to CI/CD, are thepractice of checking that promise automatically, every time code changes,and of moving a verified change toward production without manual,repetitive handling.

**RAG · nomic-embed-text** — Every commit is a promise that the project still works. Continuousintegration and continuous delivery, together shortened to CI/CD, are thepractice of checking that promise automatically, every time code changes,and of moving a verified change toward production without manual,repetitive handling.

**RAG · qwen3-embedding:4b** — Every commit is a promise that the project still works. Continuousintegration and continuous delivery, together shortened to CI/CD, are thepractice of checking that promise automatically, every time code changes,and of moving a verified change toward production without manual,repetitive handling.

</details>

### 3. What are the best methods to work in a team during a programming project.

Expected document: `working-as-a-team-with-agile-and-scrum`

| Mode | Top result |
| --- | --- |
| FTS | Delivering a Laravel application as a Scrum team |
| RAG · nomic-embed-text | Working well inside a development team |
| RAG · qwen3-embedding:4b (distance 0.4179) | Working well inside a development team |

<details><summary>Chunk text</summary>

**FTS** — Because the whole team commits to a single Laravel repository, the projectalso exercises collaboration mechanics that smaller solo projects do notrequire: consistent branching, pull requests, and reviews that keep a sharedhistory readable.

**RAG · nomic-embed-text** — Working on a shared codebase is rarely blocked by tooling. It isblocked by how people talk to each other about the work. Listening toa teammate's idea, even one that clashes with a personal preference,is a skill on its own, separate from having good ideas. Explaining aposition clearly matters just as much: stating the reasoning behindit, not only the conclusion, lets others evaluate it instead ofguessing.Trust is the resource that makes this possible. A team without trusthesitates before raising problems, slowing down exactly when speedmatters most. It is built through small, repeated acts, such as doingwhat was said and admitting uncertainty honestly, and it erodes farfaster than it forms.

**RAG · qwen3-embedding:4b** — Working on a shared codebase is rarely blocked by tooling. It isblocked by how people talk to each other about the work. Listening toa teammate's idea, even one that clashes with a personal preference,is a skill on its own, separate from having good ideas. Explaining aposition clearly matters just as much: stating the reasoning behindit, not only the conclusion, lets others evaluate it instead ofguessing.Trust is the resource that makes this possible. A team without trusthesitates before raising problems, slowing down exactly when speedmatters most. It is built through small, repeated acts, such as doingwhat was said and admitting uncertainty honestly, and it erodes farfaster than it forms.

</details>

### 4. What is the difference between a virtual machine and a Docker container, and when should I use one over the other?

Expected document: `virtual-machines-and-when-to-use-them`

| Mode | Top result |
| --- | --- |
| FTS | Infrastructure as code with Ansible |
| RAG · nomic-embed-text | Running applications in containers with Docker |
| RAG · qwen3-embedding:4b (distance 0.3065) | Running applications in containers with Docker |

<details><summary>Chunk text</summary>

**FTS** — Ansible organizes a description around a few plain concepts: aninventory lists the machines to manage, usually grouped by role, so adescription can target "the web servers" instead of naming hosts oneby one, and a playbook is the checklist itself, an ordered set oftasks each naming the state a small part of a machine should be in,such as a package being present or a file holding given content.

**RAG · nomic-embed-text** — A container packages an application with the libraries and files itneeds, without a full operating system. Unlike a virtual machine, itshares the host kernel rather than booting its own or emulatinghardware, isolating only the application's process, filesystem, andnetwork. This makes containers far lighter and faster to start, atthe cost of a narrower isolation boundary.

**RAG · qwen3-embedding:4b** — A container packages an application with the libraries and files itneeds, without a full operating system. Unlike a virtual machine, itshares the host kernel rather than booting its own or emulatinghardware, isolating only the application's process, filesystem, andnetwork. This makes containers far lighter and faster to start, atthe cost of a narrower isolation boundary.

</details>

### 5. When can you favor using typescript or laravel for building full stack applications.

Expected document: `building-a-full-stack-typescript-application`

| Mode | Top result |
| --- | --- |
| FTS | Delivering a Laravel application as a Scrum team |
| RAG · nomic-embed-text | Building a full-stack TypeScript application |
| RAG · qwen3-embedding:4b (distance 0.4067) | Building a full-stack TypeScript application |

<details><summary>Chunk text</summary>

**FTS** — This project is the largest team exercise of the training. A team of four tosix apprentices builds a full web application for a stakeholder who plays therole of a client. Unlike earlier exercises, the product idea is not given inadvance: the team must contact the stakeholder, schedule a first meeting, anddiscover the business need directly, the way a professional team would.

**RAG · nomic-embed-text** — A tutorial project and a small real product differ less in code volume thanin the seams between layers. A product needs consistent error handlingacross every endpoint, pagination on any list that can grow without bound,and a setup process that lets someone unfamiliar with the code run itlocally without guessing missing steps.Typing alone does not guarantee any of this: a fully typed application canstill return inconsistent error shapes, skip pagination, or leave thedatabase schema implicit. The type system removes one category of mistake —shape mismatches — and leaves the rest, such as choosing what belongs in theAPI layer versus the data layer, to the design of the application itself.

**RAG · qwen3-embedding:4b** — A tutorial project and a small real product differ less in code volume thanin the seams between layers. A product needs consistent error handlingacross every endpoint, pagination on any list that can grow without bound,and a setup process that lets someone unfamiliar with the code run itlocally without guessing missing steps.Typing alone does not guarantee any of this: a fully typed application canstill return inconsistent error shapes, skip pagination, or leave thedatabase schema implicit. The type system removes one category of mistake —shape mismatches — and leaves the rest, such as choosing what belongs in theAPI layer versus the data layer, to the design of the application itself.

</details>

### 6. what is test-driven-development and what are the advantages of using it.

Expected document: `practising-test-driven-development-with-katas`

| Mode | Top result |
| --- | --- |
| FTS | Adding types to JavaScript with TypeScript |
| RAG · nomic-embed-text | Practising test-driven development with katas |
| RAG · qwen3-embedding:4b (distance 0.4325) | Practising test-driven development with katas |

<details><summary>Chunk text</summary>

**FTS** — JavaScript only reports many mistakes once a bad line actually runs:calling a method that does not exist, or passing a string where a numberwas expected. TypeScript adds a layer on top of JavaScript that checksthese assumptions before the program executes, by describing the shape ofvalues ahead of time.

**RAG · nomic-embed-text** — Test-driven development is often introduced as a way to obtain testcoverage, but that framing undersells what the practice changes: writingthe test before the code forces a decision about what the code should do,as observable behavior, before any decision about how it is implemented.

**RAG · qwen3-embedding:4b** — Test-driven development inverts the usual order of writing software: adeveloper writes a test expressing one requirement first, watches itfail, then writes the code that satisfies it. The cycle repeats onerequirement at a time and is named after its three phases.

</details>


### Conlusion,

## Conclusion

**FTS vs RAG.** FTS matches basically words, and not the meaning so it struggles a little bit depending on the question you ask.

**nomic-embed-text vs qwen3-embedding:4b.** Both models returned the same top document
for all 6 questions, so the bigger model didn't improve top-1 accuracy here. The
differences are:
- **Latency:** qwen3 is a lot slower than the normal embedding model
- **Score spread:** at `min_similarity` 0.5, nomic keeps 78–130 of 131 chunks, so the
  threshold barely filters anything. qwen3 keeps 2–5. For RAG + LLM, qwen3 gives
  tighter context without retuning the threshold.

**When to use which.** 

SQL execution is around the same, but FTS is faster since it doesn't rely on 2 models, 1 for embeddings and 1 for generation of the summary,
if for example an application needs to do FTS across data or documents, and it wants to fine similiar words then what it is said in the query. then it is better to use FTS.

however if you want to implement RAG inside of an application to really capture the meaning of the query across multiple chunks. then implementing RAG would be better in this case.
But you need to consider the price of hosting embedding model and the LLM. 

**Limits.** 6 questions, 131 chunks, sequential scans only, different hardware for the
two embedding models. Behaviour at larger scale (with GIN / HNSW indexes) wasn't tested.



## EXPLAIN ANALYZE

Run with `EXPLAIN (ANALYZE, BUFFERS)` against the local database.

### Generated SQL

FTS (`Chunk::fullTextSearch`):

```sql
select * from (
    select DISTINCT ON (document_id) *,
        ts_rank_cd(search_vector, replace(websearch_to_tsquery('english', :q)::text, ' & ', ' | ')::tsquery, 1) AS rank
    from chunks
    where search_vector @@ replace(websearch_to_tsquery('english', :q)::text, ' & ', ' | ')::tsquery
    order by document_id asc, rank desc
) as chunks
order by rank desc
limit 20
```

RAG (`MeaningSearchController`):

```sql
select chunks.*, (embeddings <=> :vector) as distance
from chunks
where (embeddings <=> :vector) <= 0.5
order by (embeddings <=> :vector) asc
limit 10
```

### Plans

FTS (question 1):

```
Limit  (actual time=0.660..0.662 rows=20)
  ->  Sort  Sort Key: ts_rank_cd(...) DESC   (quicksort, 72kB)
        ->  Unique  (rows=24)                          -- DISTINCT ON (document_id)
              ->  Sort  Sort Key: document_id, ts_rank_cd(...) DESC   (quicksort, 86kB)
                    ->  Seq Scan on chunks  (rows=41)
                          Filter: search_vector @@ 'effici' | 'present' | 'program' | 'languag'
                          Rows Removed by Filter: 90
Buffers: shared hit=280
Execution Time: 0.708 ms
```

RAG · nomic-embed-text (question 1):

```
Limit  (actual time=0.573..0.574 rows=10)
  ->  Sort  Sort Key: embeddings <=> '[...]'   (top-N heapsort, 53kB)
        ->  Seq Scan on chunks  (rows=118)
              Filter: (embeddings <=> '[...]') <= 0.5
              Rows Removed by Filter: 13
Buffers: shared hit=536
Execution Time: 0.591 ms
```

RAG · qwen3-embedding:4b (question 1):

```
Limit  (cost=43.14..43.16 rows=10 width=1451) (actual time=0.212..0.213 rows=2.00 loops=1)
  Buffers: shared hit=445
  ->  Sort  (cost=43.14..43.25 rows=44 width=1451) (actual time=0.212..0.212 rows=2.00 loops=1)
        Sort Key: ((embeddings <=> '[...]'::vector))
        Sort Method: quicksort  Memory: 29kB
        Buffers: shared hit=445
        ->  Seq Scan on chunks  (cost=0.00..42.19 rows=44 width=1451) (actual time=0.098..0.209 rows=2.00 loops=1)
              Filter: ((embeddings <=> '[...]'::vector) <= '0.5'::double precision)
              Rows Removed by Filter: 129
              Buffers: shared hit=445
Planning Time: 0.026 ms
Execution Time: 0.221 ms
```

## Generation model

Local LLM benchmarks are captured via [`evaluate.js`](evaluate.js), which hits a local Ollama instance and logs generation/embedding timings. Results are recorded in [`llm_metrics.md`](llm_metrics.md).

**Generation** (`llama3.2:3b`)

| Metric | Value |
| --- | --- |
| Total duration | 655.19 ms |
| Load duration | 3.10 ms |
| Prompt eval duration | 83.24 ms |
| Prompt eval count | 32 tokens |
| Eval duration | 567.11 ms |
| Eval count | 8 tokens |
