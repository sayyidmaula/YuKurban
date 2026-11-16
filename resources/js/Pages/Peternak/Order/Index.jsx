import React from 'react';
import { Head } from '@inertiajs/react';
import PeternakLayout from '@/Layouts/PeternakLayout';
import { formatRupiah, formatTanggalWaktu } from '@/Utils/format';
import { ShoppingBag, ShieldCheck, Calendar, Clock, CheckCircle2 } from 'lucide-react';

export default function PeternakOrderIndex({ orders }) {
    const orderList = orders?.data || [];

    return (
        <PeternakLayout>
            <Head title="Daftar Pesanan Masuk - YuKurban Mitra" />

            <div className="max-w-4xl mx-auto space-y-6">
                <div>
                    <h1 className="text-xl sm:text-2xl font-extrabold text-green-900">
                        Pesanan Hewan Masuk
                    </h1>
                    <p className="text-xs text-muted mt-1">
                        Daftar hewan kurban kandang Anda yang telah dipesan dan dikonfirmasi pembeli.
                    </p>
                </div>

                {/* Privacy Badge BR-10 */}
                <div className="p-4 bg-green-50 border border-green-200 rounded-card flex items-start gap-3">
                    <ShieldCheck className="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" />
                    <div className="text-xs text-green-900 space-y-1">
                        <span className="font-bold">Privasi Terjaga (BR-10):</span>
                        <p className="text-green-800 leading-relaxed">
                            Sesuai aturan transparansi dan amanah platform, data kontak pembeli dijaga privasinya oleh sistem. Koordinasi pengambilan hewan atau penyembelihan dilakukan langsung melalui tim platform YuKurban.
                        </p>
                    </div>
                </div>

                {orderList.length === 0 ? (
                    <div className="bg-white border border-border rounded-card p-8 text-center space-y-3 shadow-sm">
                        <div className="w-12 h-12 rounded-full bg-green-50 text-green-600 mx-auto flex items-center justify-center">
                            <ShoppingBag className="w-6 h-6" />
                        </div>
                        <h3 className="text-sm font-bold text-green-900">Belum Ada Pesanan Dikonfirmasi</h3>
                        <p className="text-xs text-muted">
                            Hewan kurban yang berstatus tayang dan berhasil dikonfirmasi oleh penabung akan tampil di sini.
                        </p>
                    </div>
                ) : (
                    <div className="space-y-4">
                        {orderList.map((ord) => (
                            <div
                                key={ord.id}
                                className="bg-white border border-border rounded-card p-5 shadow-sm space-y-4"
                            >
                                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-border pb-3">
                                    <div>
                                        <span className="text-[11px] font-mono text-muted uppercase font-bold">
                                            {ord.order_no}
                                        </span>
                                        <h3 className="text-sm font-bold text-green-900 mt-0.5">
                                            {ord.animal?.title}
                                        </h3>
                                    </div>

                                    <span className={`px-2.5 py-1 rounded text-xs font-bold capitalize self-start sm:self-auto ${
                                        ord.status === 'dikonfirmasi' ? 'bg-amber-100 text-amber-800' :
                                        ord.status === 'selesai' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'
                                    }`}>
                                        {ord.status.replace('_', ' ')}
                                    </span>
                                </div>

                                <div className="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                                    <div className="p-3 bg-surface rounded-btn">
                                        <span className="text-[11px] text-muted block">Metode</span>
                                        <span className="font-bold text-text capitalize">
                                            {ord.method === 'sembelih_tim' ? 'Sembelih Tim' : 'Kirim Hewan Hidup'}
                                        </span>
                                    </div>

                                    <div className="p-3 bg-surface rounded-btn">
                                        <span className="text-[11px] text-muted block">Harga Hewan</span>
                                        <span className="font-bold text-green-900 tabular-nums">
                                            {formatRupiah(ord.animal_price)}
                                        </span>
                                    </div>

                                    <div className="p-3 bg-surface rounded-btn">
                                        <span className="text-[11px] text-muted block">Jadwal Pengambilan / Sembelih</span>
                                        <span className="font-bold text-text">
                                            {ord.scheduled_at ? formatTanggalWaktu(ord.scheduled_at) : 'Menunggu Jadwal Tim'}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </PeternakLayout>
    );
}

