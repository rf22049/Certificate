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
const deleteRecipientButton = document.querySelector('[data-delete-recipient]');

const createRecipientRow = (index) => {
	const row = document.createElement('tr');
	row.dataset.recipientRow = '';

	const numberCell = document.createElement('td');
	numberCell.className = 'recipient-number';
	const selector = document.createElement('input');
	selector.type = 'checkbox';
	selector.setAttribute('aria-label', `Pilih baris ${index + 1}`);
	selector.dataset.rowSelect = '';
	const number = document.createElement('span');
	number.dataset.rowNumber = '';
	numberCell.append(selector, number);
	row.append(numberCell);

	[['name', 'Nama penerima'], ['note', 'Keterangan']].forEach(([field, label]) => {
		const cell = document.createElement('td');
		const input = document.createElement('input');
		input.name = `recipients[${index}][${field}]`;
		input.maxLength = 255;
		input.setAttribute('aria-label', `${label} baris ${index + 1}`);
		input.dataset[field === 'name' ? 'recipientName' : 'recipientNote'] = '';
		cell.append(input);
		row.append(cell);
	});

	return row;
};

const renumberRecipientRows = () => {
	manualRows?.querySelectorAll('[data-recipient-row]').forEach((row, index) => {
		row.querySelector('[data-row-number]').textContent = String(index + 1);
		row.querySelector('[data-row-select]').setAttribute('aria-label', `Pilih baris ${index + 1}`);
		row.querySelector('[data-recipient-name]').setAttribute('aria-label', `Nama penerima baris ${index + 1}`);
		row.querySelector('[data-recipient-note]').setAttribute('aria-label', `Keterangan baris ${index + 1}`);
		row.querySelector('[data-recipient-name]').name = `recipients[${index}][name]`;
		row.querySelector('[data-recipient-note]').name = `recipients[${index}][note]`;
	});
};

addRecipientButton?.addEventListener('click', () => {
	if (manualRows.querySelectorAll('[data-recipient-row]').length >= 1000) {
		return;
	}

	manualRows.append(createRecipientRow(manualRows.querySelectorAll('[data-recipient-row]').length));
	renumberRecipientRows();
	manualRows.lastElementChild.querySelector('[data-recipient-name]').focus();
});

deleteRecipientButton?.addEventListener('click', () => {
	const rows = [...manualRows.querySelectorAll('[data-recipient-row]')];
	const selectedRows = rows.filter((row) => row.querySelector('[data-row-select]').checked);
	const rowsToDelete = selectedRows.length > 0 ? selectedRows : rows.slice(-1);

	rowsToDelete.forEach((row) => row.remove());

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
