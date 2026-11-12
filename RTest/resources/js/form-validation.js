// Delegate listeners so each modal shares the same bounded input handling.
const limitedField = (target) => target instanceof HTMLInputElement || target instanceof HTMLTextAreaElement
    ? (target.maxLength > 0 ? target : null)
    : null;

function boundedText(text, length) {
    const result = text.slice(0, Math.max(0, length));
    // Avoid splitting a UTF-16 surrogate pair at the character limit.
    return /[\uD800-\uDBFF]$/.test(result) ? result.slice(0, -1) : result;
}

function insertBounded(event, text) {
    const field = limitedField(event.target);
    if (!field || field.disabled || field.readOnly) return;
    event.preventDefault();
    const start = field.selectionStart ?? field.value.length;
    const end = field.selectionEnd ?? start;
    const room = field.maxLength - (field.value.length - (end - start));
    const inserted = boundedText(text, room);
    field.setRangeText(inserted, start, end, 'end');
    field.dispatchEvent(new Event('input', { bubbles: true }));
    if (text.length > room) {
        showLimit(field);
    }
}

function showLimit(field) {
    let hint = field.parentElement.querySelector('[data-input-limit-message]');
    if (!hint) {
        hint = document.createElement('small');
        hint.dataset.inputLimitMessage = '';
        hint.className = 'form-text text-warning d-block';
        hint.setAttribute('role', 'status');
        field.parentElement.append(hint);
    }
    hint.textContent = 'Se alcanzó el máximo de ' + field.maxLength + ' caracteres.';
}

document.addEventListener('paste', (event) => {
    if (limitedField(event.target) && event.clipboardData) {
        insertBounded(event, event.clipboardData.getData('text/plain'));
    }
});
document.addEventListener('drop', (event) => {
    if (limitedField(event.target) && event.dataTransfer) {
        insertBounded(event, event.dataTransfer.getData('text/plain'));
    }
});

function validateField(field) {
    if (field.matches('[data-catalog-name]')) {
        field.setCustomValidity('');
        if (field.value && (!field.value.trim() || field.validity.patternMismatch)) {
            field.setCustomValidity(field.title);
        }
    } else if (field.matches('input[type="text"][required], textarea[required]')) {
        field.setCustomValidity(field.value && !field.value.trim() ? 'Escribe un valor que no contenga solo espacios.' : '');
    }
    if (field.matches('input[type="file"][data-max-bytes]')) {
        const file = field.files[0];
        let error = '';
        if (file && !/\.(xlsx|xls|csv)$/i.test(file.name)) {
            error = 'Selecciona un archivo .xlsx, .xls o .csv.';
        } else if (file && file.size > Number(field.dataset.maxBytes)) {
            error = 'El archivo no puede superar los 2 MB.';
        } else if (file && file.size === 0) {
            error = 'El archivo está vacío. Selecciona uno que contenga datos.';
        }
        field.setCustomValidity(error);
        const feedback = field.closest('form').querySelector('[data-file-feedback]');
        if (feedback) feedback.textContent = error;
    }
}

document.addEventListener('input', (event) => {
    const field = limitedField(event.target);
    if (field) {
        if (field.value.length > field.maxLength) {
            field.value = boundedText(field.value, field.maxLength);
            showLimit(field);
        } else if (field.value.length < field.maxLength) {
            const hint = field.parentElement.querySelector('[data-input-limit-message]');
            if (hint) hint.textContent = '';
        }
    }
    if (event.target instanceof HTMLInputElement || event.target instanceof HTMLTextAreaElement) {
        validateField(event.target);
    }
});
document.addEventListener('change', (event) => {
    if (event.target instanceof HTMLInputElement) validateField(event.target);
});
document.querySelectorAll('input, textarea').forEach(validateField);
