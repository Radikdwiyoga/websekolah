import jsQR from 'jsqr';

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

// --- Print QR card ----------------------------------------------------------
// Buttons marked [data-qr-print] open the browser print dialog. The QR card
// partial carries the matching @media print rules.
document.querySelectorAll('[data-qr-print]').forEach((button) => {
    button.addEventListener('click', () => window.print());
});

// --- QR scanner -------------------------------------------------------------
// Containers marked [data-qr-scanner] can read a student's QR code either from
// the camera ([data-qr-start]) or from an uploaded picture ([data-qr-file])
// using the jsQR decoder. The text field stays usable with hardware scanners,
// so the form keeps working even when the camera cannot be used.
document.querySelectorAll('[data-qr-scanner]').forEach((root) => {
    const input = root.querySelector('[data-qr-input]');
    const startButton = root.querySelector('[data-qr-start]');
    const wrapper = root.querySelector('[data-qr-video-wrapper]');
    const video = root.querySelector('[data-qr-video]');
    const status = root.querySelector('[data-qr-status]');
    const fileInput = root.querySelector('[data-qr-file]');
    const form = root.tagName === 'FORM' ? root : root.closest('form');

    if (!input) {
        return;
    }

    // Kept in sessionStorage so the camera resumes automatically after the
    // auto-submit page reload, without pressing "Aktifkan Kamera" again.
    const ACTIVE_KEY = 'qr-scanner-active';
    const LAST_SCAN_KEY = 'qr-scanner-last-scan';
    const DUPLICATE_WINDOW = 15000;

    const readStorage = (key) => {
        try {
            return window.sessionStorage.getItem(key);
        } catch (error) {
            return null;
        }
    };

    const writeStorage = (key, value) => {
        try {
            if (value === null) {
                window.sessionStorage.removeItem(key);
            } else {
                window.sessionStorage.setItem(key, value);
            }
        } catch (error) {
            // Storage may be unavailable (private mode); scanning still works.
        }
    };

    const isDuplicateScan = (data) => {
        const raw = readStorage(LAST_SCAN_KEY);

        if (!raw) {
            return false;
        }

        try {
            const previous = JSON.parse(raw);

            return previous.value === data && (Date.now() - previous.at) < DUPLICATE_WINDOW;
        } catch (error) {
            return false;
        }
    };

    const canvas = document.createElement('canvas');
    const context = canvas.getContext('2d', { willReadFrequently: true });

    let stream = null;
    let frame = null;
    let submitting = false;

    const report = (message) => {
        if (status) {
            status.textContent = message;
        }
    };

    const submitForm = () => {
        if (!form || submitting) {
            return;
        }

        submitting = true;
        writeStorage(LAST_SCAN_KEY, JSON.stringify({ value: input.value, at: Date.now() }));
        report('QR code terbaca. Menyimpan kehadiran...');

        window.setTimeout(() => {
            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
            } else {
                form.submit();
            }
        }, 350);
    };

    const handleDecoded = (data) => {
        if (!data) {
            return false;
        }

        input.value = data;
        input.dispatchEvent(new Event('change', { bubbles: true }));

        if (isDuplicateScan(data)) {
            report('QR ini baru saja tercatat. Arahkan QR code siswa berikutnya...');

            return false;
        }

        submitForm();

        return true;
    };

    // --- Camera ---
    const stopCamera = ({ forget = false } = {}) => {
        if (frame) {
            window.cancelAnimationFrame(frame);
            frame = null;
        }

        if (stream) {
            stream.getTracks().forEach((track) => track.stop());
            stream = null;
        }

        if (forget) {
            writeStorage(ACTIVE_KEY, null);
        }

        if (wrapper) {
            wrapper.classList.add('hidden');
        }

        if (startButton) {
            startButton.textContent = 'Aktifkan Kamera';
        }
    };

    const scanFrame = () => {
        if (!stream || submitting) {
            return;
        }

        if (video.readyState === video.HAVE_ENOUGH_DATA) {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
            const result = jsQR(imageData.data, imageData.width, imageData.height, {
                inversionAttempts: 'dontInvert',
            });

            if (result && handleDecoded(result.data)) {
                // Keep the live preview; the page reloads once the form submits.
                return;
            }
        }

        frame = window.requestAnimationFrame(scanFrame);
    };

    const startCamera = async () => {
        if (!navigator.mediaDevices?.getUserMedia) {
            report('Browser ini tidak mendukung kamera. Gunakan "Unggah Gambar QR" atau alat pemindai.');

            return;
        }

        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
            video.srcObject = stream;
            await video.play();

            writeStorage(ACTIVE_KEY, '1');

            if (wrapper) {
                wrapper.classList.remove('hidden');
            }

            if (startButton) {
                startButton.textContent = 'Matikan Kamera';
            }

            report('Arahkan QR code siswa ke dalam kotak. Kehadiran tersimpan otomatis saat terbaca.');
            frame = window.requestAnimationFrame(scanFrame);
        } catch (error) {
            stopCamera();
            report('Kamera tidak dapat diakses. Aktifkan izin kamera atau jalankan lewat localhost/HTTPS, lalu gunakan "Unggah Gambar QR".');
        }
    };

    startButton?.addEventListener('click', () => {
        if (stream) {
            stopCamera({ forget: true });
        } else {
            startCamera();
        }
    });

    // Hardware scanners usually end their input with an Enter key press.
    input.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && ! event.shiftKey) {
            event.preventDefault();
            handleDecoded(input.value.trim());
        }
    });

    // --- Uploaded image ---
    fileInput?.addEventListener('change', () => {
        const file = fileInput.files && fileInput.files[0];

        if (!file) {
            return;
        }

        const image = new Image();

        image.onload = () => {
            canvas.width = image.naturalWidth;
            canvas.height = image.naturalHeight;
            context.drawImage(image, 0, 0);

            const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
            const result = jsQR(imageData.data, imageData.width, imageData.height);

            if (! result) {
                report('QR code tidak terbaca dari gambar. Coba gunakan gambar yang lebih jelas.');
            } else {
                handleDecoded(result.data);
            }

            URL.revokeObjectURL(image.src);
        };

        image.onerror = () => report('Gambar tidak dapat dimuat.');
        image.src = URL.createObjectURL(file);
    });

    // Resume scanning automatically after the auto-submit page reload (or when
    // the teacher opens this page again in the same tab).
    if (readStorage(ACTIVE_KEY) === '1') {
        startCamera();
    }
});

