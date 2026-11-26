const uploadForm = document.querySelector('[data-upload-form]');
const templateInput = document.querySelector('[data-template-input]');
const dropzone = document.querySelector('[data-dropzone]');

const uploadContent = document.querySelector('[data-upload-content]');
const uploadLoading = document.querySelector('[data-upload-loading]');
const uploadLoadingName = document.querySelector('[data-upload-loading-name]');

const showUploadLoading = (file) => {
    if (!file || !uploadForm || !uploadContent || !uploadLoading) {
        return;
    }

    // Switch the upload interface to loading state.
    uploadContent.hidden = true;
    uploadLoading.hidden = false;

    // Display the translated upload status with the file name.
    if (uploadLoadingName) {
        uploadLoadingName.textContent = translations.uploadingFile.replace(
            ':name',
            file.name
        );
    }

    dropzone.classList.remove('is-dragging');
    dropzone.classList.add('is-uploading');

    uploadForm.setAttribute('aria-busy', 'true');
};

const translations = {
    uploadingFile: 'Uploading :name...',
    recipientName: 'Recipient name, row :number',
    note: 'Note, row :number',
    deleteRow: 'Delete row',
    deleteRowNumber: 'Delete row :number',
    showAll: 'Show All',
    showLess: 'Show Less',
    recipientEmail: 'Recipient email, row :number',
    ...window.procertTranslations,
};


if (uploadForm && templateInput && dropzone) {
    const filePicker = document.querySelector('[data-file-picker]');

    filePicker?.addEventListener('click', () => {
        templateInput.click();
    });

    templateInput.addEventListener('change', () => {
        const [file] = templateInput.files;

        if (file) {
            showUploadLoading(file);
            uploadForm.requestSubmit();
        }
    });

    uploadForm.addEventListener('submit', () => {
        uploadForm.setAttribute('aria-busy', 'true');
    });

    dropzone.addEventListener('dragover', (event) => {
        event.preventDefault();
        dropzone.classList.add('is-dragging');
    });

    dropzone.addEventListener('dragleave', (event) => {
        if (!dropzone.contains(event.relatedTarget)) {
            dropzone.classList.remove('is-dragging');
        }
    });

    dropzone.addEventListener('drop', (event) => {
        event.preventDefault();
        dropzone.classList.remove('is-dragging');

        const [file] = event.dataTransfer.files;

        if (file) {
            const transfer = new DataTransfer();
            transfer.items.add(file);
            templateInput.files = transfer.files;

            showUploadLoading(file);
            uploadForm.requestSubmit();
        }
    });
}

// =========================================================
// RECIPIENT UPLOAD TOAST
// =========================================================

const recipientToast = document.querySelector('[data-recipient-toast]');
const closeRecipientToast = document.querySelector(
    '[data-close-recipient-toast]'
);

if (recipientToast) {
    const dismissRecipientToast = () => {
        recipientToast.classList.add('is-hiding');

        window.setTimeout(() => {
            recipientToast.remove();
        }, 350);
    };

    const toastTimer = window.setTimeout(
        dismissRecipientToast,
        5000
    );

    closeRecipientToast?.addEventListener('click', () => {
        window.clearTimeout(toastTimer);
        dismissRecipientToast();
    });
}

const replaceInput = document.querySelector('[data-replace-input]');

replaceInput?.addEventListener('change', () => {
	if (replaceInput.files.length > 0) {
		replaceInput.form.requestSubmit();
	}
});

const manualForm = document.querySelector('[data-manual-form]');
// =========================================================
// RECIPIENT MODE SWITCH — SAVE SHARED DATA
// =========================================================

const recipientModeLinks = document.querySelectorAll(
    '[data-recipient-mode-link]'
);

const recipientTargetMode = document.querySelector(
    '[data-recipient-target-mode]'
);

