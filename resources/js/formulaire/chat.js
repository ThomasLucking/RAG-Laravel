export function initChat() {
    const form = document.getElementById('chat-form');
    if (!form) return;

    const input = document.getElementById('chat-input');
    const send = document.getElementById('chat-send');
    const thread = document.getElementById('chat-thread');
    const empty = document.getElementById('chat-empty');
    const messages = document.getElementById('chat-messages');

    const resize = () => {
        input.style.height = 'auto';
        input.style.height = input.scrollHeight + 'px';
        send.disabled = input.value.trim() === '';
    };

    input.addEventListener('input', resize);

    input.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && !event.shiftKey && !event.isComposing) {
            event.preventDefault();
            form.requestSubmit();
        }
    });

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        const query = input.value.trim();
        if (!query) return;

        const bubble = document.createElement('div');
        bubble.className = 'ml-auto w-fit max-w-[80%] rounded-2xl bg-[#1A1F27] px-4 py-2.5 text-sm text-[#E4E7EB] whitespace-pre-wrap break-words';
        bubble.textContent = query;

        empty.classList.add('hidden');
        messages.classList.remove('hidden');
        messages.appendChild(bubble);
        thread.scrollTop = thread.scrollHeight;

        input.value = '';
        resize();
    });
}
