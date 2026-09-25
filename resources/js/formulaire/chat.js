import { documentFetch } from './api';

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

    const appendBubble = (className, text) => {
        const bubble = document.createElement('div');
        bubble.className = className;
        bubble.textContent = text;
        messages.appendChild(bubble);
        thread.scrollTop = thread.scrollHeight;
        return bubble;
    };

    const renderFragments = (fragments, emptyMessage = 'No similar fragments found.') => {
        const wrapper = document.createElement('div');
        wrapper.className = 'space-y-2';

        if (fragments.length === 0) {
            wrapper.appendChild(Object.assign(document.createElement('p'), {
                className: 'text-sm text-[#838E9C]',
                textContent: emptyMessage,
            }));
        }

        fragments.forEach((fragment) => {
            const card = document.createElement('div');
            card.className = 'rounded-2xl border border-[#262D38] bg-[#12161C] px-4 py-3';

            const header = document.createElement('div');
            header.className = 'flex items-start justify-between gap-3';

            const source = document.createElement('div');
            source.className = 'min-w-0';

            const documentLink = document.createElement('button');
            documentLink.type = 'button';
            documentLink.dataset.slug = fragment.document.slug;
            documentLink.className = 'chat-fragment-document block max-w-full truncate text-left text-xs font-medium text-[#3C82C4] hover:underline';
            documentLink.textContent = fragment.document.title;
            source.appendChild(documentLink);

            if (fragment.headers) {
                const headers = document.createElement('p');
                headers.className = 'truncate text-[11px] text-[#838E9C]';
                headers.textContent = fragment.headers;
                source.appendChild(headers);
            }

            const score = document.createElement('span');
            score.className = 'shrink-0 rounded-full bg-[#1A1F27] px-2 py-0.5 text-[10px] font-medium tabular-nums text-[#C3CAD3]';
            score.textContent = `${(fragment.similarity * 100).toFixed(1)}% · d ${fragment.distance.toFixed(3)}`;
            score.title = 'Cosine similarity · cosine distance';

            header.append(source, score);

            const content = document.createElement('p');
            content.className = 'mt-2 text-sm text-[#E4E7EB] whitespace-pre-wrap break-words';
            content.textContent = fragment.content;

            card.append(header, content);
            wrapper.appendChild(card);
        });

        messages.appendChild(wrapper);
        thread.scrollTop = thread.scrollHeight;
    };

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const query = input.value.trim();
        if (!query) return;

        empty.classList.add('hidden');
        messages.classList.remove('hidden');
        appendBubble('ml-auto w-fit max-w-[80%] rounded-2xl bg-[#1A1F27] px-4 py-2.5 text-sm text-[#E4E7EB] whitespace-pre-wrap break-words', query);

        input.value = '';
        resize();

        const pending = appendBubble('text-sm text-[#838E9C]', 'Searching…');

        try {
            const { data = [], message } = await documentFetch(form.action, {
                method: 'POST',
                body: JSON.stringify({ query }),
            });
            pending.remove();
            renderFragments(data, message);
        } catch (error) {
            pending.textContent = error.errors?.query?.[0] ?? 'Something went wrong while querying the corpus.';
            pending.className = 'text-sm text-red-400';
        }
    });
}
