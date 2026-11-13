import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import PenabungLayout from '@/Layouts/PenabungLayout';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import { formatRupiah } from '@/Utils/format';
import { PlusCircle, ArrowLeft, ShieldAlert, Sparkles, CreditCard } from 'lucide-react';

export default function RencanaSetor({ plan }) {
    const quickAmounts = [
        100000,
        250000,
        500000,
        plan.monthly_amount,
    ].filter((amt, idx, arr) => amt > 0 && arr.indexOf(amt) === idx);

    const { data, setData, post, processing, errors } = useForm({
        amount: plan.monthly_amount || 250000,
    });

    const submit = (e) => {
        e.preventDefault();
        post(`/rencana/${plan.id}/setor`);
    };

    return (
        <PenabungLayout>
            <Head title={`Setor Tabungan - ${plan.name}`} />

            <div className="max-w-xl mx-auto space-y-6">
                <div className="border-b border-border pb-4">
                    <Link href={`/rencana/${plan.id}`} className="inline-flex items-center gap-1 text-xs font-semibold text-green-600 hover:text-green-700 mb-1">
                        <ArrowLeft className="w-3.5 h-3.5" />
                        <span>Kembali ke Detail Rencana</span>
                    </Link>
                    <h1 className="text-xl sm:text-2xl font-bold text-green-900 mt-2">Setor Tabungan Kurban</h1>
                    <p className="text-xs text-muted mt-0.5">
                        Untuk rencana: <strong className="text-text">{plan.name}</strong> • Saldo saat ini:{' '}
                        <strong className="text-green-700 tabular-nums">{formatRupiah(plan.balance_cached)}</strong>
                    </p>
                </div>

                <div className="bg-white border border-border rounded-card p-6 sm:p-8 shadow-sm">
                    <form onSubmit={submit} className="space-y-6">
                        {/* Pilihan Nominal Cepat */}
                        <div>
                            <InputLabel value="Pilihan Nominal Cepat" className="text-xs font-bold text-text mb-2" />
                            <div className="grid grid-cols-2 gap-2.5">
                                {quickAmounts.map((amt) => {
                                    const isSelected = data.amount === amt;
                                    return (
                                        <button
                                            key={amt}
                                            type="button"
                                            onClick={() => setData('amount', amt)}
                                            className={`p-3 rounded-btn border text-left font-bold text-xs sm:text-sm tabular-nums transition-all ${
                                                isSelected
                                                    ? 'border-green-600 bg-green-50 text-green-700 ring-1 ring-green-600 shadow-sm'
                                                    : 'border-border bg-surface text-text hover:bg-white'
                                            }`}
                                        >
                                            {formatRupiah(amt)}
                                            {amt === plan.monthly_amount && (
                                                <span className="block text-[10px] font-normal text-muted mt-0.5">
                                                    1x Cicilan Bulanan
                                                </span>
                                            )}
                                        </button>
                                    );
                                })}
                            </div>
                        </div>

                        {/* Input Nominal Bebas */}
                        <div>
                            <InputLabel htmlFor="amount" value="Atau Masukkan Nominal Sendiri (Rp)" className="text-xs font-bold text-text" />
                            <TextInput
                                id="amount"
                                type="number"
                                step="10000"
                                min="10000"
                                name="amount"
                                value={data.amount}
                                className="mt-1 block w-full tabular-nums text-base font-bold text-green-900"
                                onChange={(e) => setData('amount', parseInt(e.target.value, 10) || 0)}
                                required
                            />
                            <p className="text-[11px] text-muted mt-1">Minimal setoran Rp 10.000.</p>
                            <InputError message={errors.amount} className="mt-1 text-xs" />
                        </div>

                        {/* Simulation Info Notice */}
                        <div className="p-4 bg-gold-100/70 border border-gold-300 rounded-btn text-xs text-gold-600 leading-relaxed flex items-start gap-2.5">
                            <CreditCard className="w-5 h-5 flex-shrink-0 mt-0.5" />
                            <div>
                                <strong>Simulasi Virtual Account:</strong> Sistem akan menerbitkan kode pembayaran VA simulasi 16 digit yang berlaku selama 24 jam. Anda dapat mensimulasikan pembayaran berhasil di langkah berikutnya.
                            </div>
                        </div>

                        <div className="pt-2 flex items-center justify-end gap-3 border-t border-border">
                            <Link
                                href={`/rencana/${plan.id}`}
                                className="px-4 py-2.5 rounded-btn text-xs font-semibold text-muted hover:text-text"
                            >
                                Batal
                            </Link>
                            <PrimaryButton disabled={processing}>
                                {processing ? 'Membuat Tagihan...' : 'Buat Tagihan VA'}
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </PenabungLayout>
    );
}
