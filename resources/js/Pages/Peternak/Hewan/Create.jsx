import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import PeternakLayout from '@/Layouts/PeternakLayout';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import { ArrowLeft, Upload, AlertCircle, ShieldCheck, Film, Image as ImageIcon } from 'lucide-react';

export default function HewanCreate({ regions = [], minAges = {}, defaultRegionId }) {
    const { data, setData, post, processing, errors } = useForm({
        title: '',
        type: 'kambing',
        age_months: minAges['kambing'] || 12,
        weight_kg: 30,
        price: 3200000,
        region_id: defaultRegionId || (regions[0]?.id ?? ''),
        health_notes: 'Hewan dalam kondisi sehat, aktif, nafsu makan baik, mata cerah, tidak cacat fisik, dan bertanduk sempurna.',
        photos: [],
        video: null,
    });

    const handleTypeChange = (newType) => {
        const requiredMinAge = minAges[newType] || 12;
        setData((prev) => ({
            ...prev,
            type: newType,
            age_months: Math.max(prev.age_months, requiredMinAge),
        }));
    };

    const submit = (e) => {
        e.preventDefault();
        post('/peternak/hewan');
    };

    const currentMinAge = minAges[data.type] || 12;

    return (
        <PeternakLayout>
            <Head title="Tambah Hewan Kurban Baru - YuKurban" />

            <div className="max-w-2xl mx-auto space-y-6">
                <div className="border-b border-border pb-4">
                    <Link href="/peternak/hewan" className="inline-flex items-center gap-1 text-xs font-semibold text-green-600 hover:text-green-700 mb-1">
                        <ArrowLeft className="w-3.5 h-3.5" />
                        <span>Kembali ke Daftar Hewan</span>
                    </Link>
                    <h1 className="text-xl sm:text-2xl font-bold text-green-900 mt-2">Daftarkan Hewan Kurban</h1>
                    <p className="text-xs text-muted mt-0.5">
                        Pastikan data fisik dan media memenuhi standar syariat dan kualitas kurasi YuKurban (BR-16).
                    </p>
                </div>

                <div className="bg-white border border-border rounded-card p-6 sm:p-8 shadow-sm">
                    <form onSubmit={submit} className="space-y-5">
                        {/* Judul Hewan */}
                        <div>
                            <InputLabel htmlFor="title" value="Nama / Kode Hewan" className="text-xs font-bold text-text" />
                            <TextInput
                                id="title"
                                name="title"
                                value={data.title}
                                placeholder="Contoh: Domba Garut Tanduk Mewah Super A"
                                className="mt-1 block w-full"
                                onChange={(e) => setData('title', e.target.value)}
                                required
                            />
                            <InputError message={errors.title} className="mt-1 text-xs" />
                        </div>

                        {/* Jenis Hewan & Wilayah */}
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <InputLabel htmlFor="type" value="Jenis Hewan Kurban" className="text-xs font-bold text-text" />
                                <select
                                    id="type"
                                    name="type"
                                    value={data.type}
                                    onChange={(e) => handleTypeChange(e.target.value)}
                                    className="mt-1 block w-full min-h-[44px] rounded-btn border-border text-sm shadow-sm focus:border-green-600 focus:ring-green-600 capitalize"
                                >
                                    <option value="kambing">Kambing</option>
                                    <option value="domba">Domba</option>
                                    <option value="sapi">Sapi</option>
                                </select>
                                <InputError message={errors.type} className="mt-1 text-xs" />
                            </div>

                            <div>
                                <InputLabel htmlFor="region_id" value="Wilayah Asal Ternak" className="text-xs font-bold text-text" />
                                <select
                                    id="region_id"
                                    name="region_id"
                                    value={data.region_id}
                                    onChange={(e) => setData('region_id', e.target.value)}
                                    className="mt-1 block w-full min-h-[44px] rounded-btn border-border text-sm shadow-sm focus:border-green-600 focus:ring-green-600"
                                >
                                    {regions.map((reg) => (
                                        <option key={reg.id} value={reg.id}>
                                            {reg.city}, {reg.province}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors.region_id} className="mt-1 text-xs" />
                            </div>
                        </div>

                        {/* Umur & Bobot */}
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <InputLabel htmlFor="age_months" value="Umur Hewan (Bulan)" className="text-xs font-bold text-text" />
                                <TextInput
                                    id="age_months"
                                    type="number"
                                    min={currentMinAge}
                                    name="age_months"
                                    value={data.age_months}
                                    className="mt-1 block w-full tabular-nums"
                                    onChange={(e) => setData('age_months', parseInt(e.target.value, 10) || 0)}
                                    required
                                />
                                <p className="text-[11px] text-green-700 mt-1 flex items-center gap-1 font-medium">
                                    <ShieldCheck className="w-3.5 h-3.5" />
                                    <span>Batas resmi syariat: minimal {currentMinAge} bulan.</span>
                                </p>
                                <InputError message={errors.age_months} className="mt-1 text-xs" />
                            </div>

                            <div>
                                <InputLabel htmlFor="weight_kg" value="Bobot Hidup Riil (kg)" className="text-xs font-bold text-text" />
                                <TextInput
                                    id="weight_kg"
                                    type="number"
                                    step="0.1"
                                    min="10"
                                    name="weight_kg"
                                    value={data.weight_kg}
                                    className="mt-1 block w-full tabular-nums"
                                    onChange={(e) => setData('weight_kg', parseFloat(e.target.value) || 0)}
                                    required
                                />
                                <InputError message={errors.weight_kg} className="mt-1 text-xs" />
                            </div>
                        </div>

                        {/* Harga Hewan */}
                        <div>
                            <InputLabel htmlFor="price" value="Harga Hewan Kurban (Rp)" className="text-xs font-bold text-text" />
                            <TextInput
                                id="price"
                                type="number"
                                step="50000"
                                min="1000000"
                                name="price"
                                value={data.price}
                                className="mt-1 block w-full tabular-nums text-base font-bold text-green-900"
                                onChange={(e) => setData('price', parseInt(e.target.value, 10) || 0)}
                                required
                            />
                            <p className="text-[11px] text-muted mt-1">Harga kotor sebelum dipotong komisi platform saat payout.</p>
                            <InputError message={errors.price} className="mt-1 text-xs" />
                        </div>

                        {/* Catatan Kesehatan */}
                        <div>
                            <InputLabel htmlFor="health_notes" value="Catatan Kesehatan & Kondisi Fisik" className="text-xs font-bold text-text" />
                            <textarea
                                id="health_notes"
                                name="health_notes"
                                rows={3}
                                value={data.health_notes}
                                onChange={(e) => setData('health_notes', e.target.value)}
                                className="mt-1 block w-full rounded-btn border-border text-sm shadow-sm focus:border-green-600 focus:ring-green-600"
                                required
                            />
                            <InputError message={errors.health_notes} className="mt-1 text-xs" />
                        </div>

                        {/* Foto & Video Upload (BR-16) */}
                        <div className="p-4 bg-surface rounded-card border border-border space-y-4">
                            <div>
                                <div className="flex items-center justify-between">
                                    <InputLabel htmlFor="photos" value="Foto Hewan (Minimal 3 Foto)" className="text-xs font-bold text-green-900 flex items-center gap-1.5" />
                                    <span className="text-[11px] text-gold-600 font-semibold">Wajib BR-16</span>
                                </div>
                                <p className="text-[11px] text-muted mb-2">Unggah dari berbagai sudut: tampak depan/gigi, tampak samping, dan keseluruhan tubuh.</p>
                                <input
                                    id="photos"
                                    type="file"
                                    multiple
                                    accept="image/*"
                                    onChange={(e) => setData('photos', Array.from(e.target.files))}
                                    className="block w-full text-xs text-muted file:mr-4 file:py-2 file:px-4 file:rounded-btn file:border-0 file:text-xs file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100"
                                    required
                                />
                                <InputError message={errors.photos} className="mt-1 text-xs" />
                            </div>

                            <div className="pt-3 border-t border-border">
                                <div className="flex items-center justify-between">
                                    <InputLabel htmlFor="video" value="Video Fisik Hewan (Minimal 1 Video)" className="text-xs font-bold text-green-900 flex items-center gap-1.5" />
                                    <span className="text-[11px] text-gold-600 font-semibold">Wajib BR-16</span>
                                </div>
                                <p className="text-[11px] text-muted mb-2">Video memperlihatkan hewan berjalan aktif dan kondisi kesehatan umum.</p>
                                <input
                                    id="video"
                                    type="file"
                                    accept="video/*"
                                    onChange={(e) => setData('video', e.target.files[0])}
                                    className="block w-full text-xs text-muted file:mr-4 file:py-2 file:px-4 file:rounded-btn file:border-0 file:text-xs file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100"
                                    required
                                />
                                <InputError message={errors.video} className="mt-1 text-xs" />
                            </div>
                        </div>

                        <div className="pt-3 border-t border-border flex items-center justify-end gap-3">
                            <Link
                                href="/peternak/hewan"
                                className="px-4 py-2.5 rounded-btn text-xs font-semibold text-muted hover:text-text"
                            >
                                Batal
                            </Link>
                            <PrimaryButton disabled={processing}>
                                {processing ? 'Mengunggah & Menyimpan...' : 'Simpan & Ajukan Review'}
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </PeternakLayout>
    );
}

