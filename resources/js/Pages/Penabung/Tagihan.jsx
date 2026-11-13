import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import PenabungLayout from '@/Layouts/PenabungLayout';
import { formatRupiah } from '@/Utils/format';
import { Copy, Check, Clock, CheckCircle2, XCircle, AlertCircle, ArrowLeft, ArrowRight, ShieldCheck } from 'lucide-react';

export default function Tagihan({ invoice }) {
    const [copied, setCopied] = useState(false);
    const [isSimulating, setIsSimulating] = useState(false);

    const handleCopyVa = () => {
        navigator.clipboard.writeText(invoice.va_code);
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
    };

    const handleSimulateSuccess = () => {
        setIsSimulating(true);
        router.post(`/tagihan/${invoice.id}/simulasi-berhasil`, {}, {
            onFinish: () => setIsSimulating(false),
        });
    };

    const handleSimulateFail = () => {
        setIsSimulating(true);
        router.post(`/tagihan/${invoice.id}/simulasi-gagal`, {}, {
            onFinish: () => setIsSimulating(false),
        });
    };

    const isPending = invoice.status === 'menunggu';
    const isSuccess = invoice.status === 'berhasil';
    const isFailed = invoice.status === 'gagal';
    const isExpired = invoice.status === 'kedaluwarsa' || invoice.is_expired;

    return (
        <PenabungLayout>
            <Head title={`Tagihan Pembayaran #${invoice.va_code} - YuKurban`} />

            <div className="max-w-xl mx-auto space-y-6">
                <div className="border-b border-border pb-3">
                    <Link
                        href={`/rencana/${invoice.plan.id}`}
                        className="inline-flex items-center gap-1 text-xs font-semibold text-green-600 hover:text-green-700 mb-1"
                    >
                        <ArrowLeft className="w-3.5 h-3.5" />
                        <span>Kembali ke Rencana {invoice.plan.name}</span>
                    </Link>
                    <h1 className="text-xl sm:text-2xl font-bold text-green-900 mt-1">Tagihan Virtual Account</h1>
                    <p className="text-xs text-muted">ID Transaksi Simulasi: #{invoice.va_code}</p>
                </div>

                {/* Banner Mode Simulasi (PRD BR-17) */}
                <div className="p-3 bg-gold-100 border border-gold-300 rounded-btn text-gold-600 text-xs font-semibold flex items-center gap-2">
                    <AlertCircle className="w-4 h-4 flex-shrink-0" />
                    <span>MODE SIMULASI — Pembayaran ini adalah simulasi, tidak memotong uang sungguhan.</span>
                </div>

                {/* Main Invoice Card */}
                <div className="bg-white border border-border rounded-card p-6 sm:p-8 shadow-sm space-y-6">
                    {/* Status Header */}
                    <div className="text-center pb-4 border-b border-border">
                        {isSuccess && (
                            <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-green-50 text-green-700 text-xs font-bold mb-2">
                                <CheckCircle2 className="w-4 h-4 text-green-600" />
                                <span>Pembayaran Berhasil Diterima</span>
                            </div>
                        )}
                        {isPending && (
                            <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-gold-100 text-gold-600 text-xs font-bold mb-2">
                                <Clock className="w-4 h-4" />
                                <span>Menunggu Pembayaran Simulasi</span>
                            </div>
                        )}
                        {isFailed && (
                            <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-danger/10 text-danger text-xs font-bold mb-2">
                                <XCircle className="w-4 h-4" />
                                <span>Pembayaran Gagal / Dibatalkan</span>
                            </div>
                        )}
                        {isExpired && !isFailed && !isSuccess && (
                            <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-muted/10 text-muted text-xs font-bold mb-2">
                                <AlertCircle className="w-4 h-4" />
                                <span>Tagihan Kedaluwarsa (24 Jam)</span>
                            </div>
                        )}

                        <span className="text-xs text-muted block mt-1">Total Tagihan Setoran</span>
                        <div className="text-3xl font-extrabold text-green-900 tabular-nums mt-1">
                            {formatRupiah(invoice.amount)}
                        </div>
                    </div>

                    {/* VA Box with Copy Button */}
                    <div className="bg-surface p-4 rounded-card border border-border space-y-2">
                        <span className="text-[11px] font-bold text-muted uppercase tracking-wider block">
                            Nomor Virtual Account (Simulasi Bank YuKurban)
                        </span>
                        <div className="flex items-center justify-between gap-3">
                            <span className="text-lg sm:text-xl font-mono font-bold tracking-widest text-text">
                                {invoice.va_code}
                            </span>
                            <button
                                onClick={handleCopyVa}
                                className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-btn bg-white border border-border text-xs font-semibold text-text hover:bg-green-50 hover:text-green-700 transition-colors shadow-sm"
                            >
                                {copied ? <Check className="w-4 h-4 text-green-600" /> : <Copy className="w-4 h-4" />}
                                <span>{copied ? 'Tersalin' : 'Salin VA'}</span>
                            </button>
                        </div>
                    </div>

                    {/* Details Table */}
                    <div className="space-y-2.5 text-xs">
                        <div className="flex items-center justify-between py-1.5 border-b border-border/60">
                            <span className="text-muted">Tujuan Rencana</span>
                            <span className="font-bold text-text">{invoice.plan.name}</span>
                        </div>
                        <div className="flex items-center justify-between py-1.5 border-b border-border/60">
                            <span className="text-muted">Metode Pembayaran</span>
                            <span className="font-medium text-text">Virtual Account (Simulasi)</span>
                        </div>
                        <div className="flex items-center justify-between py-1.5 border-b border-border/60">
                            <span className="text-muted">Batas Waktu Bayar</span>
                            <span className="font-medium text-text">
                                {new Date(invoice.expires_at).toLocaleString('id-ID', {
                                    dateStyle: 'medium',
                                    timeStyle: 'short',
                                })}
                            </span>
                        </div>
                        {invoice.paid_at && (
                            <div className="flex items-center justify-between py-1.5 border-b border-border/60">
                                <span className="text-muted">Waktu Pembayaran</span>
                                <span className="font-bold text-green-700">
                                    {new Date(invoice.paid_at).toLocaleString('id-ID', {
                                        dateStyle: 'medium',
                                        timeStyle: 'short',
                                    })}
                                </span>
                            </div>
                        )}
                    </div>

                    {/* Struk Sukses atau Tombol Simulasi */}
                    {isPending ? (
                        <div className="pt-2 space-y-3">
                            <div className="text-center text-xs text-muted mb-2">
                                Uji alur sistem dengan memilih salah satu skenario simulasi:
                            </div>
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <button
                                    onClick={handleSimulateSuccess}
                                    disabled={isSimulating}
                                    className="w-full py-3 px-4 rounded-btn bg-green-600 text-white font-bold text-xs hover:bg-green-700 shadow-sm transition-colors flex items-center justify-center gap-2"
                                >
                                    <CheckCircle2 className="w-4 h-4" />
                                    <span>Simulasikan Berhasil</span>
                                </button>
                                <button
                                    onClick={handleSimulateFail}
                                    disabled={isSimulating}
                                    className="w-full py-3 px-4 rounded-btn border border-border bg-white text-muted hover:text-danger hover:border-danger font-semibold text-xs transition-colors flex items-center justify-center gap-2"
                                >
                                    <XCircle className="w-4 h-4" />
                                    <span>Simulasikan Gagal</span>
                                </button>
                            </div>
                        </div>
                    ) : isSuccess ? (
                        <div className="pt-2 space-y-3">
                            <div className="p-4 bg-green-50 border border-green-200 rounded-btn text-center text-xs text-green-800 space-y-1">
                                <p className="font-bold">Transaksi Telah Masuk Buku Besar</p>
                                <p className="text-[11px] text-green-900/80">
                                    Satu baris entri ledger berjenis <strong>setoran</strong> telah ditambahkan secara append-only.
                                </p>
                            </div>
                            <Link
                                href={`/rencana/${invoice.plan.id}`}
                                className="w-full py-3 px-4 rounded-btn bg-green-600 text-white font-bold text-xs hover:bg-green-700 shadow-sm transition-colors flex items-center justify-center gap-2"
                            >
                                <span>Kembali ke Detail Rencana Tabungan</span>
                                <ArrowRight className="w-4 h-4" />
                            </Link>
                        </div>
                    ) : (
                        <div className="pt-2">
                            <Link
                                href={`/rencana/${invoice.plan.id}/setor`}
                                className="w-full py-3 px-4 rounded-btn bg-green-600 text-white font-bold text-xs hover:bg-green-700 shadow-sm transition-colors flex items-center justify-center gap-2"
                            >
                                <span>Buat Tagihan Baru</span>
                            </Link>
                        </div>
                    )}
                </div>
            </div>
        </PenabungLayout>
    );
}
