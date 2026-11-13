import React from 'react';
import { Head, Link } from '@inertiajs/react';
import PenabungLayout from '@/Layouts/PenabungLayout';
import { Target, PlusCircle, ShoppingBag, History, HelpCircle } from 'lucide-react';

export default function Beranda({ auth }) {
    return (
        <PenabungLayout>
            <Head title="Beranda Penabung - YuKurban" />

            <div className="space-y-6 max-w-4xl mx-auto">
                {/* Greeting */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-xl sm:text-2xl font-bold text-green-900">
                            Assalamu'alaikum, {auth.user?.name}
                        </h1>
                        <p className="text-xs text-muted mt-0.5">Semoga niat kurban Anda dimudahkan Allah SWT.</p>
                    </div>
                </div>

                {/* Hero Card Saldo (Preview in Fase 1, full functionality in Fase 2) */}
                <div className="relative overflow-hidden rounded-hero bg-gradient-to-r from-green-900 via-green-800 to-green-600 p-6 text-white shadow-md">
                    <div className="absolute right-0 top-0 translate-x-4 -translate-y-4 w-40 h-40 rounded-full bg-gold-500/10 blur-2xl pointer-events-none" />
                    
                    <div className="relative z-10 flex flex-col justify-between min-h-[140px]">
                        <div>
                            <span className="text-xs font-medium text-green-100/80">Total Saldo Tabungan Kurban</span>
                            <div className="text-2xl sm:text-3xl font-extrabold tracking-tight tabular-nums text-white mt-1">
                                Rp 0
                            </div>
                        </div>

                        <div className="pt-4 border-t border-green-700/60 flex items-center justify-between text-xs">
                            <span className="text-green-100/70">Pencatatan Ledger Amanah</span>
                            <Link href="/rencana/buat" className="text-gold-300 font-bold hover:text-white flex items-center gap-1">
                                <span>+ Buat Rencana Baru</span>
                            </Link>
                        </div>
                    </div>
                </div>

                {/* Aksi Cepat 4 Ikon (PRD Section 9) */}
                <div className="grid grid-cols-4 gap-3">
                    <Link
                        href="/rencana"
                        className="bg-white border border-border p-3 sm:p-4 rounded-card flex flex-col items-center justify-center text-center hover:bg-surface transition-colors shadow-sm"
                    >
                        <div className="w-10 h-10 rounded-full bg-green-50 text-green-600 flex items-center justify-center mb-1.5">
                            <PlusCircle className="w-5 h-5" />
                        </div>
                        <span className="text-[11px] sm:text-xs font-semibold text-text">Setor</span>
                    </Link>

                    <Link
                        href="/katalog"
                        className="bg-white border border-border p-3 sm:p-4 rounded-card flex flex-col items-center justify-center text-center hover:bg-surface transition-colors shadow-sm"
                    >
                        <div className="w-10 h-10 rounded-full bg-gold-100 text-gold-600 flex items-center justify-center mb-1.5">
                            <ShoppingBag className="w-5 h-5" />
                        </div>
                        <span className="text-[11px] sm:text-xs font-semibold text-text">Katalog</span>
                    </Link>

                    <Link
                        href="/pesanan"
                        className="bg-white border border-border p-3 sm:p-4 rounded-card flex flex-col items-center justify-center text-center hover:bg-surface transition-colors shadow-sm"
                    >
                        <div className="w-10 h-10 rounded-full bg-green-50 text-green-600 flex items-center justify-center mb-1.5">
                            <History className="w-5 h-5" />
                        </div>
                        <span className="text-[11px] sm:text-xs font-semibold text-text">Riwayat</span>
                    </Link>

                    <Link
                        href="/tiket"
                        className="bg-white border border-border p-3 sm:p-4 rounded-card flex flex-col items-center justify-center text-center hover:bg-surface transition-colors shadow-sm"
                    >
                        <div className="w-10 h-10 rounded-full bg-gold-100 text-gold-600 flex items-center justify-center mb-1.5">
                            <HelpCircle className="w-5 h-5" />
                        </div>
                        <span className="text-[11px] sm:text-xs font-semibold text-text">Bantuan</span>
                    </Link>
                </div>
            </div>
        </PenabungLayout>
    );
}

