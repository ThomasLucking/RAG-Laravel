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

notes for the mld inside of `mld` 

from my understanding of the JSONB, type if that instead of stocking the exact text of the meta data of the files, we can stock the binary versions of it. which allows for a lot faster reads from the db.

and another advatange is that we can also add indexes to columns with the JSONB type. and also JSONB removes. the whitespaces too.


the flow of the basically the logic of the application is

1. Raw document (markdown file)
        ↓
2. Parse + split into chunks (by headers, then by size)
        ↓
3. For EACH chunk:
     a. Insert chunk text → tsvector generates automatically (it's a GENERATED column)
     b. Send chunk text → embedding model → get vector → store in embeddings column


after analysing the documents with claude there was 3 files that were inconsistent and 1 that had polution in it

`virtual-machines-and-when-to-use-them.md` had no frontmatter
`working-in-the-unix-command-line` had no title
`principles-that-keep-code-maintainable` had random html tags 
`relational-database-design-and-modelling.md` had databases instead of database so i changed it to database



I downloaded a new package called League\commonMark
https://commonmark.thephpleague.com/2.x/customization/abstract-syntax-tree/

to parse the markdown for to ingest the corpus, I was also thinking of using an AST to help me decide the chunk sizes regarding for database.


todo list:
  add 1 index for the embedding column
  add 1 index for the tsvector to the column