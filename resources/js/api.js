/**
 * RoPar API Helper
 * Fetch wrapper with CSRF, error handling, and utility functions
 */

function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

async function request(method, url, data = null, isFormData = false) {
    const headers = {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    };

    if (!isFormData) {
        headers['Content-Type'] = 'application/json';
    }

    headers['X-CSRF-TOKEN'] = getCsrfToken();

    const options = {
        method: method.toUpperCase(),
        headers,
        credentials: 'same-origin',
    };

    if (data && method.toUpperCase() !== 'GET') {
        options.body = isFormData ? data : JSON.stringify(data);
    }

    // Build query string for GET
    if (data && method.toUpperCase() === 'GET') {
        const params = new URLSearchParams();
        Object.entries(data).forEach(([key, value]) => {
            if (value !== null && value !== undefined && value !== '') {
                params.append(key, value);
            }
        });
        const qs = params.toString();
        if (qs) url += (url.includes('?') ? '&' : '?') + qs;
    }

    const response = await fetch(url, options);

    if (!response.ok) {
        let body = {};
        try {
            body = await response.json();
        } catch (_) {}

        if (response.status === 401) {
            window.location.href = '/login';
            return Promise.reject({ message: 'Sesi berakhir, silakan login kembali.' });
        }

        if (response.status === 403) {
            return Promise.reject({ message: body.message || 'Anda tidak punya akses.' });
        }

        if (response.status === 422) {
            return Promise.reject({ errors: body.errors || {}, message: body.message || 'Validasi gagal.' });
        }

        if (response.status === 429) {
            return Promise.reject({ message: 'Terlalu banyak percobaan, coba lagi sebentar.' });
        }

        if (response.status >= 500) {
            return Promise.reject({ message: 'Terjadi kesalahan pada server. Silakan coba lagi.' });
        }

        return Promise.reject({ message: body.message || 'Permintaan gagal (' + response.status + ').' });
    }

    if (response.status === 204 || response.headers.get('content-length') === '0') {
        return {};
    }

    return response.json();
}

const api = {
    get: (url, params) => request('GET', url, params),
    post: (url, data) => request('POST', url, data),
    patch: (url, data) => request('PATCH', url, data),
    delete: (url) => request('DELETE', url),

    /**
     * Upload with FormData. Supports method spoofing for PATCH via _method field.
     */
    upload: (url, formData, method = 'POST') => {
        if (method.toUpperCase() === 'PATCH') {
            formData.append('_method', 'PATCH');
            return request('POST', url, formData, true);
        }
        return request(method, url, formData, true);
    },
};

// --- Field-level vs toast error keys ---
// Keys that are NOT form field names should be shown as toast
const NON_FIELD_ERROR_KEYS = ['robot_unit_id', 'member', 'robot', 'robot_unit', 'loan', 'status'];

/**
 * Separate field errors from toast errors
 */
function separateErrors(errors) {
    const fieldErrors = {};
    const toastMessages = [];

    Object.entries(errors).forEach(([key, messages]) => {
        if (NON_FIELD_ERROR_KEYS.includes(key)) {
            toastMessages.push(...(Array.isArray(messages) ? messages : [messages]));
        } else {
            fieldErrors[key] = Array.isArray(messages) ? messages : [messages];
        }
    });

    return { fieldErrors, toastMessages };
}

// --- Date & Currency formatting ---

/**
 * Convert ISO string or date to YYYY-MM-DD in local browser time
 */
function toLocalDateString(isoString) {
    if (!isoString) return '';
    if (typeof isoString === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(isoString)) {
        return isoString;
    }
    const date = new Date(isoString);
    if (isNaN(date.getTime())) return String(isoString).substring(0, 10);
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
}

/**
 * Format ISO date or YYYY-MM-DD to Indonesian locale (e.g., "28 Okt 2026")
 */
function formatDate(isoString) {
    if (!isoString) return '-';
    let date;
    if (typeof isoString === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(isoString)) {
        const [y, m, d] = isoString.split('-').map(Number);
        date = new Date(y, m - 1, d);
    } else {
        date = new Date(isoString);
    }
    if (isNaN(date.getTime())) return '-';
    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    }).format(date);
}

/**
 * Format number to Indonesian Rupiah
 */
function formatRupiah(amount) {
    if (amount === null || amount === undefined) return 'Rp0';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0
    }).format(amount);
}

/**
 * Calculate days late from due_date (ISO string or YYYY-MM-DD)
 * Returns 0 if not late
 */
function calculateLateDays(dueDateIso) {
    if (!dueDateIso) return 0;
    const dateStr = toLocalDateString(dueDateIso);
    if (!dateStr) return 0;
    const [y, m, d] = dateStr.split('-').map(Number);
    const dueDate = new Date(y, m - 1, d);

    const today = new Date();
    today.setHours(0, 0, 0, 0);

    const diffMs = today - dueDate;
    const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
    return Math.max(0, diffDays);
}

/**
 * Calculate estimated fine (Rp5.000/day)
 */
function calculateFine(dueDateIso) {
    return calculateLateDays(dueDateIso) * 5000;
}

/**
 * Calculate remaining days until due date
 * Returns negative if overdue
 */
function calculateRemainingDays(dueDateIso) {
    if (!dueDateIso) return 0;
    const dateStr = toLocalDateString(dueDateIso);
    if (!dateStr) return 0;
    const [y, m, d] = dateStr.split('-').map(Number);
    const dueDate = new Date(y, m - 1, d);

    const today = new Date();
    today.setHours(0, 0, 0, 0);

    const diffMs = dueDate - today;
    return Math.floor(diffMs / (1000 * 60 * 60 * 24));
}

/**
 * Get today's date as YYYY-MM-DD
 */
function todayDate() {
    const d = new Date();
    return d.getFullYear() + '-' +
        String(d.getMonth() + 1).padStart(2, '0') + '-' +
        String(d.getDate()).padStart(2, '0');
}

/**
 * Get date N days from now as YYYY-MM-DD
 */
function addDays(dateStr, days) {
    const localStr = toLocalDateString(dateStr);
    const [y, m, d] = localStr.split('-').map(Number);
    const date = new Date(y, m - 1, d);
    date.setDate(date.getDate() + days);
    return date.getFullYear() + '-' +
        String(date.getMonth() + 1).padStart(2, '0') + '-' +
        String(date.getDate()).padStart(2, '0');
}

// Export to window
window.api = api;
window.separateErrors = separateErrors;
window.toLocalDateString = toLocalDateString;
window.formatDate = formatDate;
window.formatRupiah = formatRupiah;
window.calculateLateDays = calculateLateDays;
window.calculateFine = calculateFine;
window.calculateRemainingDays = calculateRemainingDays;
window.todayDate = todayDate;
window.addDays = addDays;
