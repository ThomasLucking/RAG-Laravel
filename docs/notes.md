ollama as a docker image link

docker run -d -v ollama:/root/.ollama -p 11434:11434 --name ollama ollama/ollama


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

