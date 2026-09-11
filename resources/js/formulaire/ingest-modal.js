export function initIngestModal() {
    const modal = document.getElementById('ingest-modal');
    if (!modal) return;

    const open = () => {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };

    const close = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    };

    document.getElementById('ingest-open').addEventListener('click', open);
    document.getElementById('ingest-close').addEventListener('click', close);
    modal.addEventListener('click', (event) => {
        if (event.target === modal) close();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') close();
    });
}
