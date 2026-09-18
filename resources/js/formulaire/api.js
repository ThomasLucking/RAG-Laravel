function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]').content;
}

/**
 * Perform a JSON fetch against the Documents endpoints, throwing a
 * ValidationError when the server responds with 422 so callers can
 * render field errors consistently.
 */
export async function documentFetch(url, options = {}) {
    const response = await fetch(url, {
        ...options,
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            ...(options.body ? { 'Content-Type': 'application/json' } : {}),
            ...options.headers,
        },
    });

    if (response.status === 422) {
        const body = await response.json();
        throw new ValidationError(body.errors || {});
    }

    if (!response.ok && response.status !== 204) {
        throw new Error(`Request to ${url} failed with status ${response.status}.`);
    }

    if (response.status === 204) {
        return null;
    }

    return response.json();
}

export class ValidationError extends Error {
    constructor(errors) {
        super('Validation failed.');
        this.errors = errors;
    }
}

/**
 * Render field-level validation errors into `.modal-field-error[data-field]`
 * elements scoped under `form`.
 */
export function renderFieldErrors(form, errors) {
    Object.entries(errors).forEach(([field, messages]) => {
        const target = form.querySelector(`.modal-field-error[data-field="${field}"]`);
        if (target) target.textContent = messages[0];
    });
}
