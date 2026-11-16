import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import PenabungLayout from '@/Layouts/PenabungLayout';
import { formatRupiah } from '@/Utils/format';
import { Target, PlusCircle, ArrowDownLeft, ArrowUpRight, ArrowLeft, Clock, AlertCircle, ShoppingBag, Settings2, CheckCircle2 } from 'lucide-react';

export default function RencanaShow({ plan, ledgerEntries = [], pendingInvoices = [] }) {
    const [showAutodebetModal, setShowAutodebetModal] = useState(false);

    const autodebetForm = useForm({
        autodebet_enabled: plan.autodebet_enabled,
        autodebet_day: plan.autodebet_day || 5,
    });

    const handleAutodebetSubmit = (e) => {
        e.preventDefault();
        autodebetForm.post(`/rencana/${plan.id}/autodebet`, {
            onSuccess: () => setShowAutodebetModal(false),
        });
    };

    return (
        <PenabungLayout>
            <Head title={`${plan.name} - Rencana Tabungan`} />

            <div className="max-w-4xl mx-auto space-y-6">
                {/* Back Link & Header */}
                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-border pb-4">
                    <div>
                        <Link href="/rencana" className="inline-flex items-center gap-1 text-xs font-semibold text-green-600 hover:text-green-700 mb-1">
                            <ArrowLeft className="w-3.5 h-3.5" />
                            <span>Kembali ke Semua Rencana</span>
                        </Link>
                        <div className="flex items-center gap-2 mt-1">
                            <h1 className="text-xl sm:text-2xl font-bold text-green-900">{plan.name}</h1>
                            {plan.tier && (
                                <span className="px-2 py-0.5 rounded text-[11px] font-bold bg-gold-100 text-gold-600 capitalize">
                                    {plan.tier.name}
                                </span>
                            )}
                        </div>
                        <p className="text-xs text-muted mt-0.5">
                            Target Kurban Idul Adha <strong>{plan.target_year}</strong> • Status:{' '}
                            <span className="font-semibold text-green-700 capitalize">{plan.status}</span>
                        </p>
                    </div>

                    <div className="flex items-center gap-2">
                        <Link
                            href={`/rencana/${plan.id}/tarik`}
                            className="px-3.5 py-2 rounded-btn border border-border bg-white text-text font-semibold text-xs hover:bg-surface transition-colors"
                        >
                            Tarik Dana
                        </Link>
                        <Link
                            href={`/rencana/${plan.id}/setor`}
                            className="inline-flex items-center gap-1.5 px-4 py-2 rounded-btn bg-green-600 text-white font-semibold text-xs hover:bg-green-700 shadow-sm transition-colors"
                        >
                            <PlusCircle className="w-4 h-4" />
                            <span>Setor Dana</span>
                        </Link>
                    </div>
                </div>

                {/* Pending Invoice Alert */}
                {pendingInvoices.length > 0 && (
                    <div className="bg-gold-100 border border-gold-300 rounded-card p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div className="flex items-center gap-3">
                            <Clock className="w-5 h-5 text-gold-600 flex-shrink-0" />
                            <div>
                                <h4 className="text-xs font-bold text-gold-600">
                                    Ada {pendingInvoices.length} Tagihan Menunggu Pembayaran
                                </h4>
                                <p className="text-xs text-text mt-0.5">
                                    Nominal {formatRupiah(pendingInvoices[0].amount)} • VA: {pendingInvoices[0].va_code}
                                </p>
                            </div>
                        </div>
                        <Link
                            href={`/tagihan/${pendingInvoices[0].id}`}
                            className="inline-flex items-center justify-center px-4 py-2 rounded-btn bg-green-600 text-white font-semibold text-xs hover:bg-green-700 shadow-sm transition-colors"
                        >
                            Buka Tagihan & Bayar
                        </Link>
                    </div>
                )}

                {/* Progress & Balance Card */}
                <div className="bg-white border border-border rounded-card p-6 shadow-sm space-y-4">
                    <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <span className="text-xs text-muted font-medium">Saldo Terkumpul Saat Ini</span>
                            <div className="text-2xl font-extrabold text-green-900 tabular-nums mt-0.5">
                                {formatRupiah(plan.balance_cached)}
                            </div>
                        </div>

                        <div>
                            <span className="text-xs text-muted font-medium">Target Dana Kurban</span>
                            <div className="text-2xl font-extrabold text-text tabular-nums mt-0.5">
                                {formatRupiah(plan.target_amount)}
                            </div>
                        </div>

                        <div>
                            <span className="text-xs text-muted font-medium">Estimasi Cicilan per Bulan</span>
                            <div className="text-2xl font-extrabold text-gold-600 tabular-nums mt-0.5">
                                {formatRupiah(plan.monthly_amount)}
                            </div>
                        </div>
                    </div>

                    {/* Progress Bar Emas */}
                    <div className="pt-2">
                        <div className="flex items-center justify-between text-xs mb-1.5">
                            <span className="font-semibold text-text">Progres Tabungan</span>
                            <span className="font-bold text-gold-600 tabular-nums">
                                {plan.progress_percentage}% Tercapai
                            </span>
                        </div>
                        <div className="w-full bg-surface rounded-full h-3 overflow-hidden border border-border">
                            <div
                                className="bg-gold-500 h-3 rounded-full transition-all duration-500"
                                style={{ width: `${plan.progress_percentage}%` }}
                            />
                        </div>
                        <div className="mt-2 flex items-center justify-between text-[11px] text-muted">
                            <span>Sisa target: {formatRupiah(Math.max(0, plan.target_amount - plan.balance_cached))}</span>
                            {plan.balance_cached >= plan.target_amount ? (
                                <span className="text-green-600 font-bold flex items-center gap-1">
                                    <CheckCircle2 className="w-3.5 h-3.5" />
                                    Target Terpenuhi! Siap Membeli Hewan
                                </span>
                            ) : (
                                <span>Menabung konsisten untuk qurban terbaik</span>
                            )}
                        </div>
                    </div>

                    {/* Autodebet Setting Quick Row */}
                    <div className="pt-4 border-t border-border flex items-center justify-between text-xs">
                        <div className="flex items-center gap-2">
                            <Settings2 className="w-4 h-4 text-muted" />
                            <span className="text-muted">Autodebet Bulanan:</span>
                            <span className="font-semibold text-text">
                                {plan.autodebet_enabled ? `Aktif (Setiap tanggal ${plan.autodebet_day})` : 'Nonaktif'}
                            </span>
                        </div>
                        <button
                            onClick={() => setShowAutodebetModal(!showAutodebetModal)}
                            className="text-xs font-semibold text-green-600 hover:text-green-700 underline"
                        >
                            Ubah Pengaturan
                        </button>
                    </div>

                    {/* Autodebet Inline Settings Box */}
                    {showAutodebetModal && (
                        <form onSubmit={handleAutodebetSubmit} className="mt-3 p-4 bg-surface rounded-btn border border-border space-y-3">
                            <h4 className="text-xs font-bold text-green-900">Pengaturan Autodebet Simulasi</h4>
                            <div className="flex items-center gap-4">
                                <label className="flex items-center gap-2 cursor-pointer text-xs">
                                    <input
                                        type="checkbox"
                                        checked={autodebetForm.data.autodebet_enabled}
                                        onChange={(e) => autodebetForm.setData('autodebet_enabled', e.target.checked)}
                                        className="rounded text-green-600 focus:ring-green-500"
                                    />
                                    <span className="font-medium text-text">Aktifkan Tagihan Bulanan Otomatis</span>
                                </label>

                                {autodebetForm.data.autodebet_enabled && (
                                    <div className="flex items-center gap-2 text-xs">
                                        <span>Tanggal:</span>
                                        <select
                                            value={autodebetForm.data.autodebet_day}
                                            onChange={(e) => autodebetForm.setData('autodebet_day', parseInt(e.target.value, 10))}
                                            className="rounded-btn border-border text-xs py-1 px-2"
                                        >
                                            {Array.from({ length: 28 }, (_, i) => i + 1).map((d) => (
                                                <option key={d} value={d}>
                                                    Tgl {d}
                                                </option>
                                            ))}
                                        </select>
                                    </div>
                                )}
                            </div>

                            <div className="flex items-center justify-end gap-2 pt-2">
                                <button
                                    type="button"
                                    onClick={() => setShowAutodebetModal(false)}
                                    className="px-3 py-1.5 rounded-btn text-xs text-muted hover:text-text"
                                >
                                    Tutup
                                </button>
                                <button
                                    type="submit"
                                    disabled={autodebetForm.processing}
                                    className="px-3 py-1.5 rounded-btn bg-green-600 text-white font-semibold text-xs hover:bg-green-700"
                                >
                                    Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    )}
                </div>

                {/* Ledger Entries (Mutasi Buku Besar Append-Only) */}
                <div className="space-y-3">
                    <div className="flex items-center justify-between">
                        <div>
                            <h2 className="text-sm font-bold text-green-900 uppercase tracking-wide">
                                Mutasi Buku Besar (Ledger Append-Only)
                            </h2>
                            <p className="text-[11px] text-muted">Setiap baris dicatat secara permanen dan tidak dapat dimanipulasi (BR-01)</p>
                        </div>
                    </div>

                    {ledgerEntries.length === 0 ? (
                        <div className="bg-white border border-border rounded-card p-6 text-center shadow-sm">
                            <p className="text-xs text-muted">Belum ada transaksi di rencana tabungan ini.</p>
                            <Link
                                href={`/rencana/${plan.id}/setor`}
                                className="mt-3 inline-flex items-center gap-1.5 text-xs font-bold text-green-600 hover:text-green-700 underline"
                            >
                                <PlusCircle className="w-3.5 h-3.5" />
                                <span>Lakukan Setoran Pertama</span>
                            </Link>
                        </div>
                    ) : (
                        <div className="bg-white border border-border rounded-card overflow-hidden shadow-sm">
                            <div className="overflow-x-auto">
                                <table className="w-full text-left text-xs">
                                    <thead className="bg-surface border-b border-border text-[11px] font-bold text-muted uppercase tracking-wider">
                                        <tr>
                                            <th className="py-3 px-4">Waktu</th>
                                            <th className="py-3 px-4">Tipe Transaksi</th>
                                            <th className="py-3 px-4">Keterangan</th>
                                            <th className="py-3 px-4 text-right">Nominal</th>
                                            <th className="py-3 px-4 text-right">Saldo Sesudah</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-border">
                                        {ledgerEntries.map((entry) => {
                                            const isMasuk = entry.direction === 'masuk';
                                            return (
                                                <tr key={entry.id} className="hover:bg-surface/50 transition-colors">
                                                    <td className="py-3 px-4 text-muted whitespace-nowrap">
                                                        {entry.created_at}
                                                    </td>
                                                    <td className="py-3 px-4 whitespace-nowrap">
                                                        <span className={`inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold ${
                                                            isMasuk ? 'bg-green-50 text-green-700' : 'bg-surface text-text'
                                                        }`}>
                                                            {isMasuk ? (
                                                                <ArrowDownLeft className="w-3 h-3 stroke-[2.5]" />
                                                            ) : (
                                                                <ArrowUpRight className="w-3 h-3 stroke-[2.5]" />
                                                            )}
                                                            <span>{entry.type_label}</span>
                                                        </span>
                                                    </td>
                                                    <td className="py-3 px-4 text-text font-medium">
                                                        {entry.description}
                                                    </td>
                                                    <td className={`py-3 px-4 text-right font-bold tabular-nums whitespace-nowrap ${
                                                        isMasuk ? 'text-green-600' : 'text-text'
                                                    }`}>
                                                        {isMasuk ? '+' : '-'}{formatRupiah(entry.amount)}
                                                    </td>
                                                    <td className="py-3 px-4 text-right font-extrabold tabular-nums text-green-900 whitespace-nowrap">
                                                        {formatRupiah(entry.balance_after)}
                                                    </td>
                                                </tr>
                                            );
                                        })}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </PenabungLayout>
    );
}

