export async function apiRequest(url, options = {}) {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
    const headers = {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': token,
        ...(options.body && !(options.body instanceof FormData) ? { 'Content-Type': 'application/json' } : {}),
        ...(options.headers ?? {}),
    };

    const response = await fetch(url, {
        ...options,
        headers,
    });

    const data = await response.json().catch(() => ({}));
    if (!response.ok) {
        const message = data.message || `Request failed with status ${response.status}`;
        throw new Error(message);
    }

    return data;
}
