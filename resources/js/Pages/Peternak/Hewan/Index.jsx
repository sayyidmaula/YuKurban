import React from 'react';
import { Head, Link } from '@inertiajs/react';
import PeternakLayout from '@/Layouts/PeternakLayout';
import { formatRupiah, getAnimalImageUrl } from '@/Utils/format';
import { PlusCircle, Compass, AlertCircle, Edit, CheckCircle2, Clock } from 'lucide-react';

export default function HewanIndex({ animals = [], isVerified, profileStatus }) {
    return (
        <PeternakLayout>
            <Head title="Kelola Hewan Kurban - YuKurban" />

            <div className="space-y-6 max-w-5xl mx-auto">
                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-border pb-4">
                    <div>
                        <h1 className="text-xl sm:text-2xl font-bold text-green-900">Kelola Hewan Kurban</h1>
                        <p className="text-xs text-muted mt-0.5">Daftarkan hewan kurban fisik dengan foto dan video asli (BR-16)</p>
                    </div>

                    <Link
                        href="/peternak/hewan/buat"
                        className="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-btn bg-green-600 text-white font-semibold text-xs hover:bg-green-700 shadow-sm transition-colors"
                    >
                        <PlusCircle className="w-4 h-4" />
                        <span>Tambah Hewan Baru</span>
                    </Link>
                </div>

                {!isVerified && (
                    <div className="bg-gold-100 border border-gold-300 rounded-card p-4 flex items-start gap-3">
                        <AlertCircle className="w-5 h-5 text-gold-600 flex-shrink-0 mt-0.5" />
                        <div className="text-xs text-gold-600 leading-relaxed">
                            <strong>Status Profil:</strong> Kandang Anda berstatus <em>{profileStatus}</em>. Hewan yang Anda tambahkan akan berstatus <em>Menunggu Review</em> dan akan dikurasi tayang oleh admin setelah profil kandang terverifikasi.
                        </div>
                    </div>
                )}

                {animals.length === 0 ? (
                    <div className="bg-white border border-border rounded-card p-10 text-center shadow-sm">
                        <div className="w-12 h-12 rounded-full bg-gold-100 text-gold-600 flex items-center justify-center mx-auto mb-3">
                            <Compass className="w-6 h-6" />
                        </div>
                        <h3 className="text-base font-bold text-text">Belum Ada Hewan Terdaftar</h3>
                        <p className="text-xs text-muted mt-1 max-w-sm mx-auto">
                            Mulai daftarkan kambing, domba, atau sapi kurban Anda dengan melengkapi foto dan video kondisi fisik.
                        </p>
                        <Link
                            href="/peternak/hewan/buat"
                            className="mt-4 inline-flex items-center gap-2 px-4 py-2.5 rounded-btn bg-green-600 text-white font-semibold text-xs hover:bg-green-700 shadow-sm"
                        >
                            <PlusCircle className="w-4 h-4" />
                            <span>Tambah Hewan Kurban Pertama</span>
                        </Link>
                    </div>
                ) : (
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                        {animals.map((animal) => (
                            <div
                                key={animal.id}
                                className="bg-white border border-border rounded-card overflow-hidden shadow-sm flex flex-col justify-between hover:border-green-600 transition-all"
                            >
                                <div>
                                    {/* Thumbnail Preview */}
                                    <div className="h-44 bg-surface relative overflow-hidden flex items-center justify-center">
                                        <img
                                            src={getAnimalImageUrl(animal.thumbnail, animal.type)}
                                            alt={animal.title}
                                            className="w-full h-full object-cover"
                                            onError={(e) => {
                                                const fallback = animal.type === 'sapi' ? '/animals/sapi-01.jpg' : animal.type === 'kambing' ? '/animals/kambing-01.jpg' : '/animals/domba-01.jpg';
                                                if (!e.currentTarget.src.endsWith(fallback)) {
                                                    e.currentTarget.src = fallback;
                                                }
                                            }}
                                        />
                                        <div className="absolute top-2.5 left-2.5">
                                            <span className="px-2 py-0.5 rounded text-[10px] font-bold bg-white/90 backdrop-blur text-green-900 capitalize shadow-sm">
                                                {animal.type_label}
                                            </span>
                                        </div>
                                        <div className="absolute top-2.5 right-2.5">
                                            <span className={`px-2 py-0.5 rounded text-[10px] font-bold shadow-sm ${
                                                animal.status === 'tayang'
                                                    ? 'bg-green-600 text-white'
                                                    : animal.status === 'menunggu_review'
                                                    ? 'bg-gold-500 text-white'
                                                    : animal.status === 'dipesan'
                                                    ? 'bg-info text-white'
                                                    : 'bg-surface text-muted'
                                            }`}>
                                                {animal.status_label}
                                            </span>
                                        </div>
                                    </div>

                                    {/* Info Body */}
                                    <div className="p-4 space-y-2">
                                        <h3 className="font-bold text-sm text-green-900 line-clamp-1">{animal.title}</h3>
                                        <div className="text-xs text-muted flex items-center justify-between">
                                            <span>Bobot: <strong>{animal.weight_kg} kg</strong></span>
                                            <span>Umur: <strong>{animal.age_months} bln</strong></span>
                                        </div>
                                        <div className="text-xs text-muted">
                                            Wilayah: <strong>{animal.region}</strong>
                                        </div>
                                        <div className="pt-2 border-t border-border flex items-center justify-between">
                                            <span className="text-[11px] text-muted">Harga Jual:</span>
                                            <span className="text-base font-extrabold text-green-900 tabular-nums">
                                                {formatRupiah(animal.price)}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {/* Footer Action */}
                                <div className="p-4 pt-0">
                                    <Link
                                        href={`/peternak/hewan/${animal.id}/edit`}
                                        className="w-full inline-flex items-center justify-center gap-1.5 py-2 px-3 rounded-btn border border-border bg-surface hover:bg-white text-xs font-semibold text-text transition-colors"
                                    >
                                        <Edit className="w-3.5 h-3.5" />
                                        <span>Edit Data Hewan</span>
                                    </Link>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </PeternakLayout>
    );
}

