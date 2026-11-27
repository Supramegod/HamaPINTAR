/** Interaksi halaman: sidebar mobile, dialog, alert, dropzone, status loading form, konfirmasi. */
export function initUi() {
    initSidebar();
    initDialogs();
    initAlerts();
    initDropzones();
    initLoadingForms();
    initConfirmations();
}

/** Form dengan atribut data-confirm meminta konfirmasi sebelum dikirim (mis. hapus data). */
function initConfirmations() {
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm)) event.preventDefault();
        });
    });
}

function initSidebar() {
    const sidebar = document.querySelector('[data-sidebar]');
    const overlay = document.querySelector('[data-sidebar-overlay]');
    if (!sidebar) return;

    const setOpen = (open) => {
        sidebar.classList.toggle('-translate-x-full', !open);
        overlay?.classList.toggle('hidden', !open);
    };

    document.querySelector('[data-sidebar-open]')?.addEventListener('click', () => setOpen(true));
    document.querySelector('[data-sidebar-close]')?.addEventListener('click', () => setOpen(false));
    overlay?.addEventListener('click', () => setOpen(false));
    sidebar.querySelectorAll('[data-nav-link]').forEach((link) => link.addEventListener('click', () => setOpen(false)));
}

function initDialogs() {
    document.querySelectorAll('[data-open-dialog]').forEach((trigger) => {
        trigger.addEventListener('click', () => document.getElementById(trigger.dataset.openDialog)?.showModal());
    });

    document.querySelectorAll('dialog').forEach((dialog) => {
        dialog.querySelectorAll('[data-close-dialog]').forEach((btn) => btn.addEventListener('click', () => dialog.close()));
        // Klik pada backdrop menutup dialog
        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) dialog.close();
        });
        if (dialog.hasAttribute('data-auto-open')) dialog.showModal();
    });
}

function initAlerts() {
    document.querySelectorAll('[data-dismiss-alert]').forEach((button) => {
        button.addEventListener('click', () => button.closest('[data-alert]')?.remove());
    });
}

function initDropzones() {
    document.querySelectorAll('[data-dropzone]').forEach((zone) => {
        const input = zone.querySelector('[data-file-input]');
        const label = zone.querySelector('[data-file-name]');
        const defaultText = label?.textContent;
        const showName = () => {
            if (label) label.textContent = input.files[0]?.name ?? defaultText;
        };

        input?.addEventListener('change', showName);

        ['dragenter', 'dragover'].forEach((type) =>
            zone.addEventListener(type, (event) => {
                event.preventDefault();
                zone.dataset.dragging = '';
            }),
        );
        ['dragleave', 'drop'].forEach((type) =>
            zone.addEventListener(type, () => delete zone.dataset.dragging),
        );
        zone.addEventListener('drop', (event) => {
            event.preventDefault();
            if (input && event.dataTransfer?.files.length) {
                input.files = event.dataTransfer.files;
                showName();
            }
        });
    });
}

function initLoadingForms() {
    document.querySelectorAll('[data-loading-form]').forEach((form) => {
        form.addEventListener('submit', () => {
            const button = form.querySelector('[type="submit"]');
            if (!button) return;
            button.disabled = true;
            const label = button.querySelector('[data-label]');
            if (label) label.textContent = 'Memproses…';
        });
    });
}
