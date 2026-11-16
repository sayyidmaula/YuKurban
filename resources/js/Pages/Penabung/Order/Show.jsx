import React, { useState, useEffect } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import PenabungLayout from '@/Layouts/PenabungLayout';
import { formatRupiah, formatTanggalWaktu } from '@/Utils/format';
import { ArrowLeft, Clock, ShieldCheck, CheckCircle2, AlertTriangle, Truck, MapPin, Receipt, XCircle } from 'lucide-react';

export default function OrderShow({ order }) {
    const isReserved = order.status === 'direservasi';
    const isConfirmed = order.status === 'dikonfirmasi';
    const isCancelled = order.status === 'dibatalkan' || order.status === 'kedaluwarsa';

    const { data, setData, post, processing, errors } = useForm({
        wakalah_agreed: false,
        distribution_note: '',
        recipient_name: order.recipient_name || '',
        recipient_phone: order.recipient_phone || '',
        recipient_address: order.recipient_address || '',
    });

    const [timeLeft, setTimeLeft] = useState('');

    useEffect(() => {
        if (!isReserved || !order.reserved_until) return;

        const updateTimer = () => {
            const diff = new Date(order.reserved_until).getTime() - new Date().getTime();
            if (diff <= 0) {
                setTimeLeft('Kedaluwarsa');
                return;
            }
            const hours = Math.floor(diff / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);
            setTimeLeft(`${hours}j ${minutes}m ${seconds}d`);
        };

        updateTimer();
        const interval = setInterval(updateTimer, 1000);
        return () => clearInterval(interval);
    }, [isReserved, order.reserved_until]);

    const handleConfirm = (e) => {
        e.preventDefault();
        post(`/pesanan/${order.id}/konfirmasi`);
    };

    const handleCancel = (e) => {
        if (confirm('Apakah Anda yakin ingin membatalkan pesanan ini? Dana akan dikembalikan penuh ke tabungan Anda.')) {
            post(`/pesanan/${order.id}/batal`);
        }
    };

    return (
        <PenabungLayout>
            <Head title={`Pesanan ${order.order_no} - YuKurban`} />

            <div className="max-w-3xl mx-auto space-y-6">
                <div className="border-b border-border pb-3 flex items-center justify-between">
                    <Link
                        href="/pesanan"
                        className="inline-flex items-center gap-1.5 text-xs font-semibold text-green-600 hover:text-green-700"
                    >
                        <ArrowLeft className="w-4 h-4" />
                        <span>Daftar Pesanan Kurban</span>
                    </Link>

                    <span className={`px-2.5 py-1 rounded text-xs font-bold capitalize ${
                        isReserved ? 'bg-amber-100 text-amber-800' :
                        isConfirmed ? 'bg-green-100 text-green-800' :
                        isCancelled ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'
                    }`}>
                        Status: {order.status.replace('_', ' ')}
                    </span>
                </div>

                {/* Banner Status & Timer Reservasi (BR-06) */}
                {isReserved && (
                    <div className="bg-amber-50 border border-amber-200 rounded-card p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-sm">
                        <div className="flex items-center gap-3">
                            <Clock className="w-5 h-5 text-amber-600 flex-shrink-0 animate-pulse" />
                            <div>
                                <h3 className="text-xs font-bold text-amber-900">
                                    Hewan Sedang Direservasi (Kunci Stok 24 Jam)
                                </h3>
                                <p className="text-[11px] text-amber-800 mt-0.5">
                                    Selesaikan konfirmasi dan persetujuan kuasa sebelum batas waktu berakhir.
                                </p>
                            </div>
                        </div>

                        <div className="bg-white px-3 py-1.5 rounded-btn border border-amber-300 text-center flex-shrink-0">
                            <span className="text-[10px] text-muted block uppercase">Sisa Waktu</span>
                            <span className="text-xs font-black text-amber-900 tabular-nums">{timeLeft}</span>
                        </div>
                    </div>
                )}

                {/* Order Summary Card */}
                <div className="bg-white border border-border rounded-card p-6 shadow-sm space-y-5">
                    <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-border pb-4">
                        <div>
                            <span className="text-[11px] text-muted uppercase font-bold tracking-wider">Nomor Pesanan</span>
                            <h2 className="text-lg font-black text-green-900 tracking-tight">{order.order_no}</h2>
                        </div>
                        <div className="text-xs text-muted">
                            Dibuat: {formatTanggalWaktu(order.created_at)}
                        </div>
                    </div>

                    {/* Animal Details */}
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div className="p-3.5 bg-surface rounded-btn border border-border space-y-1">
                            <span className="text-[11px] text-muted block font-semibold">Hewan Kurban</span>
                            <p className="font-bold text-green-900 text-sm">{order.animal.title}</p>
                            <p className="text-muted">
                                {order.animal.type.toUpperCase()} • {order.animal.weight_kg} kg • {order.animal.age_months} Bulan
                            </p>
                        </div>

                        <div className="p-3.5 bg-surface rounded-btn border border-border space-y-1">
                            <span className="text-[11px] text-muted block font-semibold">Metode & Daerah Tujuan</span>
                            <p className="font-bold text-green-900 capitalize">
                                {order.method === 'sembelih_tim' ? 'Sembelih Tim (Daging Disalurkan)' : 'Kirim Hewan Hidup'}
                            </p>
                            <p className="text-muted flex items-center gap-1">
                                <MapPin className="w-3.5 h-3.5 text-gold-600" />
                                <span>{order.region.city}, {order.region.province}</span>
                            </p>
                        </div>
                    </div>

                    {/* Cost Breakdown BR-09 */}
                    <div className="p-4 bg-surface rounded-btn border border-border space-y-2.5 text-xs">
                        <h4 className="font-bold text-green-900 uppercase tracking-wider text-[11px]">
                            Rincian Pembayaran
                        </h4>

                        <div className="flex justify-between items-center text-muted">
                            <span>Harga Hewan:</span>
                            <span className="font-semibold text-text tabular-nums">{formatRupiah(order.animal_price)}</span>
                        </div>

                        {order.method === 'sembelih_tim' ? (
                            <>
                                <div className="flex justify-between items-center text-muted">
                                    <span>Ujrah Penyembelihan:</span>
                                    <span className="font-semibold text-text tabular-nums">{formatRupiah(order.fee_slaughter)}</span>
                                </div>
                                <div className="flex justify-between items-center text-muted">
                                    <span>Ujrah Penyaluran Daging:</span>
                                    <span className="font-semibold text-text tabular-nums">{formatRupiah(order.fee_distribution)}</span>
                                </div>
                            </>
                        ) : (
                            <div className="flex justify-between items-center text-muted">
                                <span>Ongkir Pengiriman:</span>
                                <span className="font-semibold text-text tabular-nums">{formatRupiah(order.fee_delivery)}</span>
                            </div>
                        )}

                        <div className="pt-2 border-t border-border flex justify-between items-center text-sm font-bold text-green-900">
                            <span>Total Debet Tabungan:</span>
                            <span className="text-base font-black tabular-nums">{formatRupiah(order.total_buyer_pays)}</span>
                        </div>
                    </div>
                </div>

                {/* Form Konfirmasi Jika Masih Reservasi */}
                {isReserved && (
                    <form onSubmit={handleConfirm} className="bg-white border border-border rounded-card p-6 shadow-sm space-y-5">
                        <h3 className="text-sm font-bold text-green-900 border-b border-border pb-2">
                            Konfirmasi Pembelian & Persetujuan Akad
                        </h3>

                        {order.method === 'sembelih_tim' ? (
                            <div className="space-y-4">
                                {/* BR-12 Wakalah Agreement */}
                                <div className="p-4 bg-green-50 border border-green-200 rounded-btn space-y-3">
                                    <div className="flex items-start gap-3">
                                        <input
                                            type="checkbox"
                                            id="wakalah_agreed"
                                            checked={data.wakalah_agreed}
                                            onChange={(e) => setData('wakalah_agreed', e.target.checked)}
                                            className="mt-0.5 rounded text-green-600 focus:ring-green-500 w-4 h-4"
                                            required
                                        />
                                        <label htmlFor="wakalah_agreed" className="text-xs text-green-900 leading-relaxed cursor-pointer">
                                            <strong>Persetujuan Kuasa (Akad Wakalah bil Ujrah):</strong><br />
                                            Dengan ini saya memberikan kuasa (wakalah) kepada tim YuKurban untuk menyembelih hewan kurban atas nama saya sesuai syariat Islam, mendokumentasikan prosesnya, serta menyalurkan daging kurban kepada penerima manfaat yang berhak di wilayah tujuan.
                                        </label>
                                    </div>
                                    {errors.wakalah_agreed && (
                                        <p className="text-xs text-red-600 pl-7">{errors.wakalah_agreed}</p>
                                    )}
                                </div>

                                <div>
                                    <label className="text-xs font-semibold text-text block mb-1">
                                        Catatan Penyaluran Daging (Opsional)
                                    </label>
                                    <textarea
                                        value={data.distribution_note}
                                        onChange={(e) => setData('distribution_note', e.target.value)}
                                        placeholder="Contoh: Prioritaskan warga lansia di RW 04, atau catatan khusus lainnya."
                                        rows={2}
                                        className="w-full text-xs rounded-btn border border-border p-2.5 focus:ring-2 focus:ring-green-600 focus:outline-none"
                                    />
                                </div>
                            </div>
                        ) : (
                            <div className="space-y-3">
                                <p className="text-xs text-muted">
                                    Lengkapi informasi alamat tujuan pengiriman hewan hidup:
                                </p>

                                <div>
                                    <label className="text-xs font-semibold text-text block mb-1">Nama Penerima</label>
                                    <input
                                        type="text"
                                        value={data.recipient_name}
                                        onChange={(e) => setData('recipient_name', e.target.value)}
                                        className="w-full text-xs rounded-btn border border-border p-2.5 focus:ring-2 focus:ring-green-600 focus:outline-none"
                                        required
                                    />
                                    {errors.recipient_name && <p className="text-xs text-red-600 mt-1">{errors.recipient_name}</p>}
                                </div>

                                <div>
                                    <label className="text-xs font-semibold text-text block mb-1">Nomor Telepon / WhatsApp</label>
                                    <input
                                        type="tel"
                                        value={data.recipient_phone}
                                        onChange={(e) => setData('recipient_phone', e.target.value)}
                                        className="w-full text-xs rounded-btn border border-border p-2.5 focus:ring-2 focus:ring-green-600 focus:outline-none"
                                        required
                                    />
                                    {errors.recipient_phone && <p className="text-xs text-red-600 mt-1">{errors.recipient_phone}</p>}
                                </div>

                                <div>
                                    <label className="text-xs font-semibold text-text block mb-1">Alamat Lengkap Pengiriman</label>
                                    <textarea
                                        value={data.recipient_address}
                                        onChange={(e) => setData('recipient_address', e.target.value)}
                                        rows={3}
                                        className="w-full text-xs rounded-btn border border-border p-2.5 focus:ring-2 focus:ring-green-600 focus:outline-none"
                                        required
                                    />
                                    {errors.recipient_address && <p className="text-xs text-red-600 mt-1">{errors.recipient_address}</p>}
                                </div>
                            </div>
                        )}

                        <button
                            type="submit"
                            disabled={processing}
                            className="w-full py-3.5 px-4 rounded-btn bg-green-600 text-white font-bold text-sm hover:bg-green-700 shadow-md transition-colors flex items-center justify-center gap-2"
                        >
                            <CheckCircle2 className="w-4 h-4" />
                            <span>{processing ? 'Memproses Konfirmasi...' : 'Konfirmasi & Debet Saldo Tabungan'}</span>
                        </button>
                    </form>
                )}

                {/* Tombol Pembatalan Sebelum Jadwal (BR-14) */}
                {isConfirmed && (
                    <div className="bg-white border border-border rounded-card p-5 shadow-sm space-y-3">
                        <h4 className="text-xs font-bold text-green-900">Pembatalan Mandiri (BR-14)</h4>
                        <p className="text-xs text-muted leading-relaxed">
                            Pesanan Anda telah dikonfirmasi. Anda masih dapat membatalkan pesanan secara mandiri sebelum tim menjadwalkan hewan. Dana akan dikembalikan 100% ke saldo tabungan Anda.
                        </p>
                        <button
                            onClick={handleCancel}
                            disabled={processing}
                            className="inline-flex items-center gap-1.5 px-4 py-2 border border-red-300 text-red-700 text-xs font-bold rounded-btn hover:bg-red-50 transition-colors"
                        >
                            <XCircle className="w-4 h-4" />
                            <span>Batalkan Pesanan Ini (Kembalikan Saldo)</span>
                        </button>
                    </div>
                )}
            </div>
        </PenabungLayout>
    );
}

