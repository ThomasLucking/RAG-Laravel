import { documentFetch, renderFieldErrors, ValidationError } from './api';

function documentListItemHtml(doc) {
    return `
        <button type="button" data-slug="${doc.slug}"
            class="document-item w-full flex items-center justify-between gap-2 text-left px-3 py-2 rounded-md text-sm text-[#C3CAD3] hover:bg-[#12161C] hover:text-[#E4E7EB] transition-colors">
            <span class="truncate">${doc.title}</span>
            <span class="document-item-origin shrink-0 rounded-full px-2 py-0.5 text-[10px] font-medium bg-[#12241C] text-[#6BC79A]">
                Workshop
            </span>
        </button>
    `;
}

export function addDocumentToSidebar(doc) {
    const list = document.getElementById('document-list');
    if (!list) return;

    const empty = list.querySelector('p');
    if (empty) empty.remove();

    list.insertAdjacentHTML('beforeend', documentListItemHtml(doc));
}

export function initIngestModal() {
    const modal = document.getElementById('ingest-modal');
    if (!modal) return;

    const form = document.getElementById('ingest-form');
    const submit = document.getElementById('ingest-submit');
    const error = document.getElementById('ingest-error');

    const resetMessages = () => {
        error.classList.add('hidden');
        error.textContent = '';
        form.querySelectorAll('.modal-field-error').forEach((el) => (el.textContent = ''));
    };

    const open = () => {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };

    const close = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        resetMessages();
        form.reset();
    };

    document.getElementById('ingest-open').addEventListener('click', open);
    document.getElementById('ingest-close').addEventListener('click', close);
    modal.addEventListener('click', (event) => {
        if (event.target === modal) close();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') close();
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        resetMessages();
        submit.disabled = true;

        const payload = {
            title: form.title.value,
            summary: form.summary.value,
            tags: form.tags.value,
            updated: form.updated.value,
            content: form.content.value,
        };

        try {
            const { data: doc } = await documentFetch('/documents', {
                method: 'POST',
                body: JSON.stringify(payload),
            });

            addDocumentToSidebar(doc);
            close();
        } catch (e) {
            if (e instanceof ValidationError) {
                renderFieldErrors(form, e.errors);
                error.textContent = 'Please fix the errors above.';
                error.classList.remove('hidden');
            } else {
                error.textContent = 'Could not save document.';
                error.classList.remove('hidden');
            }
        } finally {
            submit.disabled = false;
        }
    });
}
