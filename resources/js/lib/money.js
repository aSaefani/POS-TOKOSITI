const formatter = new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
});

/** Format Rupiah. Nilai selalu integer (rupiah utuh), tidak pernah float. */
export const rupiah = (value) => formatter.format(Number(value) || 0);

/** Parse input nominal: hanya digit yang dipertahankan. */
export const digitsOnly = (raw) => Number(String(raw ?? '').replace(/\D/g, '')) || 0;

window.rupiah = rupiah;
