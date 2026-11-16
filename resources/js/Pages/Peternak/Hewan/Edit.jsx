import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import PeternakLayout from '@/Layouts/PeternakLayout';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import { ArrowLeft, ShieldCheck } from 'lucide-react';

export default function HewanEdit({ animal, regions = [], minAges = {} }) {
    const { data, setData, put, processing, errors } = useForm({
        title: animal.title,
        type: animal.type,
        age_months: animal.age_months,
        weight_kg: animal.weight_kg,
        price: animal.price,
        region_id: animal.region_id,
        health_notes: animal.health_notes,
    });

    const submit = (e) => {
        e.preventDefault();
        put(`/peternak/hewan/${animal.id}`);
    };

    const currentMinAge = minAges[data.type] || 12;

    return (
        <PeternakLayout>
            <Head title={`Edit Hewan - ${animal.title}`} />

            <div className="max-w-2xl mx-auto space-y-6">
                <div className="border-b border-border pb-4">
                    <Link href="/peternak/hewan" className="inline-flex items-center gap-1 text-xs font-semibold text-green-600 hover:text-green-700 mb-1">
                        <ArrowLeft className="w-3.5 h-3.5" />
                        <span>Kembali ke Daftar Hewan</span>
                    </Link>
                    <h1 className="text-xl sm:text-2xl font-bold text-green-900 mt-2">Edit Data Hewan Kurban</h1>
                    <p className="text-xs text-muted mt-0.5">Status saat ini: <strong className="capitalize">{animal.status}</strong></p>
                </div>

                <div className="bg-white border border-border rounded-card p-6 sm:p-8 shadow-sm">
                    <form onSubmit={submit} className="space-y-5">
                        <div>
                            <InputLabel htmlFor="title" value="Nama / Kode Hewan" className="text-xs font-bold text-text" />
                            <TextInput
                                id="title"
                                name="title"
                                value={data.title}
                                className="mt-1 block w-full"
                                onChange={(e) => setData('title', e.target.value)}
                                required
                            />
                            <InputError message={errors.title} className="mt-1 text-xs" />
                        </div>

                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <InputLabel htmlFor="type" value="Jenis Hewan" className="text-xs font-bold text-text" />
                                <select
                                    id="type"
                                    name="type"
                                    value={data.type}
                                    onChange={(e) => setData('type', e.target.value)}
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

                        <div>
                            <InputLabel htmlFor="price" value="Harga Hewan Kurban (Rp)" className="text-xs font-bold text-text" />
                            <TextInput
                                id="price"
                                type="number"
                                step="50000"
                                min="1000000"
                                name="price"
                                value={data.price}
                                className="mt-1 block w-full tabular-nums font-bold text-green-900"
                                onChange={(e) => setData('price', parseInt(e.target.value, 10) || 0)}
                                required
                            />
                            <InputError message={errors.price} className="mt-1 text-xs" />
                        </div>

                        <div>
                            <InputLabel htmlFor="health_notes" value="Catatan Kesehatan" className="text-xs font-bold text-text" />
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

                        <div className="pt-3 border-t border-border flex items-center justify-end gap-3">
                            <Link
                                href="/peternak/hewan"
                                className="px-4 py-2.5 rounded-btn text-xs font-semibold text-muted hover:text-text"
                            >
                                Batal
                            </Link>
                            <PrimaryButton disabled={processing}>
                                {processing ? 'Menyimpan...' : 'Perbarui & Ajukan Ulang'}
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </PeternakLayout>
    );
}

