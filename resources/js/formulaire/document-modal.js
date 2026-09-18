import { documentFetch, renderFieldErrors, ValidationError } from './api';

export function initDocumentModal() {
    const modal = document.getElementById('document-modal');
    if (!modal) return;

    const modalForm = document.getElementById('modal-form');
    const modalTitleDisplay = document.getElementById('modal-title-display');
    const modalClose = document.getElementById('modal-close');
    const modalEditToggle = document.getElementById('modal-edit-toggle');
    const modalSave = document.getElementById('modal-save');
    const modalError = document.getElementById('modal-error');
    const modalSuccess = document.getElementById('modal-success');
    const modalOriginDisplay = document.getElementById('modal-origin-display');
    const modalDelete = document.getElementById('modal-delete');
    const modalFields = modalForm.querySelectorAll('.modal-field');
    let currentSlug = null;

    function resetMessages() {
        modalError.classList.add('hidden');
        modalError.textContent = '';
        modalSuccess.classList.add('hidden');
        modalForm.querySelectorAll('.modal-field-error').forEach((el) => (el.textContent = ''));
    }

    function setEditing(editing) {
        modalFields.forEach((field) => (field.disabled = !editing));
        modalSave.classList.toggle('hidden', !editing);
        modalSave.disabled = !editing;
        modalEditToggle.textContent = editing ? 'Cancel' : 'Edit';
    }

    function openModal(doc) {
        currentSlug = doc.slug;
        resetMessages();
        setEditing(false);
        modalTitleDisplay.textContent = doc.title;
        modalOriginDisplay.textContent = doc.origin === 'manual' ? 'Added by the workshop' : 'Imported into the corpus';
        modalForm.title.value = doc.title;
        modalForm.summary.value = doc.summary;
        modalForm.tags.value = Array.isArray(doc.tags) ? doc.tags.join(', ') : (doc.tags || '');
        modalForm.updated.value = doc.updated || '';
        modalForm.content.value = doc.content;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        currentSlug = null;
    }

    document.getElementById('document-list').addEventListener('click', async (event) => {
        const button = event.target.closest('.document-item');
        if (!button) return;
        const slug = button.dataset.slug;
        try {
            const { data: doc } = await documentFetch(`/documents/${slug}`);
            openModal(doc);
        } catch (e) {
            alert('Could not load document.');
        }
    });

    modalClose.addEventListener('click', closeModal);
    modal.addEventListener('click', (event) => {
        if (event.target === modal) closeModal();
    });

    modalEditToggle.addEventListener('click', () => {
        const nowEditing = modalSave.classList.contains('hidden');
        setEditing(nowEditing);
    });

    modalForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!currentSlug) return;
        resetMessages();
        modalSave.disabled = true;

        const payload = {
            title: modalForm.title.value,
            summary: modalForm.summary.value,
            tags: modalForm.tags.value,
            updated: modalForm.updated.value,
            content: modalForm.content.value,
        };

        try {
            const { data: doc } = await documentFetch(`/documents/${currentSlug}`, {
                method: 'PUT',
                body: JSON.stringify(payload),
            });

            modalTitleDisplay.textContent = doc.title;
                modalSuccess.classList.remove('hidden');
            setEditing(false);

            const listItem = document.querySelector(`.document-item[data-slug="${doc.slug}"] span:first-child`);
            if (listItem) listItem.textContent = doc.title;
        } catch (e) {
            if (e instanceof ValidationError) {
                renderFieldErrors(modalForm, e.errors);
                modalError.textContent = 'Please fix the errors above.';
            } else {
                modalError.textContent = 'Could not save document.';
            }
            modalError.classList.remove('hidden');
        } finally {
            modalSave.disabled = false;
        }
    });

    modalDelete.addEventListener('click', async () => {
        if (!currentSlug) return;
        if (!confirm(`Delete "${modalTitleDisplay.textContent}"? This cannot be undone.`)) return;

        resetMessages();
        modalDelete.disabled = true;

        try {
            await documentFetch(`/documents/${currentSlug}`, { method: 'DELETE' });

            const listItem = document.querySelector(`.document-item[data-slug="${currentSlug}"]`);
            if (listItem) listItem.remove();

            const list = document.getElementById('document-list');
            if (list && !list.querySelector('.document-item')) {
                list.innerHTML = '<p class="px-3 py-2 text-xs text-[#4A5261]">No documents yet.</p>';
            }

            closeModal();
        } catch (e) {
            modalError.textContent = 'Could not delete document.';
            modalError.classList.remove('hidden');
        } finally {
            modalDelete.disabled = false;
        }
    });
}
