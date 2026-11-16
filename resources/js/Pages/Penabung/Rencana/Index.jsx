import React from 'react';
import { Head, Link } from '@inertiajs/react';
import PenabungLayout from '@/Layouts/PenabungLayout';
import { formatRupiah } from '@/Utils/format';
import { Target, PlusCircle, Sparkles, ChevronRight, CheckCircle2 } from 'lucide-react';

export default function RencanaIndex({ plans = [], totalBalance = 0 }) {
    return (
        <PenabungLayout>
            <Head title="Rencana Tabungan Kurban - YuKurban" />

            <div className="space-y-6 max-w-4xl mx-auto">
                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-border pb-4">
                    <div>
                        <h1 className="text-xl sm:text-2xl font-bold text-green-900">Rencana Tabungan Kurban</h1>
                        <p className="text-xs text-muted mt-0.5">Kelola target tabungan, setor cicilan, atau aktifkan autodebet</p>
                    </div>

                    <Link
                        href="/rencana/buat"
                        className="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-btn bg-green-600 text-white font-semibold text-xs hover:bg-green-700 shadow-sm transition-colors"
                    >
                        <PlusCircle className="w-4 h-4" />
                        <span>Buat Rencana Baru</span>
                    </Link>
                </div>

                {/* Total Balance Summary Header */}
                <div className="bg-white border border-border rounded-card p-5 shadow-sm flex items-center justify-between">
                    <div>
                        <span className="text-xs text-muted font-medium">Akumulasi Saldo Seluruh Rencana</span>
                        <div className="text-xl sm:text-2xl font-extrabold text-green-900 tabular-nums mt-0.5">
                            {formatRupiah(totalBalance)}
                        </div>
                    </div>
                    <span className="px-3 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700">
                        {plans.length} Rencana Terdaftar
                    </span>
                </div>

                {/* Plans List */}
                {plans.length === 0 ? (
                    <div className="bg-white border border-border rounded-card p-8 text-center shadow-sm">
                        <div className="w-12 h-12 rounded-full bg-green-50 text-green-600 flex items-center justify-center mx-auto mb-3">
                            <Target className="w-6 h-6" />
                        </div>
                        <h3 className="text-base font-bold text-text">Belum Memiliki Rencana Tabungan</h3>
                        <p className="text-xs text-muted mt-1 max-w-sm mx-auto">
                            Tentukan target tabungan Anda untuk tahun kurban yang diinginkan tanpa terikat pada hewan tertentu.
                        </p>
                        <Link
                            href="/rencana/buat"
                            className="mt-4 inline-flex items-center gap-2 px-5 py-2.5 rounded-btn bg-green-600 text-white font-semibold text-xs hover:bg-green-700 shadow-sm"
                        >
                            <Sparkles className="w-4 h-4" />
                            <span>Mulai Rencana Sekarang</span>
                        </Link>
                    </div>
                ) : (
                    <div className="space-y-4">
                        {plans.map((plan) => (
                            <Link
                                key={plan.id}
                                href={`/rencana/${plan.id}`}
                                className="block bg-white border border-border rounded-card p-5 hover:border-green-600 shadow-sm transition-all"
                            >
                                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                    <div className="flex items-center gap-3">
                                        <div className="w-10 h-10 rounded-full bg-green-50 text-green-600 flex items-center justify-center font-bold flex-shrink-0">
                                            <Target className="w-5 h-5" />
                                        </div>
                                        <div>
                                            <div className="flex items-center gap-2">
                                                <h3 className="font-bold text-sm text-green-900">{plan.name}</h3>
                                                {plan.tier && (
                                                    <span className="px-2 py-0.5 rounded text-[10px] font-bold bg-gold-100 text-gold-600 capitalize">
                                                        {plan.tier.name}
                                                    </span>
                                                )}
                                                <span className={`px-2 py-0.5 rounded text-[10px] font-semibold ${
                                                    plan.status === 'menabung' ? 'bg-green-100 text-green-700' : 'bg-surface text-muted'
                                                }`}>
                                                    {plan.status === 'menabung' ? 'Aktif Menabung' : plan.status}
                                                </span>
                                            </div>
                                            <p className="text-xs text-muted mt-0.5">
                                                Target Idul Adha <strong>{plan.target_year}</strong> • Cicilan <strong>{formatRupiah(plan.monthly_amount)}/bulan</strong>
                                            </p>
                                        </div>
                                    </div>

                                    <div className="text-left sm:text-right mt-2 sm:mt-0">
                                        <div className="text-base font-extrabold text-green-900 tabular-nums">
                                            {formatRupiah(plan.balance_cached)}
                                        </div>
                                        <div className="text-[11px] text-muted tabular-nums">
                                            dari target {formatRupiah(plan.target_amount)}
                                        </div>
                                    </div>
                                </div>

                                {/* Progress Bar */}
                                <div className="mt-4 pt-3 border-t border-border">
                                    <div className="flex items-center justify-between text-xs mb-1.5">
                                        <span className="text-[11px] text-muted">Progress Pencapaian</span>
                                        <span className="font-bold text-gold-600 tabular-nums text-xs">
                                            {plan.progress_percentage}%
                                        </span>
                                    </div>
                                    <div className="w-full bg-surface rounded-full h-2 overflow-hidden border border-border">
                                        <div
                                            className="bg-gold-500 h-2 rounded-full transition-all duration-500"
                                            style={{ width: `${plan.progress_percentage}%` }}
                                        />
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

