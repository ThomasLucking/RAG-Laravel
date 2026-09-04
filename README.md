# Project Setup

## Local Development Setup (Docker + pgvector + Ollama)

This project runs on Laravel Sail with two additional pieces layered on top of the default stack: a **pgvector-enabled PostgreSQL** image (for storing embeddings) and an **Ollama** service (for running local LLMs/embedding models). Both are defined as services in `compose.yaml` alongside the standard Sail services.

### 1. Starting the stack

```bash
./vendor/bin/sail up -d
```

This builds/starts all services defined in `compose.yaml`, including `pgsql` and `ollama`. It does **not** download any Ollama models — that's a separate, manual step (see below).

### 2. PostgreSQL with pgvector

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

### 3. Ollama service

The `ollama` service uses the official `ollama/ollama` image and is **not** a custom build — no Dockerfile is involved for it. It exposes:

- Port `11434` (mapped to the host)
- A named volume (`ollama_data:/root/.ollama`) so downloaded models **persist across `sail down` / `sail up` cycles**

**Pulling a model** is a manual step, run *after* the container is up (this is not defined in `compose.yaml` and does not happen automatically):

```bash
docker exec -it ollama ollama pull llama3.2:3b
docker exec -it ollama ollama pull nomic-embed-text
```

Once pulled, models stay in the volume — you don't need to re-pull them on every restart, only if the volume itself is removed (e.g. `sail down -v`).

### 4. Networking rules

All inter-container communication uses **Docker service names**, not `localhost` and not container IPs:

| From | To | Host to use |
|---|---|---|
| App container → PostgreSQL | `pgsql:5432` | service name |
| App container → Ollama | `ollama:11434` | service name |
| Your host machine (e.g. DB client, curl) → PostgreSQL | `localhost:5432` | forwarded port |
| Your host machine → Ollama | `localhost:11434` | forwarded port |
| Browser → Vite dev server | `localhost:5173` | forwarded port |

`localhost` inside a container refers to that container itself — using it for service-to-service calls is the most common source of connection errors in this setup.

### 5. Ollama API quick reference

```bash
# Text generation
curl http://ollama:11434/api/generate -d '{
  "model": "llama3.2:3b",
  "prompt": "Why is the sky blue?",
  "stream": false
}'

# Chat (multi-turn)

# Embeddings (for pgvector storage)
curl http://ollama:11434/api/embeddings -d '{
  "model": "nomic-embed-text",
  "prompt": "Why is the sky blue?"
}'

# List locally available models
curl http://ollama:11434/api/tags
```

Replace `ollama` with `localhost` when running these from your host machine instead of from inside another container.

### 6. Verifying the setup

- **Volume persistence:** pull a model, run `sail down` then `sail up -d`, and confirm `docker exec -it ollama ollama list` still shows it.
- **App → Ollama connectivity:** from inside the app container, `curl http://ollama:11434` should respond with `Ollama is running`.
- **App → Postgres connectivity:** confirm `DB_HOST=pgsql` (not `localhost`) in `.env`, matching the service name in `compose.yaml`.