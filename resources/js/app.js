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

const replaceInput = document.querySelector('[data-replace-input]');

replaceInput?.addEventListener('change', () => {
	if (replaceInput.files.length > 0) {
		replaceInput.form.requestSubmit();
	}
});

const manualForm = document.querySelector('[data-manual-form]');
const manualRows = document.querySelector('[data-manual-rows]');
const addRecipientButton = document.querySelector('[data-add-recipient]');

const createRecipientRow = (index) => {
    const row = document.createElement('tr');
    row.dataset.recipientRow = '';

    // Nomor baris
    const numberCell = document.createElement('td');
    numberCell.className = 'recipient-number';

    const number = document.createElement('span');
    number.dataset.rowNumber = '';
    numberCell.append(number);

    row.append(numberCell);

    // Nama penerima
    const nameCell = document.createElement('td');
    const nameInput = document.createElement('input');

    nameInput.name = `recipients[${index}][name]`;
    nameInput.maxLength = 255;
    nameInput.dataset.recipientName = '';
    nameInput.setAttribute('aria-label', translations.recipientName.replace(':number', index + 1));

    nameCell.append(nameInput);
    row.append(nameCell);

    // Keterangan dan tombol hapus
    const noteCell = document.createElement('td');
    const noteWrapper = document.createElement('div');
    noteWrapper.className = 'recipient-note-cell';

    const noteInput = document.createElement('input');
    noteInput.name = `recipients[${index}][note]`;
    noteInput.maxLength = 255;
    noteInput.dataset.recipientNote = '';
    noteInput.setAttribute('aria-label', translations.note.replace(':number', index + 1));

    const deleteButton = document.createElement('button');
    deleteButton.type = 'button';
    deleteButton.className = 'delete-row-button';
    deleteButton.dataset.deleteRow = '';
    deleteButton.setAttribute('aria-label', translations.deleteRowNumber.replace(':number', index + 1));
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


// Mengatur ulang nomor dan name input
const renumberRecipientRows = () => {
    const rows = manualRows?.querySelectorAll('[data-recipient-row]');
    rows?.forEach((row, index) => {
        const number = index + 1;
        const rowNumber = row.querySelector('[data-row-number]');
        const nameInput = row.querySelector('[data-recipient-name]');
        const noteInput = row.querySelector('[data-recipient-note]');
        const deleteButton = row.querySelector('[data-delete-row]');

        if (!rowNumber || !nameInput || !noteInput) {
            return;
        }

        rowNumber.textContent = number;
        nameInput.name = `recipients[${index}][name]`;
        nameInput.setAttribute('aria-label', translations.recipientName.replace(':number', number));

        noteInput.name = `recipients[${index}][note]`;
        noteInput.setAttribute('aria-label', translations.note.replace(':number', number));

        deleteButton?.setAttribute('aria-label', translations.deleteRowNumber.replace(':number', number));
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

const importForm = document.querySelector('[data-recipient-import-form]');
const recipientFileInput = document.querySelector('[data-recipient-file]');
const recipientDropzone = document.querySelector('[data-recipient-dropzone]');
const recipientPicker = document.querySelector('[data-recipient-picker]');
const uploadStatus = document.querySelector('[data-recipient-uploading]');

const submitRecipientFile = (file) => {
    if (!file || !importForm) {
        return;
    }

    if (uploadStatus) {
        uploadStatus.hidden = false;

        uploadStatus.textContent = translations.uploadingFile.replace(
            ':name',
            file.name
        );
    }

    importForm.requestSubmit();
};

recipientPicker?.addEventListener('click', () => recipientFileInput.click());
recipientFileInput?.addEventListener('change', () => submitRecipientFile(recipientFileInput.files[0]));

recipientDropzone?.addEventListener('dragover', (event) => {
	event.preventDefault();
	recipientDropzone.classList.add('is-dragging');
});

recipientDropzone?.addEventListener('dragleave', (event) => {
	if (!recipientDropzone.contains(event.relatedTarget)) {
		recipientDropzone.classList.remove('is-dragging');
	}
});

recipientDropzone?.addEventListener('drop', (event) => {
	event.preventDefault();
	recipientDropzone.classList.remove('is-dragging');
	const [file] = event.dataTransfer.files;

	if (file) {
		const transfer = new DataTransfer();
		transfer.items.add(file);
		recipientFileInput.files = transfer.files;
		submitRecipientFile(file);
	}
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
