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

export function formatTanggalWaktu(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        timeZone: 'Asia/Jakarta',
    }).format(date);
}

export function getAnimalImageUrl(path, type = 'domba') {
    if (path) {
        if (path.startsWith('http://') || path.startsWith('https://')) {
            return path;
        }
        if (path.startsWith('/')) {
            return path;
        }
        if (path.startsWith('animals/')) {
            return `/${path}`;
        }
        return `/storage/${path}`;
    }
    const safeType = (type || 'domba').toLowerCase();
    if (safeType === 'sapi') return '/animals/sapi-01.jpg';
    if (safeType === 'kambing') return '/animals/kambing-01.jpg';
    return '/animals/domba-01.jpg';
}

