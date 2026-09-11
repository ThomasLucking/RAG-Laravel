export function initDocumentModal() {
    const modal = document.getElementById('document-modal');
    if (!modal) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const modalForm = document.getElementById('modal-form');
    const modalTitleDisplay = document.getElementById('modal-title-display');
    const modalClose = document.getElementById('modal-close');
    const modalEditToggle = document.getElementById('modal-edit-toggle');
    const modalSave = document.getElementById('modal-save');
    const modalError = document.getElementById('modal-error');
    const modalSuccess = document.getElementById('modal-success');
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
            const response = await fetch(`/documents/${slug}`, {
                headers: { Accept: 'application/json' },
            });
            if (!response.ok) throw new Error('Failed to load document.');
            const doc = await response.json();
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
            const response = await fetch(`/documents/${currentSlug}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(payload),
            });

            if (response.status === 422) {
                const body = await response.json();
                Object.entries(body.errors || {}).forEach(([field, messages]) => {
                    const target = modalForm.querySelector(`.modal-field-error[data-field="${field}"]`);
                    if (target) target.textContent = messages[0];
                });
                modalError.textContent = 'Please fix the errors above.';
                modalError.classList.remove('hidden');
                modalSave.disabled = false;
                return;
            }

            if (!response.ok) throw new Error('Failed to save document.');

            const doc = await response.json();
            modalTitleDisplay.textContent = doc.title;
            modalSuccess.classList.remove('hidden');
            setEditing(false);

            const listItem = document.querySelector(`.document-item[data-slug="${doc.slug}"]`);
            if (listItem) listItem.textContent = doc.title;
        } catch (e) {
            modalError.textContent = 'Could not save document.';
            modalError.classList.remove('hidden');
            modalSave.disabled = false;
        }
    });
}
