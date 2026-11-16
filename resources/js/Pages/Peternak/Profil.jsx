import React from 'react';
import { Head, Link, useForm, router } from '@inertiajs/react';
import PeternakLayout from '@/Layouts/PeternakLayout';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import { Store, MapPin, ShieldCheck, AlertCircle, Clock, XCircle, LogOut } from 'lucide-react';

export default function PeternakProfil({ auth, profile, regions = [] }) {
    const { data, setData, post, processing, errors } = useForm({
        farm_name: profile?.farm_name || '',
        address: profile?.address || '',
        region_id: profile?.region_id || (regions[0]?.id ?? ''),
        description: profile?.description || '',
        farm_photo: null,
    });

    const submit = (e) => {
        e.preventDefault();
        post('/peternak/profil');
    };

    const handleLogout = (e) => {
        e.preventDefault();
        router.post('/logout');
    };

    const status = profile?.status ?? 'belum_ada';

    return (
        <PeternakLayout>
            <Head title="Profil Kandang Peternak - YuKurban" />

            <div className="max-w-2xl mx-auto space-y-6">
                <div className="border-b border-border pb-3">
                    <h1 className="text-xl sm:text-2xl font-bold text-green-900">Profil Kandang Peternak</h1>
                    <p className="text-xs text-muted">Lengkapi data kandang untuk diverifikasi oleh tim admin YuKurban</p>
                </div>

                {/* Status Verifikasi Badge */}
                <div className="bg-white border border-border rounded-card p-5 shadow-sm">
                    <div className="flex items-center justify-between">
                        <div>
                            <span className="text-xs text-muted font-medium">Status Verifikasi Akun:</span>
                            <div className="mt-1">
                                {status === 'terverifikasi' && (
                                    <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-green-50 text-green-700">
                                        <ShieldCheck className="w-4 h-4 text-green-600" />
                                        <span>Terverifikasi (Mitra Resmi)</span>
                                    </span>
                                )}
                                {status === 'menunggu' && (
                                    <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-gold-100 text-gold-600">
                                        <Clock className="w-4 h-4" />
                                        <span>Menunggu Review Kurator Admin</span>
                                    </span>
                                )}
                                {status === 'ditolak' && (
                                    <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-danger/10 text-danger">
                                        <XCircle className="w-4 h-4" />
                                        <span>Verifikasi Ditolak</span>
                                    </span>
                                )}
                                {status === 'belum_ada' && (
                                    <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-surface text-muted">
                                        <AlertCircle className="w-4 h-4" />
                                        <span>Belum Melengkapi Profil</span>
                                    </span>
                                )}
                            </div>
                        </div>

                        <span className="text-[11px] text-muted">
                            Akun: {auth.user?.name}
                        </span>
                    </div>

                    {status === 'ditolak' && profile?.rejection_reason && (
                        <div className="mt-4 p-3 bg-danger/5 border border-danger/20 rounded-btn text-xs text-danger">
                            <strong>Alasan Penolakan:</strong> {profile.rejection_reason}
                        </div>
                    )}
                </div>

                {/* Form Data Kandang */}
                <div className="bg-white border border-border rounded-card p-6 sm:p-8 shadow-sm">
                    <form onSubmit={submit} className="space-y-5">
                        <div>
                            <InputLabel htmlFor="farm_name" value="Nama Kandang / Usaha Peternakan" className="text-xs font-bold text-text" />
                            <TextInput
                                id="farm_name"
                                name="farm_name"
                                value={data.farm_name}
                                placeholder="Contoh: Peternakan Berkah Al-Ikhlas"
                                className="mt-1 block w-full"
                                onChange={(e) => setData('farm_name', e.target.value)}
                                required
                            />
                            <InputError message={errors.farm_name} className="mt-1 text-xs" />
                        </div>

                        <div>
                            <InputLabel htmlFor="region_id" value="Wilayah / Kota Kandang" className="text-xs font-bold text-text" />
                            <select
                                id="region_id"
                                name="region_id"
                                value={data.region_id}
                                onChange={(e) => setData('region_id', e.target.value)}
                                className="mt-1 block w-full min-h-[44px] rounded-btn border-border text-sm shadow-sm focus:border-green-600 focus:ring-green-600"
                                required
                            >
                                <option value="" disabled>Pilih Kota / Kabupaten</option>
                                {regions.map((reg) => (
                                    <option key={reg.id} value={reg.id}>
                                        {reg.city}, {reg.province}
                                    </option>
                                ))}
                            </select>
                            <InputError message={errors.region_id} className="mt-1 text-xs" />
                        </div>

                        <div>
                            <InputLabel htmlFor="address" value="Alamat Lengkap Kandang" className="text-xs font-bold text-text" />
                            <textarea
                                id="address"
                                name="address"
                                rows={3}
                                value={data.address}
                                placeholder="Jl. Raya Desa No. 12, RT 02/RW 03, Kecamatan..."
                                onChange={(e) => setData('address', e.target.value)}
                                className="mt-1 block w-full rounded-btn border-border text-sm shadow-sm focus:border-green-600 focus:ring-green-600"
                                required
                            />
                            <p className="text-[11px] text-muted mt-1">Alamat ini disimpan rahasia dan tidak pernah ditampilkan ke pembeli umum (BR-10).</p>
                            <InputError message={errors.address} className="mt-1 text-xs" />
                        </div>

                        <div>
                            <InputLabel htmlFor="description" value="Deskripsi Kandang & Pengalaman (Opsional)" className="text-xs font-bold text-text" />
                            <textarea
                                id="description"
                                name="description"
                                rows={2}
                                value={data.description}
                                placeholder="Ceritakan kapasitas ternak, pakan yang digunakan, sertifikasi dokter hewan, dll."
                                onChange={(e) => setData('description', e.target.value)}
                                className="mt-1 block w-full rounded-btn border-border text-sm shadow-sm focus:border-green-600 focus:ring-green-600"
                            />
                            <InputError message={errors.description} className="mt-1 text-xs" />
                        </div>

                        <div>
                            <InputLabel htmlFor="farm_photo" value="Foto Kandang / Lingkungan Ternak" className="text-xs font-bold text-text" />
                            <input
                                id="farm_photo"
                                type="file"
                                accept="image/*"
                                onChange={(e) => setData('farm_photo', e.target.files[0])}
                                className="mt-1 block w-full text-xs text-muted file:mr-4 file:py-2 file:px-4 file:rounded-btn file:border-0 file:text-xs file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100"
                            />
                            <InputError message={errors.farm_photo} className="mt-1 text-xs" />
                        </div>

                        <div className="pt-3 border-t border-border flex items-center justify-end">
                            <PrimaryButton disabled={processing}>
                                {processing ? 'Menyimpan...' : 'Simpan & Ajukan Verifikasi'}
                            </PrimaryButton>
                        </div>
                    </form>
                </div>

                <div className="bg-white border border-border rounded-card p-4 shadow-sm flex items-center justify-between">
                    <span className="text-xs text-muted">Ingin keluar dari akun ini?</span>
                    <button
                        onClick={handleLogout}
                        className="inline-flex items-center gap-1.5 text-xs font-bold text-danger hover:underline"
                    >
                        <LogOut className="w-3.5 h-3.5" />
                        <span>Keluar Akun</span>
                    </button>
                </div>
            </div>
        </PeternakLayout>
    );
}
