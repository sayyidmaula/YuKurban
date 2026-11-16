import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import PenabungLayout from '@/Layouts/PenabungLayout';
import { ArrowLeft, Send, AlertTriangle, HelpCircle } from 'lucide-react';

export default function TicketCreate({ initialType, selectedOrder, selectedAnimal, orders }) {
    const { data, setData, post, processing, errors } = useForm({
        type: initialType || 'pertanyaan_prabeli',
        order_id: selectedOrder?.id || '',
        animal_id: selectedAnimal?.id || '',
        category: initialType === 'laporan_masalah' ? 'Kualitas Hewan' : 'Informasi Hewan',
        subject: '',
        body: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/tiket');
    };

    return (
        <PenabungLayout>
            <Head title="Buka Tiket Bantuan - YuKurban" />

            <div className="max-w-2xl mx-auto space-y-6">
                <div>
                    <Link
                        href="/tiket"
                        className="inline-flex items-center gap-1.5 text-xs font-semibold text-green-600 hover:text-green-700 mb-2"
                    >
                        <ArrowLeft className="w-4 h-4" />
                        <span>Kembali ke Pusat Tiket</span>
                    </Link>
                    <h1 className="text-xl sm:text-2xl font-extrabold text-green-900 tracking-tight">
                        Buka Tiket Bantuan Baru
                    </h1>
                    <p className="text-xs text-muted mt-1">
                        Pilih tipe bantuan yang Anda butuhkan agar tim kami dapat merespons dengan tepat.
                    </p>
                </div>

                <form onSubmit={handleSubmit} className="bg-white border border-border rounded-card p-6 shadow-sm space-y-5">
                    {/* Tipe Bantuan */}
                    <div>
                        <label className="text-xs font-bold text-green-900 block mb-2 uppercase tracking-wider text-[11px]">
                            Tipe Bantuan
                        </label>
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label className={`p-3.5 border rounded-btn flex items-start gap-3 cursor-pointer transition-colors ${
                                data.type === 'pertanyaan_prabeli' ? 'border-green-600 bg-green-50' : 'border-border'
                            }`}>
                                <input
                                    type="radio"
                                    name="type"
                                    value="pertanyaan_prabeli"
                                    checked={data.type === 'pertanyaan_prabeli'}
                                    onChange={(e) => setData('type', e.target.value)}
                                    className="mt-0.5 text-green-600 focus:ring-green-500"
                                />
                                <div>
                                    <span className="text-xs font-bold text-green-900 block">Pertanyaan Pra-Beli</span>
                                    <span className="text-[11px] text-muted">Seputar detail hewan kurban, riwayat vaksin, atau wilayah.</span>
                                </div>
                            </label>

                            <label className={`p-3.5 border rounded-btn flex items-start gap-3 cursor-pointer transition-colors ${
                                data.type === 'laporan_masalah' ? 'border-red-600 bg-red-50' : 'border-border'
                            }`}>
                                <input
                                    type="radio"
                                    name="type"
                                    value="laporan_masalah"
                                    checked={data.type === 'laporan_masalah'}
                                    onChange={(e) => setData('type', e.target.value)}
                                    className="mt-0.5 text-red-600 focus:ring-red-500"
                                />
                                <div>
                                    <span className="text-xs font-bold text-red-900 block">Laporan Masalah Pesanan</span>
                                    <span className="text-[11px] text-muted">Kendala jadwal, kondisi fisik saat tiba/sembelih (BR-15).</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    {/* Jika Laporan Masalah: Pilih Pesanan */}
                    {data.type === 'laporan_masalah' && (
                        <div className="p-4 bg-surface rounded-btn border border-border space-y-3">
                            <label className="text-xs font-bold text-text block">Pilih Nomor Pesanan</label>
                            <select
                                value={data.order_id}
                                onChange={(e) => setData('order_id', e.target.value)}
                                className="w-full text-xs rounded-btn border border-border p-2.5 focus:ring-2 focus:ring-green-600 focus:outline-none"
                                required
                            >
                                <option value="">-- Pilih Pesanan Terkait --</option>
                                {orders.map((ord) => (
                                    <option key={ord.id} value={ord.id}>
                                        {ord.order_no} - {ord.status.toUpperCase()}
                                    </option>
                                ))}
                            </select>
                            {errors.order_id && <p className="text-xs text-red-600">{errors.order_id}</p>}
                            <p className="text-[11px] text-muted">
                                * Berdasarkan BR-15, laporan hanya dapat diajukan maksimal 48 jam setelah hewan disalurkan atau diterima.
                            </p>
                        </div>
                    )}

                    {/* Kategori */}
                    <div>
                        <label className="text-xs font-semibold text-text block mb-1">Kategori Masalah</label>
                        <input
                            type="text"
                            value={data.category}
                            onChange={(e) => setData('category', e.target.value)}
                            placeholder="Contoh: Kualitas Hewan, Jadwal Pengiriman, dll."
                            className="w-full text-xs rounded-btn border border-border p-2.5 focus:ring-2 focus:ring-green-600 focus:outline-none"
                            required
                        />
                        {errors.category && <p className="text-xs text-red-600 mt-1">{errors.category}</p>}
                    </div>

                    {/* Subject */}
                    <div>
                        <label className="text-xs font-semibold text-text block mb-1">Judul / Subjek Tiket</label>
                        <input
                            type="text"
                            value={data.subject}
                            onChange={(e) => setData('subject', e.target.value)}
                            placeholder="Ringkasan singkat pertanyaan atau laporan"
                            className="w-full text-xs rounded-btn border border-border p-2.5 focus:ring-2 focus:ring-green-600 focus:outline-none"
                            required
                        />
                        {errors.subject && <p className="text-xs text-red-600 mt-1">{errors.subject}</p>}
                    </div>

                    {/* Body */}
                    <div>
                        <label className="text-xs font-semibold text-text block mb-1">Rincian Pertanyaan / Laporan</label>
                        <textarea
                            value={data.body}
                            onChange={(e) => setData('body', e.target.value)}
                            rows={4}
                            placeholder="Tuliskan secara lengkap dan jelas rincian yang ingin Anda tanyakan atau laporkan..."
                            className="w-full text-xs rounded-btn border border-border p-2.5 focus:ring-2 focus:ring-green-600 focus:outline-none"
                            required
                        />
                        {errors.body && <p className="text-xs text-red-600 mt-1">{errors.body}</p>}
                    </div>

                    <button
                        type="submit"
                        disabled={processing}
                        className="w-full py-3 px-4 rounded-btn bg-green-600 text-white font-bold text-xs hover:bg-green-700 shadow-sm transition-colors flex items-center justify-center gap-2"
                    >
                        <Send className="w-4 h-4" />
                        <span>{processing ? 'Mengirim Tiket...' : 'Kirim Tiket Sekarang'}</span>
                    </button>
                </form>
            </div>
        </PenabungLayout>
    );
}
