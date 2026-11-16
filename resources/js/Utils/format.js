export function formatRupiah(amount) {
    if (amount === undefined || amount === null) return 'Rp 0';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount).replace(/\s/g, ' ');
}

export function formatNumber(number) {
    if (number === undefined || number === null) return '0';
    return new Intl.NumberFormat('id-ID').format(number);
}

