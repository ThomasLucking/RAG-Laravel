ollama as a docker image link

The `ollama` service is now managed by Sail via `compose.yaml` (see README) — start it with `./vendor/bin/sail up -d` rather than a standalone `docker run`. Running a separate `docker run ... --name ollama ...` alongside the Compose stack collides on the container name and host port, and stores models in a different volume (`ollama` here vs. `ollama_data` in `compose.yaml`), so don't use it.

this is to test if the model answers inside of the docker container
```sh
curl http://ollama:11434/api/generate -d '{
  "model": "llama3.2:3b",
  "prompt": "Why is the sky blue?",
  "stream": false
  
}'

curl http://ollama:11434/api/embeddings -d '{
  "model": "nomic-embed-text",
  "prompt": "Why is the sky blue?"
}'
```
and for the embedding model 

