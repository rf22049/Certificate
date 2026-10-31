const uploadForm = document.querySelector('[data-upload-form]');
const templateInput = document.querySelector('[data-template-input]');
const dropzone = document.querySelector('[data-dropzone]');
const selectedFile = document.querySelector('[data-selected-file]');

const formatFileSize = (size) => {
	if (size < 1024 * 1024) {
		return `${Math.max(1, Math.round(size / 1024))} KB`;
	}

	return `${(size / 1024 / 1024).toFixed(1)} MB`;
};

const displaySelectedFile = (file) => {
	if (!file || !selectedFile || !uploadForm) {
		return;
	}

	selectedFile.hidden = false;
	selectedFile.querySelector('[data-file-name]').textContent = file.name;
	selectedFile.querySelector('[data-file-size]').textContent = formatFileSize(file.size);
	selectedFile.querySelector('[data-file-status]').textContent = 'Mengunggah...';
	uploadForm.requestSubmit();
};

if (uploadForm && templateInput && dropzone) {
	const filePicker = document.querySelector('[data-file-picker]');

	filePicker?.addEventListener('click', () => templateInput.click());
	templateInput.addEventListener('change', () => {
		const [file] = templateInput.files;

		if (file) {
			displaySelectedFile(file);
		}
	});

	uploadForm.addEventListener('submit', () => uploadForm.setAttribute('aria-busy', 'true'));

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
			displaySelectedFile(file);
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
    nameInput.setAttribute('aria-label', `Nama penerima baris ${index + 1}`);

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
    noteInput.setAttribute('aria-label', `Keterangan baris ${index + 1}`);

    const deleteButton = document.createElement('button');
    deleteButton.type = 'button';
    deleteButton.className = 'delete-row-button';
    deleteButton.dataset.deleteRow = '';
    deleteButton.setAttribute('aria-label', `Hapus baris ${index + 1}`);
    deleteButton.title = 'Hapus baris';

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

        row.querySelector('[data-row-number]').textContent = number;

        const nameInput = row.querySelector('[data-recipient-name]');
        const noteInput = row.querySelector('[data-recipient-note]');
        const deleteButton = row.querySelector('[data-delete-row]');

        nameInput.name = `recipients[${index}][name]`;
        nameInput.setAttribute('aria-label', `Nama penerima baris ${number}`);

        noteInput.name = `recipients[${index}][note]`;
        noteInput.setAttribute('aria-label', `Keterangan baris ${number}`);

        deleteButton.setAttribute('aria-label', `Hapus baris ${number}`);
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
		uploadStatus.textContent = `Mengunggah ${file.name}...`;
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
	previewToggle.querySelector('[data-preview-label]').textContent = previewExpanded ? 'Tampilkan Lebih Sedikit' : 'Tampilkan Semua';
});
