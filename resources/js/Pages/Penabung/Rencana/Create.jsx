import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import PenabungLayout from '@/Layouts/PenabungLayout';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import Checkbox from '@/Components/Checkbox';
import { formatRupiah } from '@/Utils/format';
import { Target, Sparkles, Calendar, HelpCircle, ArrowRight } from 'lucide-react';

export default function RencanaCreate({ tiers = [], defaultYear }) {
    const currentYear = defaultYear || new Date().getFullYear();

    const { data, setData, post, processing, errors } = useForm({
        name: '',
        tier_id: tiers.length > 0 ? tiers[0].id : '',
        target_amount: tiers.length > 0 ? tiers[0].min_price : 3000000,
        target_year: currentYear,
        monthly_amount: 250000,
        autodebet_enabled: false,
        autodebet_day: 5,
    });

    const handleTierSelect = (tier) => {
        setData((prev) => ({
            ...prev,
            tier_id: tier.id,
            target_amount: tier.min_price,
            monthly_amount: Math.ceil(tier.min_price / 12),
        }));
    };

    const handleTargetAmountChange = (val) => {
        const amount = parseInt(val, 10) || 0;
        setData((prev) => ({
            ...prev,
            target_amount: amount,
            monthly_amount: Math.ceil(amount / 12),
        }));
    };

    const submit = (e) => {
        e.preventDefault();
        post('/rencana');
    };

    return (
        <PenabungLayout>
            <Head title="Buat Rencana Tabungan Baru - YuKurban" />

            <div className="max-w-2xl mx-auto space-y-6">
                <div className="border-b border-border pb-4">
                    <Link href="/rencana" className="text-xs font-semibold text-green-600 hover:text-green-700">
                        ← Kembali ke Daftar Rencana
                    </Link>
                    <h1 className="text-xl sm:text-2xl font-bold text-green-900 mt-2">Buat Rencana Tabungan Kurban</h1>
                    <p className="text-xs text-muted mt-0.5">
                        Tentukan target tabungan tanpa terikat pada hewan tertentu. Hewan fisik akan Anda pilih saat jendela pembelian dibuka.
                    </p>
                </div>

                <div className="bg-white border border-border rounded-card p-6 sm:p-8 shadow-sm">
                    <form onSubmit={submit} className="space-y-6">
                        {/* Nama Rencana */}
                        <div>
                            <InputLabel htmlFor="name" value="Nama Rencana Tabungan" className="text-xs font-bold text-text" />
                            <TextInput
                                id="name"
                                name="name"
                                value={data.name}
                                placeholder="Contoh: Kurban Kambing Ayah 2027"
                                className="mt-1 block w-full"
                                onChange={(e) => setData('name', e.target.value)}
                                required
                            />
                            <p className="text-[11px] text-muted mt-1">Beri nama untuk memudahkan membedakan jika Anda memiliki lebih dari satu rencana.</p>
                            <InputError message={errors.name} className="mt-1 text-xs" />
                        </div>

                        {/* Pilihan Tingkat Harga (Tier) */}
                        {tiers.length > 0 && (
                            <div>
                                <InputLabel value="Pilih Tingkat Harga (Estimasi)" className="text-xs font-bold text-text mb-2" />
                                <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    {tiers.map((t) => {
                                        const isSelected = data.tier_id === t.id;
                                        return (
                                            <button
                                                key={t.id}
                                                type="button"
                                                onClick={() => handleTierSelect(t)}
                                                className={`p-3.5 rounded-card border text-left transition-all ${
                                                    isSelected
                                                        ? 'border-green-600 bg-green-50/50 shadow-sm ring-1 ring-green-600'
                                                        : 'border-border bg-surface hover:bg-white'
                                                }`}
                                            >
                                                <div className="flex items-center justify-between">
                                                    <span className="font-bold text-xs uppercase tracking-wider text-green-900">
                                                        {t.name}
                                                    </span>
                                                    {isSelected && (
                                                        <span className="w-2 h-2 rounded-full bg-green-600" />
                                                    )}
                                                </div>
                                                <div className="mt-2 text-xs font-extrabold text-gold-600 tabular-nums">
                                                    {formatRupiah(t.min_price)}
                                                </div>
                                                <div className="text-[10px] text-muted tabular-nums">
                                                    s/d {formatRupiah(t.max_price)}
                                                </div>
                                            </button>
                                        );
                                    })}
                                </div>
                                <InputError message={errors.tier_id} className="mt-1 text-xs" />
                            </div>
                        )}

                        {/* Target Nominal & Tahun */}
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <InputLabel htmlFor="target_amount" value="Target Nominal (Rp)" className="text-xs font-bold text-text" />
                                <TextInput
                                    id="target_amount"
                                    type="number"
                                    step="50000"
                                    min="500000"
                                    name="target_amount"
                                    value={data.target_amount}
                                    className="mt-1 block w-full tabular-nums"
                                    onChange={(e) => handleTargetAmountChange(e.target.value)}
                                    required
                                />
                                <InputError message={errors.target_amount} className="mt-1 text-xs" />
                            </div>

                            <div>
                                <InputLabel htmlFor="target_year" value="Tahun Target Kurban" className="text-xs font-bold text-text" />
                                <select
                                    id="target_year"
                                    name="target_year"
                                    value={data.target_year}
                                    onChange={(e) => setData('target_year', parseInt(e.target.value, 10))}
                                    className="mt-1 block w-full min-h-[44px] rounded-btn border-border text-sm shadow-sm focus:border-green-600 focus:ring-green-600"
                                >
                                    {[currentYear, currentYear + 1, currentYear + 2, currentYear + 3].map((yr) => (
                                        <option key={yr} value={yr}>
                                            Idul Adha {yr}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors.target_year} className="mt-1 text-xs" />
                            </div>
                        </div>

                        {/* Cicilan per bulan */}
                        <div>
                            <InputLabel htmlFor="monthly_amount" value="Nominal Cicilan per Bulan (Rp)" className="text-xs font-bold text-text" />
                            <TextInput
                                id="monthly_amount"
                                type="number"
                                step="10000"
                                min="10000"
                                name="monthly_amount"
                                value={data.monthly_amount}
                                className="mt-1 block w-full tabular-nums"
                                onChange={(e) => setData('monthly_amount', parseInt(e.target.value, 10) || 0)}
                                required
                            />
                            <p className="text-[11px] text-muted mt-1">
                                Rekomendasi cicilan: <strong>{formatRupiah(data.monthly_amount)}</strong>/bulan untuk mencapai target.
                            </p>
                            <InputError message={errors.monthly_amount} className="mt-1 text-xs" />
                        </div>

                        {/* Autodebet Simulasi */}
                        <div className="p-4 bg-surface rounded-card border border-border space-y-3">
                            <label className="flex items-start gap-3 cursor-pointer">
                                <Checkbox
                                    name="autodebet_enabled"
                                    checked={data.autodebet_enabled}
                                    onChange={(e) => setData('autodebet_enabled', e.target.checked)}
                                    className="rounded text-green-600 focus:ring-green-500 mt-0.5"
                                />
                                <div>
                                    <span className="text-xs font-bold text-green-900 block">
                                        Aktifkan Autodebet Simulasi Otomatis
                                    </span>
                                    <span className="text-[11px] text-muted block leading-relaxed mt-0.5">
                                        Sistem akan membuat tagihan virtual account otomatis setiap bulan pada tanggal yang ditentukan.
                                    </span>
                                </div>
                            </label>

                            {data.autodebet_enabled && (
                                <div className="pt-2 pl-7 flex items-center gap-3">
                                    <label htmlFor="autodebet_day" className="text-xs text-text font-medium">
                                        Jatuh tempo setiap tanggal:
                                    </label>
                                    <select
                                        id="autodebet_day"
                                        value={data.autodebet_day}
                                        onChange={(e) => setData('autodebet_day', parseInt(e.target.value, 10))}
                                        className="rounded-btn border-border text-xs py-1.5 focus:border-green-600 focus:ring-green-600"
                                    >
                                        {Array.from({ length: 28 }, (_, i) => i + 1).map((d) => (
                                            <option key={d} value={d}>
                                                Tanggal {d}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                            )}
                        </div>

                        <div className="pt-4 flex items-center justify-end gap-3 border-t border-border">
                            <Link
                                href="/rencana"
                                className="px-4 py-2.5 rounded-btn text-xs font-semibold text-muted hover:text-text transition-colors"
                            >
                                Batal
                            </Link>
                            <PrimaryButton disabled={processing}>
                                {processing ? 'Menyimpan...' : 'Buat Rencana Tabungan'}
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </PenabungLayout>
    );
}
