export function initWordCount() {
    const content = document.getElementById('content');
    const count = document.getElementById('word-count');
    if (!content || !count) return;

    const update = () => {
        const words = content.value.trim().split(/\s+/).filter(Boolean).length;
        count.textContent = words + (words === 1 ? ' word' : ' words');
    };

    content.addEventListener('input', update);
    update();
}
