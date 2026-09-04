# Project Setup

## Local Development Setup (Docker + pgvector + Ollama)

This project runs on Laravel Sail with two additional pieces layered on top of the default stack: a **pgvector-enabled PostgreSQL** image (for storing embeddings) and an **Ollama** service (for running local LLMs/embedding models). Both are defined as services in `compose.yaml` alongside the standard Sail services.

### 1. Installing dependencies

On a fresh clone, `vendor/` and `node_modules/` are not committed. Install both, then use Composer's Sail package to generate `compose.yaml` and add the `sail` script:

```bash
composer install
pnpm install
```

### 2. Configuring the environment

```bash
cp .env.example .env
```

`.env.example` defaults `DB_HOST` to `127.0.0.1`, which is wrong for this setup — the app runs in its own container and must reach PostgreSQL via the `pgsql` service name (see [Networking rules](#6-networking-rules) below), not `localhost`/`127.0.0.1`. Before starting the stack, set:

```env
DB_HOST=pgsql
```

Skipping this causes the app container to connect to itself instead of the `pgsql` service, and the migration step below will fail to connect.

### 3. Starting the stack

```bash
./vendor/bin/sail up -d
```

This builds/starts all services defined in `compose.yaml`, including `pgsql` and `ollama`. It does **not** download any Ollama models — that's a separate, manual step (see below).

### 4. PostgreSQL with pgvector

The `pgsql` service uses the `pgvector/pgvector` image instead of the plain `postgres` image, so the `vector` extension binary is available out of the box. It still needs to be enabled per-database via a migration:

```php
public function up(): void
{
    DB::statement('CREATE EXTENSION IF NOT EXISTS vector');
}
```

Run migrations as usual:

```bash
./vendor/bin/sail artisan migrate
```

### 5. Ollama service

The `ollama` service uses the official `ollama/ollama` image and is **not** a custom build — no Dockerfile is involved for it. It exposes:

- Port `11434` (mapped to the host, overridable via `FORWARD_OLLAMA_PORT` in `.env`, same pattern as `FORWARD_DB_PORT`)
- A named volume (`ollama_data:/root/.ollama`) so downloaded models **persist across `sail down` / `sail up` cycles**

**Pulling a model** is a manual step, run *after* the container is up (this is not defined in `compose.yaml` and does not happen automatically):

```bash
docker compose exec ollama ollama pull llama3.2:3b
docker compose exec ollama ollama pull nomic-embed-text
```

Once pulled, models stay in the volume — you don't need to re-pull them on every restart, only if the volume itself is removed (e.g. `sail down -v`).

### 6. Networking rules

All inter-container communication uses **Docker service names**, not `localhost` and not container IPs:

| From | To | Host to use |
|---|---|---|
| App container → PostgreSQL | `pgsql:5432` | service name |
| App container → Ollama | `ollama:11434` | service name |
| Your host machine (e.g. DB client, curl) → PostgreSQL | `localhost:5432` | forwarded port |
| Your host machine → Ollama | `localhost:11434` | forwarded port |
| Browser → Vite dev server | `localhost:5173` | forwarded port |

`localhost` inside a container refers to that container itself — using it for service-to-service calls is the most common source of connection errors in this setup.

### 7. Ollama API quick reference

```bash
# Text generation
curl http://ollama:11434/api/generate -d '{
  "model": "llama3.2:3b",
  "prompt": "Why is the sky blue?",
  "stream": false
}'

# Embeddings (for pgvector storage)
curl http://ollama:11434/api/embeddings -d '{
  "model": "nomic-embed-text",
  "prompt": "Why is the sky blue?"
}'

# List locally available models
curl http://ollama:11434/api/tags
```

Replace `ollama` with `localhost` when running these from your host machine instead of from inside another container.

### 8. Verifying the setup

- **Volume persistence:** pull a model, run `sail down` then `sail up -d`, and confirm `docker compose exec ollama ollama list` still shows it.
- **App → Ollama connectivity:** from inside the app container, `curl http://ollama:11434` should respond with `Ollama is running`.
- **App → Postgres connectivity:** confirm `DB_HOST=pgsql` (not `localhost`) in `.env`, matching the service name in `compose.yaml`.

## LLM Performance Metrics

Local LLM benchmarks are captured via [`docs/evaluate.js`](docs/evaluate.js), which hits a local Ollama instance and logs generation/embedding timings. Results are recorded in [`docs/llm_metrics.md`](docs/llm_metrics.md).

**Generation** (`llama3.2:3b`)

| Metric | Value |
| --- | --- |
| Total duration | 655.19 ms |
| Load duration | 3.10 ms |
| Prompt eval duration | 83.24 ms |
| Prompt eval count | 32 tokens |
| Eval duration | 567.11 ms |
| Eval count | 8 tokens |

**Embedding** (`nomic-embed-text`)

| Metric | Value |
| --- | --- |
| Elapsed time | 13.45 ms |
| Vector length | 768 |