recipientModeLinks.forEach((link) => {
    link.addEventListener('click', (event) => {
        const targetMode = link.dataset.recipientModeLink;

        // No save needed when the current mode is Excel.
        // Imported data already lives in the session.
        if (!manualForm || !manualRows || !recipientTargetMode) {
            return;
        }

        const currentMode = document.querySelector(
            '[data-recipient-panel="manual"]:not([hidden])'
        );

        // Only intercept when leaving the manual mode.
        if (!currentMode || targetMode !== 'import') {
            return;
        }

        event.preventDefault();
        /*
        * Mode switching is not a final submission.
        * Allow empty manual rows to be skipped while saving
        * the current draft to the shared recipient session.
        */
        manualForm.noValidate = true;

        recipientTargetMode.value = targetMode;

        manualForm.action = manualForm.dataset.draftAction;

        manualForm.requestSubmit();
    });
});

const manualRows = document.querySelector('[data-manual-rows]');
const addRecipientButton = document.querySelector('[data-add-recipient]');


const createRecipientRow = (index) => {
    const row = document.createElement('tr');
    row.dataset.recipientRow = '';

    // Row number
    const numberCell = document.createElement('td');
    numberCell.className = 'recipient-number';

    const number = document.createElement('span');
    number.dataset.rowNumber = '';
    numberCell.append(number);

    row.append(numberCell);

    // Recipient name
    const nameCell = document.createElement('td');
    const nameInput = document.createElement('input');

    nameInput.name = `recipients[${index}][name]`;
    nameInput.maxLength = 255;
    nameInput.required = true;
    nameInput.dataset.recipientName = '';
    nameInput.setAttribute(
        'aria-label',
        translations.recipientName.replace(':number', index + 1)
    );

    nameCell.append(nameInput);
    row.append(nameCell);

    // Optional email
    const emailCell = document.createElement('td');
    const emailInput = document.createElement('input');

    emailInput.type = 'email';
    emailInput.name = `recipients[${index}][email]`;
    emailInput.maxLength = 254;
    emailInput.dataset.recipientEmail = '';
    emailInput.setAttribute(
        'aria-label',
        translations.recipientEmail.replace(':number', index + 1)
    );

    emailCell.append(emailInput);
    row.append(emailCell);

    // Note and delete button
    const noteCell = document.createElement('td');
    const noteWrapper = document.createElement('div');

    noteWrapper.className = 'recipient-note-cell';

    const noteInput = document.createElement('input');

    noteInput.name = `recipients[${index}][note]`;
    noteInput.maxLength = 255;
    noteInput.dataset.recipientNote = '';
    noteInput.setAttribute(
        'aria-label',
        translations.note.replace(':number', index + 1)
    );

    const deleteButton = document.createElement('button');

    deleteButton.type = 'button';
    deleteButton.className = 'delete-row-button';
    deleteButton.dataset.deleteRow = '';
    deleteButton.setAttribute(
        'aria-label',
        translations.deleteRowNumber.replace(':number', index + 1)
    );
    deleteButton.title = translations.deleteRow;

    deleteButton.innerHTML = `
        <svg viewBox="0 0 20 20" fill="none"
            stroke="currentColor" stroke-width="1.8"
            stroke-linecap="round" stroke-linejoin="round"
            aria-hidden="true">
            <path d="M3 5h14M7 5V3h6v2m-8 0 1 12h8l1-12M8 8v6m4-6v6"/>
        </svg>
    `;

    noteWrapper.append(noteInput, deleteButton);
    noteCell.append(noteWrapper);
    row.append(noteCell);

    return row;
};


const renumberRecipientRows = () => {
    const rows = manualRows?.querySelectorAll('[data-recipient-row]');

    rows?.forEach((row, index) => {
        const number = index + 1;

        const rowNumber = row.querySelector('[data-row-number]');
        const nameInput = row.querySelector('[data-recipient-name]');
        const emailInput = row.querySelector('[data-recipient-email]');
        const noteInput = row.querySelector('[data-recipient-note]');
        const deleteButton = row.querySelector('[data-delete-row]');

        if (!rowNumber || !nameInput || !emailInput || !noteInput) {
            return;
        }

        rowNumber.textContent = number;

        nameInput.name = `recipients[${index}][name]`;
        nameInput.setAttribute(
            'aria-label',
            translations.recipientName.replace(':number', number)
        );

        emailInput.name = `recipients[${index}][email]`;
        emailInput.setAttribute(
            'aria-label',
            translations.recipientEmail.replace(':number', number)
        );

        noteInput.name = `recipients[${index}][note]`;
        noteInput.setAttribute(
            'aria-label',
            translations.note.replace(':number', number)
        );

        deleteButton?.setAttribute(
            'aria-label',
            translations.deleteRowNumber.replace(':number', number)
        );
    });
};

