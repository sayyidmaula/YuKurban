import React, { useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import PenabungLayout from '@/Layouts/PenabungLayout';
import { formatRupiah } from '@/Utils/format';
import { Eye, EyeOff, PlusCircle, ShoppingBag, History, HelpCircle, ArrowDownLeft, ArrowUpRight, Target, ChevronRight, Sparkles } from 'lucide-react';

export default function Beranda({ auth, totalBalance = 0, activePlans = [], recentEntries = [] }) {
    const [showBalance, setShowBalance] = useState(true);

    // Group recent entries by date
    const groupedEntries = recentEntries.reduce((acc, entry) => {
        const date = entry.created_date;
        if (!acc[date]) acc[date] = [];
        acc[date].push(entry);
        return acc;
    }, {});

    return (
        <PenabungLayout>
            <Head title="Beranda Tabungan - YuKurban" />

            <div className="space-y-6 max-w-4xl mx-auto">
                {/* Greeting & Quick Summary */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-xl sm:text-2xl font-bold text-green-900">
                            Assalamu'alaikum, {auth.user?.name}
                        </h1>
                        <p className="text-xs text-muted mt-0.5">Semoga niat kurban Anda senantiasa diberkahi.</p>
                    </div>
                </div>

                {/* Hero Kartu Saldo (PRD Section 9) */}
                <div className="relative overflow-hidden rounded-hero bg-gradient-to-br from-green-900 via-green-800 to-green-600 p-6 sm:p-7 text-white shadow-lg border border-gold-500/20">
                    {/* Islamic Geometric pattern overlay */}
                    <div className="absolute inset-0 opacity-6 pointer-events-none bg-[radial-gradient(#C9A227_1.5px,transparent_1.5px)] [background-size:20px_20px]" />
                    <div className="absolute right-0 top-0 translate-x-8 -translate-y-8 w-48 h-48 rounded-full bg-gold-500/15 blur-3xl pointer-events-none" />

                    <div className="relative z-10 flex flex-col justify-between min-h-[140px]">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-medium text-green-100/90 tracking-wide uppercase">
                                Total Saldo Tabungan Kurban
                            </span>
                            <button
                                onClick={() => setShowBalance(!showBalance)}
                                className="p-1.5 rounded-full hover:bg-white/10 text-green-100 transition-colors"
                                title={showBalance ? 'Sembunyikan saldo' : 'Tampilkan saldo'}
                            >
                                {showBalance ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                            </button>
                        </div>

                        <div className="my-3">
                            <div className="text-2xl sm:text-4xl font-extrabold tracking-tight tabular-nums text-white">
                                {showBalance ? formatRupiah(totalBalance) : '••••••••'}
                            </div>
                            <p className="text-[11px] text-green-100/70 mt-1">
                                Saldo amanah tercatat mutlak di buku besar (append-only ledger)
                            </p>
                        </div>

                        <div className="pt-4 border-t border-green-700/60 flex items-center justify-between text-xs">
                            <span className="text-green-100/80">
                                {activePlans.length} Rencana Tabungan Aktif
                            </span>
                            <Link
                                href="/rencana/buat"
                                className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-btn bg-gold-500 text-green-950 font-bold hover:bg-gold-300 transition-colors shadow-sm text-xs"
                            >
                                <Sparkles className="w-3.5 h-3.5" />
                                <span>+ Buat Rencana Baru</span>
                            </Link>
                        </div>
                    </div>
                </div>

                {/* Aksi Cepat Grid 4 Ikon (PRD Section 9) */}
                <div className="grid grid-cols-4 gap-3">
                    <Link
                        href={activePlans.length > 0 ? `/rencana/${activePlans[0].id}/setor` : '/rencana'}
                        className="bg-white border border-border p-3 sm:p-4 rounded-card flex flex-col items-center justify-center text-center hover:bg-surface hover:border-green-600 transition-all shadow-sm"
                    >
                        <div className="w-11 h-11 rounded-full bg-green-50 text-green-600 flex items-center justify-center mb-1.5 shadow-sm">
                            <PlusCircle className="w-5 h-5 stroke-[2]" />
                        </div>
                        <span className="text-xs font-semibold text-text">Setor</span>
                    </Link>

                    <Link
                        href="/katalog"
                        className="bg-white border border-border p-3 sm:p-4 rounded-card flex flex-col items-center justify-center text-center hover:bg-surface hover:border-gold-500 transition-all shadow-sm"
                    >
                        <div className="w-11 h-11 rounded-full bg-gold-100 text-gold-600 flex items-center justify-center mb-1.5 shadow-sm">
                            <ShoppingBag className="w-5 h-5 stroke-[2]" />
                        </div>
                        <span className="text-xs font-semibold text-text">Katalog</span>
                    </Link>

                    <Link
                        href="/pesanan"
                        className="bg-white border border-border p-3 sm:p-4 rounded-card flex flex-col items-center justify-center text-center hover:bg-surface transition-all shadow-sm"
                    >
                        <div className="w-11 h-11 rounded-full bg-green-50 text-green-600 flex items-center justify-center mb-1.5 shadow-sm">
                            <History className="w-5 h-5 stroke-[2]" />
                        </div>
                        <span className="text-xs font-semibold text-text">Pesanan</span>
                    </Link>

                    <Link
                        href="/tiket"
                        className="bg-white border border-border p-3 sm:p-4 rounded-card flex flex-col items-center justify-center text-center hover:bg-surface transition-all shadow-sm"
                    >
                        <div className="w-11 h-11 rounded-full bg-gold-100 text-gold-600 flex items-center justify-center mb-1.5 shadow-sm">
                            <HelpCircle className="w-5 h-5 stroke-[2]" />
                        </div>
                        <span className="text-xs font-semibold text-text">Bantuan</span>
                    </Link>
                </div>

                {/* Kartu Rencana Tabungan (Goal Setting) */}
                <div className="space-y-3">
                    <div className="flex items-center justify-between">
                        <h2 className="text-sm font-bold text-green-900 uppercase tracking-wide">
                            Rencana Tabungan Kurban Anda
                        </h2>
                        <Link href="/rencana" className="text-xs font-semibold text-green-600 hover:text-green-700">
                            Lihat Semua ({activePlans.length}) →
                        </Link>
                    </div>

                    {activePlans.length === 0 ? (
                        <div className="bg-white border border-border rounded-card p-6 text-center shadow-sm">
                            <div className="w-12 h-12 rounded-full bg-green-50 text-green-600 flex items-center justify-center mx-auto mb-3">
                                <Target className="w-6 h-6" />
                            </div>
                            <h3 className="text-sm font-bold text-text">Belum Ada Rencana Kurban</h3>
                            <p className="text-xs text-muted mt-1 max-w-sm mx-auto">
                                Mulai tentukan target kurban Anda untuk tahun ini atau tahun depan, pilih tingkat harga, dan tabung berkala.
                            </p>
                            <Link
                                href="/rencana/buat"
                                className="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-btn bg-green-600 text-white font-semibold text-xs hover:bg-green-700 shadow-sm"
                            >
                                <span>Buat Rencana Pertama</span>
                            </Link>
                        </div>
                    ) : (
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {activePlans.map((plan) => (
                                <Link
                                    key={plan.id}
                                    href={`/rencana/${plan.id}`}
                                    className="bg-white border border-border rounded-card p-5 hover:border-green-600 shadow-sm transition-all flex flex-col justify-between"
                                >
                                    <div>
                                        <div className="flex items-center justify-between">
                                            <span className="font-bold text-sm text-green-900 truncate">{plan.name}</span>
                                            {plan.tier && (
                                                <span className="px-2 py-0.5 rounded text-[10px] font-bold bg-gold-100 text-gold-600 capitalize">
                                                    {plan.tier}
                                                </span>
                                            )}
                                        </div>
                                        <div className="mt-2 text-xs text-muted">
                                            Target Kurban Idul Adha <strong>{plan.target_year}</strong>
                                        </div>
                                    </div>

                                    {/* Progress Bar Emas */}
                                    <div className="mt-4">
                                        <div className="flex items-center justify-between text-xs mb-1">
                                            <span className="font-bold tabular-nums text-green-800">
                                                {formatRupiah(plan.balance_cached)}
                                            </span>
                                            <span className="text-muted tabular-nums">
                                                dari {formatRupiah(plan.target_amount)}
                                            </span>
                                        </div>
                                        <div className="w-full bg-surface rounded-full h-2.5 overflow-hidden border border-border">
                                            <div
                                                className="bg-gold-500 h-2.5 rounded-full transition-all duration-500"
                                                style={{ width: `${plan.progress_percentage}%` }}
                                            />
                                        </div>
                                        <div className="mt-1.5 flex items-center justify-between text-[11px] text-muted">
                                            <span className="font-semibold text-gold-600 tabular-nums">
                                                {plan.progress_percentage}% Tercapai
                                            </span>
                                            <span>Buka Detail →</span>
                                        </div>
                                    </div>
                                </Link>
                            ))}
                        </div>
                    )}
                </div>

                {/* Riwayat Mutasi Buku Besar (Ledger) Terbaru */}
                <div className="space-y-3 pt-2">
                    <h2 className="text-sm font-bold text-green-900 uppercase tracking-wide">
                        Mutasi Buku Besar Terbaru
                    </h2>

                    {recentEntries.length === 0 ? (
                        <div className="bg-white border border-border rounded-card p-6 text-center shadow-sm">
                            <p className="text-xs text-muted">Belum ada riwayat transaksi setoran atau penarikan.</p>
                        </div>
                    ) : (
                        <div className="space-y-4">
                            {Object.entries(groupedEntries).map(([date, entries]) => (
                                <div key={date} className="bg-white border border-border rounded-card overflow-hidden shadow-sm">
                                    <div className="bg-surface px-4 py-2 border-b border-border text-[11px] font-bold text-muted uppercase tracking-wider">
                                        {date}
                                    </div>
                                    <div className="divide-y divide-border">
                                        {entries.map((entry) => {
                                            const isMasuk = entry.direction === 'masuk';
                                            return (
                                                <div key={entry.id} className="p-4 flex items-center justify-between gap-3">
                                                    <div className="flex items-center gap-3">
                                                        <div className={`w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0 ${
                                                            isMasuk ? 'bg-green-50 text-green-600' : 'bg-surface text-muted'
                                                        }`}>
                                                            {isMasuk ? (
                                                                <ArrowDownLeft className="w-4 h-4 stroke-[2.5]" />
                                                            ) : (
                                                                <ArrowUpRight className="w-4 h-4 stroke-[2.5]" />
                                                            )}
                                                        </div>
                                                        <div>
                                                            <h4 className="text-xs font-bold text-text">{entry.description}</h4>
                                                            <p className="text-[11px] text-muted">
                                                                {entry.plan_name} • {entry.created_time}
                                                            </p>
                                                        </div>
                                                    </div>

                                                    <div className="text-right">
                                                        <div className={`text-xs font-bold tabular-nums ${
                                                            isMasuk ? 'text-green-600' : 'text-text'
                                                        }`}>
                                                            {isMasuk ? '+' : '-'}{formatRupiah(entry.amount)}
                                                        </div>
                                                        <div className="text-[10px] text-muted tabular-nums">
                                                            Saldo: {formatRupiah(entry.balance_after)}
                                                        </div>
                                                    </div>
                                                </div>
                                            );
                                        })}
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </PenabungLayout>
    );
}
