import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import PenabungLayout from '@/Layouts/PenabungLayout';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import { formatRupiah } from '@/Utils/format';
import { ArrowLeft, AlertTriangle, ShieldCheck } from 'lucide-react';

export default function RencanaTarik({ plan }) {
    const { data, setData, post, processing, errors } = useForm({
        amount: Math.min(plan.balance_cached, 500000),
        reason: 'Kebutuhan mendesak',
    });

    const submit = (e) => {
        e.preventDefault();
        post(`/rencana/${plan.id}/tarik`);
    };

    return (
        <PenabungLayout>
            <Head title={`Tarik Dana - ${plan.name}`} />

            <div className="max-w-xl mx-auto space-y-6">
                <div className="border-b border-border pb-4">
                    <Link href={`/rencana/${plan.id}`} className="inline-flex items-center gap-1 text-xs font-semibold text-green-600 hover:text-green-700 mb-1">
                        <ArrowLeft className="w-3.5 h-3.5" />
                        <span>Kembali ke Detail Rencana</span>
                    </Link>
                    <h1 className="text-xl sm:text-2xl font-bold text-green-900 mt-2">Penarikan Dana Tabungan</h1>
                    <p className="text-xs text-muted mt-0.5">
                        Rencana: <strong className="text-text">{plan.name}</strong> • Saldo Tersedia:{' '}
                        <strong className="text-green-700 tabular-nums">{formatRupiah(plan.balance_cached)}</strong>
                    </p>
                </div>

                <div className="bg-white border border-border rounded-card p-6 sm:p-8 shadow-sm">
                    {plan.balance_cached <= 0 ? (
                        <div className="text-center py-6 space-y-3">
                            <AlertTriangle className="w-10 h-10 text-danger mx-auto" />
                            <h3 className="font-bold text-sm text-text">Saldo Tabungan Masih Rp 0</h3>
                            <p className="text-xs text-muted max-w-sm mx-auto">
                                Anda belum memiliki saldo yang dapat ditarik pada rencana tabungan ini.
                            </p>
                            <Link
                                href={`/rencana/${plan.id}/setor`}
                                className="inline-flex items-center gap-2 px-4 py-2 rounded-btn bg-green-600 text-white font-semibold text-xs"
                            >
                                Lakukan Setoran
                            </Link>
                        </div>
                    ) : (
                        <form onSubmit={submit} className="space-y-6">
                            <div>
                                <InputLabel htmlFor="amount" value="Nominal yang Ingin Ditarik (Rp)" className="text-xs font-bold text-text" />
                                <TextInput
                                    id="amount"
                                    type="number"
                                    step="10000"
                                    min="10000"
                                    max={plan.balance_cached}
                                    name="amount"
                                    value={data.amount}
                                    className="mt-1 block w-full tabular-nums text-base font-bold text-text"
                                    onChange={(e) => setData('amount', parseInt(e.target.value, 10) || 0)}
                                    required
                                />
                                <div className="mt-1.5 flex items-center justify-between text-[11px] text-muted">
                                    <span>Maksimal: {formatRupiah(plan.balance_cached)}</span>
                                    <button
                                        type="button"
                                        onClick={() => setData('amount', plan.balance_cached)}
                                        className="text-green-600 font-semibold hover:underline"
                                    >
                                        Tarik Semua Saldo
                                    </button>
                                </div>
                                <InputError message={errors.amount} className="mt-1 text-xs" />
                            </div>

                            <div>
                                <InputLabel htmlFor="reason" value="Alasan Penarikan" className="text-xs font-bold text-text" />
                                <TextInput
                                    id="reason"
                                    name="reason"
                                    value={data.reason}
                                    placeholder="Contoh: Kebutuhan dana darurat keluarga"
                                    className="mt-1 block w-full"
                                    onChange={(e) => setData('reason', e.target.value)}
                                    required
                                />
                                <InputError message={errors.reason} className="mt-1 text-xs" />
                            </div>

                            {/* Amanah & Syariat Notice */}
                            <div className="p-4 bg-green-50 border border-green-100 rounded-btn text-xs text-green-800 leading-relaxed space-y-1.5">
                                <div className="font-bold flex items-center gap-1.5">
                                    <ShieldCheck className="w-4 h-4 text-green-600" />
                                    <span>Hak Penarikan Dana Sepenuhnya</span>
                                </div>
                                <p className="text-[11px] text-green-900/80">
                                    Dana tabungan adalah titipan amanah milik Anda pribadi. Penarikan dana diperbolehkan kapan saja dan langsung dicatat sebagai entri ledger `penarikan` yang sah.
                                </p>
                            </div>

                            <div className="pt-2 flex items-center justify-end gap-3 border-t border-border">
                                <Link
                                    href={`/rencana/${plan.id}`}
                                    className="px-4 py-2.5 rounded-btn text-xs font-semibold text-muted hover:text-text"
                                >
                                    Batal
                                </Link>
                                <PrimaryButton
                                    disabled={processing || data.amount <= 0 || data.amount > plan.balance_cached}
                                    className="bg-danger hover:bg-danger/90 active:bg-danger"
                                >
                                    {processing ? 'Memproses Penarikan...' : 'Konfirmasi Tarik Dana'}
                                </PrimaryButton>
                            </div>
                        </form>
                    )}
                </div>
            </div>
        </PenabungLayout>
    );
}

