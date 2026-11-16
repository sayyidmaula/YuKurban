import React from 'react';
import { Head, Link } from '@inertiajs/react';
import PenabungLayout from '@/Layouts/PenabungLayout';
import { formatRupiah, formatTanggalWaktu } from '@/Utils/format';
import { CheckCircle2, ArrowRight, Home, Receipt, MapPin } from 'lucide-react';

export default function OrderSukses({ order }) {
    return (
        <PenabungLayout>
            <Head title="Pemesanan Kurban Berhasil - YuKurban" />

            <div className="max-w-md mx-auto py-6 space-y-6">
                {/* Animasi Centang Sukses */}
                <div className="text-center space-y-3">
                    <div className="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 text-green-600 animate-bounce">
                        <CheckCircle2 className="w-10 h-10" />
                    </div>
                    <h1 className="text-xl font-extrabold text-green-900">
                        Pemesanan Kurban Berhasil!
                    </h1>
                    <p className="text-xs text-muted max-w-sm mx-auto">
                        Alhamdulillah, pembayaran dari tabungan Anda telah terverifikasi dan pesanan telah tercatat di buku besar amanah.
                    </p>
                </div>

                {/* Struk Transaksi */}
                <div className="bg-white border-2 border-dashed border-border rounded-card p-6 shadow-sm space-y-5 relative">
                    <div className="flex items-center justify-between border-b border-border pb-3">
                        <div className="flex items-center gap-1.5 text-xs font-bold text-green-900">
                            <Receipt className="w-4 h-4 text-green-600" />
                            <span>STRUK PEMBELIAN KURBAN</span>
                        </div>
                        <span className="text-[11px] font-mono text-muted">
                            {formatTanggalWaktu(order.confirmed_at || order.updated_at)}
                        </span>
                    </div>

                    <div className="space-y-3 text-xs">
                        <div className="flex justify-between">
                            <span className="text-muted">Nomor Pesanan:</span>
                            <span className="font-bold text-green-900 font-mono">{order.order_no}</span>
                        </div>

                        <div className="flex justify-between">
                            <span className="text-muted">Hewan Kurban:</span>
                            <span className="font-semibold text-text">{order.animal.title}</span>
                        </div>

                        <div className="flex justify-between">
                            <span className="text-muted">Metode:</span>
                            <span className="font-semibold text-text capitalize">
                                {order.method === 'sembelih_tim' ? 'Sembelih Tim' : 'Kirim Hewan Hidup'}
                            </span>
                        </div>

                        <div className="flex justify-between">
                            <span className="text-muted">Daerah Tujuan:</span>
                            <span className="font-semibold text-text">{order.region.city}</span>
                        </div>

                        <div className="flex justify-between">
                            <span className="text-muted">Sumber Dana Tabungan:</span>
                            <span className="font-semibold text-text">{order.savings_plan.name}</span>
                        </div>

                        <div className="pt-3 border-t border-border flex justify-between items-center text-sm">
                            <span className="font-bold text-green-900">Total Terdebet:</span>
                            <span className="text-lg font-black text-green-900 tabular-nums">
                                {formatRupiah(order.total_buyer_pays)}
                            </span>
                        </div>
                    </div>

                    <div className="p-3 bg-surface rounded-btn text-[11px] text-muted text-center leading-relaxed">
                        Simpan bukti struk ini. Tim platform akan segera menjadwalkan hewan kurban dan mengabari setiap tahap perkembangannya.
                    </div>
                </div>

                {/* Navigasi Aksi */}
                <div className="space-y-2 pt-2">
                    <Link
                        href={`/pesanan/${order.id}`}
                        className="w-full py-3 px-4 rounded-btn bg-green-600 text-white font-bold text-xs hover:bg-green-700 shadow-sm transition-colors flex items-center justify-center gap-2"
                    >
                        <span>Lihat Detail & Status Pesanan</span>
                        <ArrowRight className="w-4 h-4" />
                    </Link>

                    <Link
                        href="/beranda"
                        className="w-full py-2.5 px-4 rounded-btn border border-border text-muted font-semibold text-xs hover:text-text hover:bg-surface transition-colors flex items-center justify-center gap-2"
                    >
                        <Home className="w-3.5 h-3.5" />
                        <span>Kembali ke Beranda Tabungan</span>
                    </Link>
                </div>
            </div>
        </PenabungLayout>
    );
}

