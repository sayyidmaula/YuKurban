import React from 'react';
import { Head, Link } from '@inertiajs/react';
import PenabungLayout from '@/Layouts/PenabungLayout';
import { formatRupiah, formatTanggalWaktu } from '@/Utils/format';
import { ShoppingBag, ArrowRight, Clock, MapPin, CheckCircle2 } from 'lucide-react';

export default function OrderIndex({ orders }) {
    const orderList = orders?.data || [];

    return (
        <PenabungLayout>
            <Head title="Daftar Pesanan Kurban - YuKurban" />

            <div className="max-w-4xl mx-auto space-y-6">
                <div>
                    <h1 className="text-xl sm:text-2xl font-extrabold text-green-900">
                        Pesanan Kurban Saya
                    </h1>
                    <p className="text-xs text-muted mt-1">
                        Pantau riwayat reservasi, status penyembelihan, dan pengiriman kurban Anda.
                    </p>
                </div>

                {orderList.length === 0 ? (
                    <div className="bg-white border border-border rounded-card p-8 text-center space-y-4 shadow-sm">
                        <div className="w-12 h-12 rounded-full bg-green-50 text-green-600 mx-auto flex items-center justify-center">
                            <ShoppingBag className="w-6 h-6" />
                        </div>
                        <div>
                            <h3 className="text-sm font-bold text-green-900">Belum Ada Pesanan Kurban</h3>
                            <p className="text-xs text-muted mt-1">
                                Anda belum melakukan pemesanan kurban. Buka katalog untuk memilih hewan kurban impian Anda.
                            </p>
                        </div>
                        <Link
                            href="/katalog"
                            className="inline-flex items-center gap-1.5 px-4 py-2 rounded-btn bg-green-600 text-white text-xs font-bold hover:bg-green-700 transition-colors"
                        >
                            <span>Lihat Katalog Hewan</span>
                            <ArrowRight className="w-4 h-4" />
                        </Link>
                    </div>
                ) : (
                    <div className="space-y-4">
                        {orderList.map((ord) => (
                            <Link
                                key={ord.id}
                                href={`/pesanan/${ord.id}`}
                                className="block bg-white border border-border rounded-card p-5 hover:border-green-600 transition-all shadow-sm"
                            >
                                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-border pb-3">
                                    <div>
                                        <span className="text-[11px] font-mono font-bold text-muted uppercase">
                                            {ord.order_no}
                                        </span>
                                        <h3 className="text-sm font-bold text-green-900 mt-0.5">
                                            {ord.animal?.title || 'Hewan Kurban'}
                                        </h3>
                                    </div>

                                    <div className="flex items-center gap-2">
                                        <span className={`px-2.5 py-1 rounded text-xs font-bold capitalize ${
                                            ord.status === 'direservasi' ? 'bg-amber-100 text-amber-800' :
                                            ord.status === 'dikonfirmasi' ? 'bg-green-100 text-green-800' :
                                            ord.status === 'selesai' ? 'bg-blue-100 text-blue-800' : 'bg-surface text-muted'
                                        }`}>
                                            {ord.status.replace('_', ' ')}
                                        </span>
                                    </div>
                                </div>

                                <div className="pt-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                                    <div className="flex items-center gap-4 text-muted">
                                        <span className="flex items-center gap-1">
                                            <MapPin className="w-3.5 h-3.5 text-gold-600" />
                                            <span>{ord.region?.city}</span>
                                        </span>
                                        <span>•</span>
                                        <span className="capitalize">
                                            {ord.method === 'sembelih_tim' ? 'Sembelih Tim' : 'Kirim Hewan Hidup'}
                                        </span>
                                    </div>

                                    <div className="text-left sm:text-right">
                                        <span className="text-muted block text-[11px]">Total Biaya</span>
                                        <span className="font-bold text-green-900 tabular-nums">
                                            {formatRupiah(ord.total_buyer_pays)}
                                        </span>
                                    </div>
                                </div>
                            </Link>
                        ))}
                    </div>
                )}
            </div>
        </PenabungLayout>
    );
}