// Tambah satu baris kosong
addRecipientButton?.addEventListener('click', () => {
    const rowCount = manualRows.querySelectorAll('[data-recipient-row]').length;

    if (rowCount >= 1000) {
        return;
    }

    const newRow = createRecipientRow(rowCount);

    manualRows.append(newRow);
    renumberRecipientRows();

    newRow.querySelector('[data-recipient-name]').focus();
});


// Hapus baris berdasarkan ikon sampah yang ditekan
manualRows?.addEventListener('click', (event) => {
    const deleteButton = event.target.closest('[data-delete-row]');

    if (!deleteButton) {
        return;
    }

    const row = deleteButton.closest('[data-recipient-row]');

    if (!row) {
        return;
    }

    row.remove();

    // Pastikan selalu tersedia minimal satu baris kosong
    if (manualRows.querySelectorAll('[data-recipient-row]').length === 0) {
        manualRows.append(createRecipientRow(0));
    }

    renumberRecipientRows();
});


// =========================================================
// STEP 2 — RECIPIENT EXCEL / CSV UPLOAD
// =========================================================

const importForm = document.querySelector('[data-recipient-import-form]');
const recipientFileInput = document.querySelector('[data-recipient-file]');
const recipientDropzone = document.querySelector('[data-recipient-dropzone]');
const recipientPicker = document.querySelector('[data-recipient-picker]');

const recipientUploadContent = document.querySelector(
    '[data-recipient-upload-content]'
);

const recipientUploadLoading = document.querySelector(
    '[data-recipient-upload-loading]'
);

const recipientUploadName = document.querySelector(
    '[data-recipient-uploading-name]'
);

let recipientUploadStarted = false;

const showRecipientUploadLoading = (file) => {
    if (
        !file ||
        !importForm ||
        !recipientUploadContent ||
        !recipientUploadLoading
    ) {
        return;
    }

    // Mencegah satu file terkirim berkali-kali.
    if (recipientUploadStarted) {
        return;
    }

    recipientUploadStarted = true;

    // Sembunyikan tampilan awal upload.
    recipientUploadContent.hidden = true;

    // Tampilkan animasi loading.
    recipientUploadLoading.hidden = false;

    // Tampilkan nama file yang sedang diproses.
    if (recipientUploadName) {
        recipientUploadName.textContent =
            translations.uploadingFile.replace(':name', file.name);
    }

    // Ubah tampilan dropzone.
    recipientDropzone?.classList.remove('is-dragging');
    recipientDropzone?.classList.add('is-uploading');

    // Tandai form sedang mengirim data.
    importForm.setAttribute('aria-busy', 'true');

    // Kirim file ke server.
    importForm.requestSubmit();
};


// Tombol Pilih File.
recipientPicker?.addEventListener('click', () => {
    if (!recipientFileInput) {
        return;
    }

    recipientFileInput.click();
});


// Upload melalui file picker.
recipientFileInput?.addEventListener('change', () => {
    const file = recipientFileInput.files?.[0];

    if (file) {
        showRecipientUploadLoading(file);
    }
});


// Drag and drop: ketika file berada di atas dropzone.
recipientDropzone?.addEventListener('dragover', (event) => {
    event.preventDefault();

    recipientDropzone.classList.add('is-dragging');
});


// Drag and drop: ketika file meninggalkan dropzone.
recipientDropzone?.addEventListener('dragleave', (event) => {
    if (!recipientDropzone.contains(event.relatedTarget)) {
        recipientDropzone.classList.remove('is-dragging');
    }
});


// Drag and drop: ketika file dilepaskan.
recipientDropzone?.addEventListener('drop', (event) => {
    event.preventDefault();

    recipientDropzone.classList.remove('is-dragging');

    const file = event.dataTransfer?.files?.[0];

    if (!file || !recipientFileInput) {
        return;
    }

    // Masukkan file hasil drag-and-drop ke input file.
    const transfer = new DataTransfer();

    transfer.items.add(file);

    recipientFileInput.files = transfer.files;

    showRecipientUploadLoading(file);
});

