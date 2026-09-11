import { initWordCount } from './formulaire/word-count';
import { initDocumentModal } from './formulaire/document-modal';
import { initIngestModal } from './formulaire/ingest-modal';
import { initChat } from './formulaire/chat';

document.addEventListener('DOMContentLoaded', () => {
    initWordCount();
    initDocumentModal();
    initIngestModal();
    initChat();
});

