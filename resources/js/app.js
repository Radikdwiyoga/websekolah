// Minimal progressive-enhancement script for shared UI components.
// No framework — native DOM APIs only.

// --- Native <dialog> modals ---------------------------------------------
document.addEventListener('click', (event) => {
    const opener = event.target.closest('[data-modal-open]');
    if (opener) {
        const dialog = document.getElementById(opener.dataset.modalOpen);
        if (dialog && typeof dialog.showModal === 'function') {
            dialog.showModal();
            document.body.classList.add('overflow-hidden');
        }
        return;
    }

    const closer = event.target.closest('[data-modal-close]');
    if (closer) {
        const dialog = closer.closest('dialog');
        if (dialog && typeof dialog.close === 'function') {
            dialog.close();
            document.body.classList.remove('overflow-hidden');
        }
    }
});

// Close modal when its backdrop is clicked.
document.addEventListener('click', (event) => {
    const dialog = event.target.closest('dialog[open]');
    if (dialog && event.target === dialog) {
        dialog.close();
        document.body.classList.remove('overflow-hidden');
    }
});

// --- Toasts ---------------------------------------------------------------
document.querySelectorAll('[data-toast]').forEach((toast) => {
    const closeButton = toast.querySelector('[data-toast-close]');
    const dismiss = () => toast.remove();

    closeButton?.addEventListener('click', dismiss);
    window.setTimeout(dismiss, 5000);
});

// --- Repeatable field rows --------------------------------------------------
// A container marked [data-repeater] clones rows from its <template
// data-repeater-template> child. Every __INDEX__ placeholder inside the clone
// is replaced with an incrementing counter so nested field names stay unique.
// Buttons marked [data-repeater-add] append a row; [data-repeater-remove]
// removes its own row (keeping at least one). Server-rendered rows are
// supported out of the box by seeding data-repeater-next.
document.querySelectorAll('[data-repeater]').forEach((repeater) => {
    const template = repeater.querySelector('[data-repeater-template]');
    const container = repeater.querySelector('[data-repeater-rows]');
    if (!template || !container) {
        return;
    }

    let index = Number(repeater.dataset.repeaterNext || 0);

    const bindRemove = (row) => {
        row.querySelectorAll('[data-repeater-remove]').forEach((button) => {
            button.addEventListener('click', (event) => {
                event.preventDefault();
                if (container.querySelectorAll('[data-repeater-row]').length <= 1) {
                    return;
                }
                row.remove();
            });
        });
    };

    const addRow = () => {
        const html = template.innerHTML.replaceAll('__INDEX__', String(index++));
        const holder = document.createElement('template');
        holder.innerHTML = html.trim();
        const row = holder.content.firstElementChild;
        if (!row) {
            return;
        }
        container.appendChild(row);
        bindRemove(row);
    };

    repeater.querySelectorAll('[data-repeater-add]').forEach((button) => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            addRow();
        });
    });

    container.querySelectorAll('[data-repeater-row]').forEach(bindRemove);

    repeater.dataset.repeaterNext = String(index);
});

// --- Answer progress --------------------------------------------------------
// Forms marked [data-progress] show live completion feedback for the given
// number of questions. The element [data-progress-bar] fills as answers change.
document.querySelectorAll('form[data-answer-progress]').forEach((form) => {
    const total = Number(form.dataset.answerTotal || 0);
    const label = form.querySelector('[data-progress-label]');
    const bar = form.querySelector('[data-progress-bar]');
    if (total <= 0 || !label || !bar) {
        return;
    }

    const update = () => {
        let answered = 0;
        form.querySelectorAll('[data-question]').forEach((question) => {
            const field = question.querySelector('input[type="radio"]:checked, textarea, input[type="text"]');
            if (field && String(field.value).trim() !== '') {
                answered++;
            }
        });
        label.textContent = answered + ' dari ' + total + ' soal terjawab';
        bar.style.width = Math.round((answered / total) * 100) + '%';
    };

    form.addEventListener('input', update);
    form.addEventListener('change', update);
    update();
});

// --- Submit loading state ---------------------------------------------------
// Forms opt in with data-submit-loading. While a request is in flight the
// submit buttons are disabled and show a spinner plus the text from their
// data-loading-text attribute (falls back to keeping the original label).
document.querySelectorAll('form[data-submit-loading]').forEach((form) => {
    form.addEventListener('submit', () => {
        form.querySelectorAll('button[type="submit"]').forEach((button) => {
            if (button.disabled) {
                return;
            }

            button.disabled = true;
            button.setAttribute('aria-busy', 'true');

            const loadingText = button.dataset.loadingText;
            if (loadingText) {
                button.innerHTML = '' +
                    '<svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">' +
                    '<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle>' +
                    '<path d="M4 12a8 8 0 0 1 8-8" stroke="currentColor" stroke-width="4" stroke-linecap="round"></path>' +
                    '</svg>' + loadingText;
            }
        });
    });
});