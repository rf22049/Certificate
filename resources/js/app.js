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

const continueButton = document.querySelector('[data-next-step]');
const nextStepNotice = document.querySelector('[data-next-step-notice]');

continueButton?.addEventListener('click', () => {
	if (nextStepNotice) {
		nextStepNotice.hidden = false;
	}
});