const previewToggle = document.querySelector('[data-preview-toggle]');
let previewExpanded = false;

previewToggle?.addEventListener('click', () => {
	previewExpanded = !previewExpanded;
	document.querySelectorAll('[data-preview-row]').forEach((row, index) => {
		row.hidden = !previewExpanded && index >= 10;
	});
	previewToggle.setAttribute('aria-expanded', String(previewExpanded));
	previewToggle.querySelector('[data-preview-label]').textContent = previewExpanded
		? translations.showLess
		: translations.showAll;
});

const themeToggle = document.querySelector('[data-theme-toggle]');
const themePreferenceKey = 'procert-theme';
const systemTheme = window.matchMedia('(prefers-color-scheme: dark)');



const syncThemeToggle = () => {
    if (!themeToggle) {
        return;
    }

    const isDark = document.documentElement.dataset.theme === 'dark';

    const actionLabel = isDark
        ? themeToggle.dataset.labelLight
        : themeToggle.dataset.labelDark;

    themeToggle.setAttribute('aria-pressed', String(isDark));
    themeToggle.setAttribute('aria-label', actionLabel);
    themeToggle.setAttribute('title', actionLabel);

    const moonIcon = themeToggle.querySelector('[data-theme-icon="moon"]');
    const sunIcon = themeToggle.querySelector('[data-theme-icon="sun"]');

    // Moon hanya tampil pada Dark Mode
    if (moonIcon) {
        if (isDark) {
            moonIcon.removeAttribute('hidden');
        } else {
            moonIcon.setAttribute('hidden', '');
        }
    }

    // Sun hanya tampil pada Light Mode
    if (sunIcon) {
        if (isDark) {
            sunIcon.setAttribute('hidden', '');
        } else {
            sunIcon.removeAttribute('hidden');
        }
    }
};



themeToggle?.addEventListener('click', () => {
	const nextTheme = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';

	document.documentElement.dataset.theme = nextTheme;
	localStorage.setItem(themePreferenceKey, nextTheme);
	syncThemeToggle();
});

systemTheme.addEventListener('change', (event) => {
	if (localStorage.getItem(themePreferenceKey)) {
		return;
	}

	document.documentElement.dataset.theme = event.matches ? 'dark' : 'light';
	syncThemeToggle();
});

syncThemeToggle();


 // =========================================================
 // STEP 1 — CERTIFICATE WORKSPACE
 // =========================================================

const canvasViewport = document.querySelector('[data-canvas-viewport]');
const canvasStage = document.querySelector('[data-canvas-stage]');
const canvasImage = document.querySelector('[data-canvas-image]');
const canvasPdf = document.querySelector('[data-canvas-pdf]');
const zoomValue = document.querySelector('[data-zoom-value]');
const dimensionsLabel = document.querySelector('[data-canvas-dimensions]');

