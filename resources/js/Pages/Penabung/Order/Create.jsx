import React, { useState, useMemo } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import PenabungLayout from '@/Layouts/PenabungLayout';
import { formatRupiah, getAnimalImageUrl } from '@/Utils/format';
import { ArrowLeft, CheckCircle2, ShieldCheck, AlertCircle, ShoppingBag, MapPin, Wallet, Calendar } from 'lucide-react';

export default function OrderCreate({
    animal,
    savingsPlans = [],
    regions = [],
    isSembelihOpen = false,
    isKirimOpen = false,
    feeRule = null,
}) {
    const { data, setData, post, processing, errors } = useForm({
        savings_plan_id: savingsPlans[0]?.id || '',
        region_id: regions[0]?.id || '',
        method: isSembelihOpen ? 'sembelih_tim' : (isKirimOpen ? 'kirim_hidup' : ''),
    });

    const selectedPlan = useMemo(() => {
        return savingsPlans.find((p) => String(p.id) === String(data.savings_plan_id));
    }, [savingsPlans, data.savings_plan_id]);

    const selectedRegion = useMemo(() => {
        return regions.find((r) => String(r.id) === String(data.region_id));
    }, [regions, data.region_id]);

    const zone = selectedRegion?.service_zone;

    // Hitung rincian biaya dinamis
    const costBreakdown = useMemo(() => {
        const animalPrice = animal.price;
        let feeSlaughter = 0;
        let feeDistribution = 0;
        let feeDelivery = 0;
        let isMethodSupported = true;
        let methodError = null;

        if (data.method === 'sembelih_tim') {
            if (!isSembelihOpen) {
                isMethodSupported = false;
                methodError = 'Jendela pemesanan Sembelih Tim saat ini sedang ditutup.';
            } else if (zone && !zone.supports_sembelih_tim) {
                isMethodSupported = false;
                methodError = 'Wilayah ini tidak mendukung layanan Sembelih Tim.';
            } else {
                feeSlaughter = feeRule ? feeRule.fee_slaughter : 0;
                feeDistribution = feeRule ? feeRule.fee_distribution : 0;
            }
        } else if (data.method === 'kirim_hidup') {
            if (!isKirimOpen) {
                isMethodSupported = false;
                methodError = 'Jendela pemesanan Kirim Hidup saat ini sedang ditutup.';
            } else if (zone && !zone.supports_kirim_hidup) {
                isMethodSupported = false;
                methodError = 'Wilayah ini tidak mendukung layanan Kirim Hewan Hidup.';
            } else {
                feeDelivery = zone ? zone.delivery_fee : 0;
            }
        }

        const total = animalPrice + feeSlaughter + feeDistribution + feeDelivery;
        const currentBalance = selectedPlan ? (selectedPlan.balance_cached || 0) : 0;
        const isBalanceEnough = currentBalance >= total;

        return {
            animalPrice,
            feeSlaughter,
            feeDistribution,
            feeDelivery,
            total,
            currentBalance,
            isBalanceEnough,
            isMethodSupported,
            methodError,
        };
    }, [animal, data.method, zone, feeRule, selectedPlan, isSembelihOpen, isKirimOpen]);

    const handleSubmit = (e) => {
        e.preventDefault();
        post(`/beli/${animal.id}/reservasi`);
    };

    return (
        <PenabungLayout>
            <Head title={`Pesan ${animal.title} - YuKurban`} />

            <div className="max-w-3xl mx-auto space-y-6">
                {/* Back button */}
                <div className="border-b border-border pb-3">
                    <Link
                        href={`/katalog/${animal.id}`}
                        className="inline-flex items-center gap-1.5 text-xs font-semibold text-green-600 hover:text-green-700"
                    >
                        <ArrowLeft className="w-4 h-4" />
                        <span>Kembali ke Detail Hewan</span>
                    </Link>
                </div>

                {/* Header */}
                <div>
                    <h1 className="text-xl sm:text-2xl font-extrabold text-green-900">
                        Formulir Pemesanan Kurban
                    </h1>
                    <p className="text-xs text-muted mt-1">
                        Pilih rencana tabungan, wilayah tujuan, dan metode pelaksanaan kurban Anda.
                    </p>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    {/* Ringkasan Hewan Pilihan */}
                    <div className="bg-white border border-border rounded-card p-5 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div className="flex items-center gap-4">
                            <div className="w-16 h-16 rounded-btn bg-surface border border-border overflow-hidden flex-shrink-0 flex items-center justify-center">
                                <img
                                    src={getAnimalImageUrl(animal.media?.[0]?.path, animal.type)}
                                    alt={animal.title}
                                    className="w-full h-full object-cover"
                                    onError={(e) => {
                                        const fallback = animal.type === 'sapi' ? '/animals/sapi-01.jpg' : animal.type === 'kambing' ? '/animals/kambing-01.jpg' : '/animals/domba-01.jpg';
                                        if (!e.currentTarget.src.endsWith(fallback)) {
                                            e.currentTarget.src = fallback;
                                        }
                                    }}
                                />
                            </div>
                            <div>
                                <span className="px-2 py-0.5 rounded text-[11px] font-bold bg-green-50 text-green-700 capitalize">
                                    {animal.type_label}
                                </span>
                                <h3 className="text-sm font-bold text-green-900 mt-1">{animal.title}</h3>
                                <p className="text-xs text-muted">
                                    {animal.age_months} Bulan • {animal.weight_kg} kg • Kandang: {animal.farm_name}
                                </p>
                            </div>
                        </div>

                        <div className="text-left sm:text-right">
                            <span className="text-xs text-muted block">Harga Hewan</span>
                            <span className="text-lg font-bold text-green-900 tabular-nums">
                                {formatRupiah(animal.price)}
                            </span>
                        </div>
                    </div>

                    {/* Step 1: Pilih Rencana Tabungan */}
                    <div className="bg-white border border-border rounded-card p-5 shadow-sm space-y-3">
                        <label className="text-xs font-bold text-green-900 flex items-center gap-2">
                            <Wallet className="w-4 h-4 text-green-600" />
                            <span>1. Pilih Rekening Tabungan Sumber Dana</span>
                        </label>

                        {savingsPlans.length === 0 ? (
                            <div className="p-4 bg-amber-50 border border-amber-200 rounded-btn text-xs text-amber-800">
                                Anda belum memiliki rekening tabungan yang aktif. Silakan buka tabungan kurban terlebih dahulu di menu Rencana.
                            </div>
                        ) : (
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                {savingsPlans.map((plan) => (
                                    <label
                                        key={plan.id}
                                        className={`p-3.5 rounded-btn border cursor-pointer transition-all block ${
                                            String(data.savings_plan_id) === String(plan.id)
                                                ? 'border-green-600 bg-green-50/50 shadow-sm ring-1 ring-green-600'
                                                : 'border-border bg-white hover:bg-surface'
                                        }`}
                                    >
                                        <div className="flex items-center justify-between">
                                            <span className="font-bold text-xs text-green-900">{plan.name}</span>
                                            <input
                                                type="radio"
                                                name="savings_plan_id"
                                                value={plan.id}
                                                checked={String(data.savings_plan_id) === String(plan.id)}
                                                onChange={(e) => setData('savings_plan_id', e.target.value)}
                                                className="text-green-600 focus:ring-green-500"
                                            />
                                        </div>
                                        <div className="mt-2 text-xs text-muted flex justify-between items-center">
                                            <span>Saldo Tersedia:</span>
                                            <span className="font-bold text-green-700 tabular-nums">
                                                {formatRupiah(plan.balance_cached || 0)}
                                            </span>
                                        </div>
                                    </label>
                                ))}
                            </div>
                        )}
                        {errors.savings_plan_id && (
                            <p className="text-xs text-red-600 mt-1">{errors.savings_plan_id}</p>
                        )}
                    </div>

                    {/* Step 2: Pilih Wilayah Tujuan */}
                    <div className="bg-white border border-border rounded-card p-5 shadow-sm space-y-3">
                        <label className="text-xs font-bold text-green-900 flex items-center gap-2">
                            <MapPin className="w-4 h-4 text-green-600" />
                            <span>2. Pilih Wilayah Tujuan Pelaksanaan / Pengiriman</span>
                        </label>

                        <select
                            value={data.region_id}
                            onChange={(e) => setData('region_id', e.target.value)}
                            className="w-full text-xs font-medium rounded-btn border border-border bg-surface px-3 py-2.5 text-text focus:outline-none focus:ring-2 focus:ring-green-600"
                        >
                            {regions.map((reg) => (
                                <option key={reg.id} value={reg.id}>
                                    {reg.city}, {reg.province}
                                </option>
                            ))}
                        </select>
                        {errors.region_id && (
                            <p className="text-xs text-red-600 mt-1">{errors.region_id}</p>
                        )}
                    </div>

                    {/* Step 3: Pilih Metode Kurban */}
                    <div className="bg-white border border-border rounded-card p-5 shadow-sm space-y-3">
                        <label className="text-xs font-bold text-green-900 flex items-center gap-2">
                            <Calendar className="w-4 h-4 text-green-600" />
                            <span>3. Pilih Metode Pelaksanaan Kurban</span>
                        </label>

                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            {/* Option A: Sembelih Tim */}
                            <label
                                className={`p-4 rounded-btn border cursor-pointer transition-all flex flex-col justify-between ${
                                    data.method === 'sembelih_tim'
                                        ? 'border-green-600 bg-green-50/50 shadow-sm ring-1 ring-green-600'
                                        : 'border-border bg-white hover:bg-surface'
                                } ${!isSembelihOpen || (zone && !zone.supports_sembelih_tim) ? 'opacity-50 cursor-not-allowed' : ''}`}
                            >
                                <div className="space-y-2">
                                    <div className="flex items-center justify-between">
                                        <span className="font-bold text-xs text-green-900">
                                            Sembelih Tim (Daging Disalurkan)
                                        </span>
                                        <input
                                            type="radio"
                                            name="method"
                                            value="sembelih_tim"
                                            checked={data.method === 'sembelih_tim'}
                                            disabled={!isSembelihOpen || (zone && !zone.supports_sembelih_tim)}
                                            onChange={(e) => setData('method', e.target.value)}
                                            className="text-green-600 focus:ring-green-500"
                                        />
                                    </div>
                                    <p className="text-[11px] text-muted leading-relaxed">
                                        Tim platform menyembelih secara syar'i atas nama Anda, mendokumentasikan, dan menyalurkan daging ke warga yang berhak.
                                    </p>
                                </div>
                                <div className="mt-3 pt-2 border-t border-border/60 text-[11px] text-green-700 font-semibold">
                                    Ujrah Sembelih & Distribusi: {formatRupiah((feeRule?.fee_slaughter || 0) + (feeRule?.fee_distribution || 0))}
                                </div>
                            </label>

                            {/* Option B: Kirim Hidup */}
                            <label
                                className={`p-4 rounded-btn border cursor-pointer transition-all flex flex-col justify-between ${
                                    data.method === 'kirim_hidup'
                                        ? 'border-green-600 bg-green-50/50 shadow-sm ring-1 ring-green-600'
                                        : 'border-border bg-white hover:bg-surface'
                                } ${!isKirimOpen || (zone && !zone.supports_kirim_hidup) ? 'opacity-50 cursor-not-allowed' : ''}`}
                            >
                                <div className="space-y-2">
                                    <div className="flex items-center justify-between">
                                        <span className="font-bold text-xs text-green-900">
                                            Kirim Hewan Hidup
                                        </span>
                                        <input
                                            type="radio"
                                            name="method"
                                            value="kirim_hidup"
                                            checked={data.method === 'kirim_hidup'}
                                            disabled={!isKirimOpen || (zone && !zone.supports_kirim_hidup)}
                                            onChange={(e) => setData('method', e.target.value)}
                                            className="text-green-600 focus:ring-green-500"
                                        />
                                    </div>
                                    <p className="text-[11px] text-muted leading-relaxed">
                                        Hewan dikirim hidup menggunakan armada terpercaya langsung ke alamat yang Anda tentukan untuk disembelih mandiri.
                                    </p>
                                </div>
                                <div className="mt-3 pt-2 border-t border-border/60 text-[11px] text-green-700 font-semibold">
                                    Ongkir Wilayah: {formatRupiah(zone?.delivery_fee || 0)}
                                </div>
                            </label>
                        </div>

                        {costBreakdown.methodError && (
                            <div className="p-3 bg-red-50 border border-red-200 rounded-btn text-xs text-red-700 flex items-center gap-2">
                                <AlertCircle className="w-4 h-4 flex-shrink-0" />
                                <span>{costBreakdown.methodError}</span>
                            </div>
                        )}
                        {errors.method && (
                            <p className="text-xs text-red-600 mt-1">{errors.method}</p>
                        )}
                    </div>

                    {/* Rincian Biaya Transparan (BR-09) */}
                    <div className="bg-white border border-border rounded-card p-5 shadow-sm space-y-4">
                        <h3 className="text-xs font-bold text-green-900 uppercase tracking-wider">
                            Rincian Biaya Transparan (BR-09)
                        </h3>

                        <div className="space-y-2.5 text-xs">
                            <div className="flex justify-between items-center text-muted">
                                <span>Harga Hewan Kurban:</span>
                                <span className="font-semibold text-text tabular-nums">{formatRupiah(costBreakdown.animalPrice)}</span>
                            </div>

                            {data.method === 'sembelih_tim' && (
                                <>
                                    <div className="flex justify-between items-center text-muted">
                                        <span>Ujrah Penyembelihan:</span>
                                        <span className="font-semibold text-text tabular-nums">{formatRupiah(costBreakdown.feeSlaughter)}</span>
                                    </div>
                                    <div className="flex justify-between items-center text-muted">
                                        <span>Ujrah Penyaluran Daging:</span>
                                        <span className="font-semibold text-text tabular-nums">{formatRupiah(costBreakdown.feeDistribution)}</span>
                                    </div>
                                </>
                            )}

                            {data.method === 'kirim_hidup' && (
                                <div className="flex justify-between items-center text-muted">
                                    <span>Ongkos Kirim Pengiriman Wilayah:</span>
                                    <span className="font-semibold text-text tabular-nums">{formatRupiah(costBreakdown.feeDelivery)}</span>
                                </div>
                            )}

                            <div className="pt-3 border-t border-border flex justify-between items-center text-sm">
                                <span className="font-bold text-green-900">Total Yang Didebet:</span>
                                <span className="text-xl font-black text-green-900 tabular-nums">
                                    {formatRupiah(costBreakdown.total)}
                                </span>
                            </div>

                            <div className="flex justify-between items-center text-xs pt-1">
                                <span className="text-muted">Saldo Tabungan Terpilih:</span>
                                <span className={`font-bold tabular-nums ${costBreakdown.isBalanceEnough ? 'text-green-700' : 'text-red-600'}`}>
                                    {formatRupiah(costBreakdown.currentBalance)}
                                </span>
                            </div>
                        </div>

                        {!costBreakdown.isBalanceEnough && (
                            <div className="p-3 bg-red-50 border border-red-200 rounded-btn text-xs text-red-700 flex items-center gap-2">
                                <AlertCircle className="w-4 h-4 flex-shrink-0" />
                                <span>
                                    Saldo tabungan Anda kurang sebesar <strong>{formatRupiah(costBreakdown.total - costBreakdown.currentBalance)}</strong>. Silakan setor saldo terlebih dahulu.
                                </span>
                            </div>
                        )}
                    </div>

                    {/* Submit Button (Reservasi 24 Jam - BR-06) */}
                    <div className="pt-2">
                        <button
                            type="submit"
                            disabled={processing || !costBreakdown.isBalanceEnough || !costBreakdown.isMethodSupported || !data.method}
                            className="w-full py-3.5 px-4 rounded-btn bg-green-600 text-white font-bold text-sm hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed shadow-md transition-colors flex items-center justify-center gap-2"
                        >
                            <ShoppingBag className="w-4 h-4" />
                            <span>
                                {processing ? 'Memproses Reservasi...' : 'Reservasi Hewan Ini (24 Jam)'}
                            </span>
                        </button>
                        <p className="text-[11px] text-muted text-center mt-2">
                            *Hewan akan dikunci untuk Anda selama 24 jam. Saldo Anda baru akan didebet pada langkah konfirmasi berikutnya.
                        </p>
                    </div>
                </form>
            </div>
        </PenabungLayout>
    );
}
