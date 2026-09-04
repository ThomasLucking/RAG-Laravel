const extractGenerationMetrics = (data) => {
    const metrics = {
        model: data.model,
        totalMs: data.total_duration ? data.total_duration / 1e6 : null, // nanoseconds -> milliseconds
        loadMs: data.load_duration ? data.load_duration / 1e6 : null,
        promptEvalMs: data.prompt_eval_duration ? data.prompt_eval_duration / 1e6 : null,
        promptEvalCount: data.prompt_eval_count ?? null,
        evalMs: data.eval_duration ? data.eval_duration / 1e6 : null,
        evalCount: data.eval_count ?? null,
    };
    return metrics;
};

const extractEmbeddingMetrics = (data, model, elapsedMs) => {
    return {
        model, 
        elapsedMs,
        vectorLength: data.embedding?.length ?? null,
    };
};


const responseFromLlamaNormal = await fetch('http://localhost:11434/api/generate', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        model: 'llama3.2:3b',
        prompt: 'What is the capital of France?',
        stream: false,
    }),
});

const llamaData = await responseFromLlamaNormal.json();
const llamaMetrics = extractGenerationMetrics(llamaData);


const embeddingModel = 'nomic-embed-text';
const embeddingStart = performance.now();

const responseFromEmbeddingModel = await fetch('http://localhost:11434/api/embeddings', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        model: embeddingModel,
        prompt: 'What is the capital of France?',
    }),
});

const embeddingData = await responseFromEmbeddingModel.json();
const embeddingElapsedMs = performance.now() - embeddingStart;
const embeddingMetrics = extractEmbeddingMetrics(embeddingData, embeddingModel, embeddingElapsedMs);

console.log('Llama generation:', llamaMetrics);
console.log('Embedding:', embeddingMetrics);