if (canvasViewport && canvasStage && (canvasImage || canvasPdf)) {
    const MIN_ZOOM = 0.1;
    const MAX_ZOOM = 3;
    const ZOOM_STEP = 0.1;

    let zoom = 1;
    let originalWidth = 0;
    let originalHeight = 0;

    const getCanvasContent = () => canvasImage || canvasPdf;

    // Read the original dimensions before applying any zoom.
    const measureCanvas = () => {
        if (canvasImage) {
            originalWidth = canvasImage.naturalWidth;
            originalHeight = canvasImage.naturalHeight;
        } else if (canvasPdf) {
            originalWidth = 1100;
            originalHeight = 780;
        }

        return originalWidth > 0 && originalHeight > 0;
    };

    // Keep the content centered inside a stage that matches its
    // scaled visual dimensions. This prevents layout/transform mismatch.
    const updateZoom = (keepCenter = true) => {
        const content = getCanvasContent();

        if (!content || !originalWidth || !originalHeight) {
            return;
        }

        const previousCenterX =
            canvasViewport.scrollLeft + canvasViewport.clientWidth / 2;

        const previousCenterY =
            canvasViewport.scrollTop + canvasViewport.clientHeight / 2;

        const scaledWidth = originalWidth * zoom;
        const scaledHeight = originalHeight * zoom;

        canvasStage.style.width = `${scaledWidth}px`;
        canvasStage.style.height = `${scaledHeight}px`;

        content.style.position = 'absolute';
        content.style.left = '50%';
        content.style.top = '50%';
        content.style.width = `${originalWidth}px`;
        content.style.height = `${originalHeight}px`;
        content.style.maxWidth = 'none';
        content.style.maxHeight = 'none';
        content.style.transform =
            `translate(-50%, -50%) scale(${zoom})`;
        content.style.transformOrigin = 'center center';

        if (zoomValue) {
            zoomValue.textContent = `${Math.round(zoom * 100)}%`;
        }

        if (keepCenter) {
            requestAnimationFrame(() => {
                const maxScrollLeft =
                    canvasViewport.scrollWidth - canvasViewport.clientWidth;

                const maxScrollTop =
                    canvasViewport.scrollHeight - canvasViewport.clientHeight;

                canvasViewport.scrollLeft = Math.max(
                    0,
                    Math.min(
                        maxScrollLeft,
                        previousCenterX - canvasViewport.clientWidth / 2
                    )
                );

                canvasViewport.scrollTop = Math.max(
                    0,
                    Math.min(
                        maxScrollTop,
                        previousCenterY - canvasViewport.clientHeight / 2
                    )
                );
            });
        }
    };

    // Calculate a zoom level that displays the complete file.
    const fitCanvas = () => {
        if (!measureCanvas()) {
            return;
        }

        const availableWidth = Math.max(
            100,
            canvasViewport.clientWidth - 60
        );

        const availableHeight = Math.max(
            100,
            canvasViewport.clientHeight - 60
        );

        zoom = Math.min(
            availableWidth / originalWidth,
            availableHeight / originalHeight,
            1
        );

        updateZoom(false);

        requestAnimationFrame(() => {
            canvasViewport.scrollLeft = 0;
            canvasViewport.scrollTop = 0;
        });
    };

    // Zoom in while preserving the current viewport center.
    document.querySelector('[data-zoom-in]')?.addEventListener('click', () => {
        zoom = Math.min(MAX_ZOOM, zoom + ZOOM_STEP);
        updateZoom(true);
    });

    // Zoom out while preserving the current viewport center.
    document.querySelector('[data-zoom-out]')?.addEventListener('click', () => {
        zoom = Math.max(MIN_ZOOM, zoom - ZOOM_STEP);
        updateZoom(true);
    });

    // Fit the complete file inside the available workspace.
    document.querySelector('[data-zoom-fit]')?.addEventListener('click', fitCanvas);

    // Initialize image workspace after the original image is ready.
    if (canvasImage) {
        const initializeImage = () => {
            if (!measureCanvas()) {
                return;
            }

            if (dimensionsLabel) {
                dimensionsLabel.textContent =
                    `${originalWidth} × ${originalHeight} px`;
            }

            fitCanvas();
        };

        if (canvasImage.complete && canvasImage.naturalWidth > 0) {
            initializeImage();
        } else {
            canvasImage.addEventListener('load', initializeImage, {
                once: true
            });
        }
    }

    // Initialize PDF workspace.
    if (canvasPdf) {
        const initializePdf = () => {
            if (!measureCanvas()) {
                return;
            }

            if (dimensionsLabel) {
                dimensionsLabel.textContent = 'PDF document';
            }

            fitCanvas();
        };

        initializePdf();
    }

    // Recalculate Fit only when the viewport size changes.
    let resizeTimer;

    window.addEventListener('resize', () => {
        window.clearTimeout(resizeTimer);

        resizeTimer = window.setTimeout(() => {
            fitCanvas();
        }, 150);
    });
}


// =========================================================
// STEP 1 — PROCERT CHAT NOTIFICATION
// =========================================================

const procertNotification = document.querySelector(
    '[data-procert-notification]'
);

if (procertNotification) {
    const closeButton = procertNotification.querySelector(
        '[data-notification-close]'
    );

    let notificationTimer;

    const closeNotification = () => {
        if (!procertNotification.isConnected) return;

        window.clearTimeout(notificationTimer);

        procertNotification.classList.add('is-hiding');

        window.setTimeout(() => {
            procertNotification.remove();
        }, 260);
    };

    closeButton?.addEventListener('click', closeNotification);

    notificationTimer = window.setTimeout(
        closeNotification,
        5000
    );
}
