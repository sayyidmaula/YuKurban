import React, { useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import PublicLayout from '@/Layouts/PublicLayout';
import PenabungLayout from '@/Layouts/PenabungLayout';
import { formatRupiah, getAnimalImageUrl } from '@/Utils/format';
import { ArrowLeft, MapPin, ShieldCheck, HeartHandshake, Film, Image as ImageIcon, MessageCircleQuestion, AlertCircle, ShoppingBag } from 'lucide-react';

export default function KatalogShow({ animal, isPenabung = false, savingsPlans = [] }) {
    const Layout = isPenabung ? PenabungLayout : PublicLayout;

    const photos = animal.media.filter((m) => m.type === 'foto');
    const videos = animal.media.filter((m) => m.type === 'video');

    const [selectedPhoto, setSelectedPhoto] = useState(photos[0]?.path || null);
    const [viewMode, setViewMode] = useState('foto'); // 'foto' | 'video'

    const totalSavings = savingsPlans.reduce((sum, p) => sum + (p.balance_cached || 0), 0);

    return (
        <Layout>
            <Head title={`${animal.title} - Katalog Hewan Kurban`} />

            <div className="max-w-5xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8 py-6 sm:py-8 pb-16 sm:pb-24">
                {/* Back Link */}
                <div className="border-b border-border pb-3">
                    <Link
                        href="/katalog"
                        className="inline-flex items-center gap-1.5 text-xs font-semibold text-green-600 hover:text-green-700"
                    >
                        <ArrowLeft className="w-4 h-4" />
                        <span>Kembali ke Katalog Hewan</span>
                    </Link>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    {/* Left: Media Gallery */}
                    <div className="lg:col-span-2 space-y-4">
                        {/* Media Mode Tabs */}
                        <div className="flex items-center gap-2">
                            <button
                                onClick={() => setViewMode('foto')}
                                className={`inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-btn text-xs font-bold transition-all ${
                                    viewMode === 'foto'
                                        ? 'bg-green-600 text-white shadow-sm'
                                        : 'bg-white border border-border text-muted hover:text-text'
                                }`}
                            >
                                <ImageIcon className="w-3.5 h-3.5" />
                                <span>Foto Fisik ({photos.length})</span>
                            </button>

                            {videos.length > 0 && (
                                <button
                                    onClick={() => setViewMode('video')}
                                    className={`inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-btn text-xs font-bold transition-all ${
                                        viewMode === 'video'
                                            ? 'bg-gold-500 text-white shadow-sm'
                                            : 'bg-white border border-border text-muted hover:text-text'
                                    }`}
                                >
                                    <Film className="w-3.5 h-3.5" />
                                    <span>Video Fisik ({videos.length})</span>
                                </button>
                            )}
                        </div>

                        {/* Main Media Player / Viewer */}
                        <div className="bg-white border border-border rounded-card overflow-hidden shadow-sm">
                            {viewMode === 'foto' ? (
                                <div className="aspect-[4/3] bg-surface relative flex items-center justify-center overflow-hidden">
                                    <img
                                        src={getAnimalImageUrl(selectedPhoto, animal.type)}
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
                            ) : (
                                <div className="aspect-[4/3] bg-black relative flex items-center justify-center">
                                    {videos[0] ? (
                                        <video
                                            controls
                                            src={getAnimalImageUrl(videos[0].path)}
                                            className="w-full h-full max-h-[480px]"
                                        />
                                    ) : (
                                        <div className="text-xs text-white">Video tidak tersedia</div>
                                    )}
                                </div>
                            )}

                            {/* Thumbnail Strips for Photos */}
                            {viewMode === 'foto' && photos.length > 1 && (
                                <div className="p-3 bg-surface border-t border-border flex items-center gap-2 overflow-x-auto">
                                    {photos.map((p, idx) => (
                                        <button
                                            key={p.id}
                                            onClick={() => setSelectedPhoto(p.path)}
                                            className={`w-16 h-16 rounded-btn overflow-hidden flex-shrink-0 border-2 transition-all ${
                                                selectedPhoto === p.path ? 'border-green-600 scale-105' : 'border-transparent opacity-70 hover:opacity-100'
                                            }`}
                                        >
                                            <img
                                                src={getAnimalImageUrl(p.path, animal.type)}
                                                alt={`Thumbnail ${idx + 1}`}
                                                className="w-full h-full object-cover"
                                                onError={(e) => {
                                                    const fallback = animal.type === 'sapi' ? '/animals/sapi-01.jpg' : animal.type === 'kambing' ? '/animals/kambing-01.jpg' : '/animals/domba-01.jpg';
                                                    if (!e.currentTarget.src.endsWith(fallback)) {
                                                        e.currentTarget.src = fallback;
                                                    }
                                                }}
                                            />
                                        </button>
                                    ))}
                                </div>
                            )}
                        </div>

                        {/* Spesifikasi & Catatan Kesehatan */}
                        <div className="bg-white border border-border rounded-card p-6 shadow-sm space-y-4">
                            <h3 className="text-base font-bold text-green-900 border-b border-border pb-2">
                                Catatan Kesehatan & Kelayakan Syariat
                            </h3>

                            <div className="text-xs sm:text-sm text-text leading-relaxed whitespace-pre-line bg-surface p-4 rounded-btn border border-border">
                                {animal.health_notes}
                            </div>

                            <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2 text-center text-xs">
                                <div className="p-3 bg-green-50 rounded-btn border border-green-100">
                                    <span className="text-[11px] text-muted block">Jenis</span>
                                    <span className="font-bold text-green-900 capitalize">{animal.type_label}</span>
                                </div>
                                <div className="p-3 bg-green-50 rounded-btn border border-green-100">
                                    <span className="text-[11px] text-muted block">Umur</span>
                                    <span className="font-bold text-green-900 tabular-nums">{animal.age_months} Bulan</span>
                                </div>
                                <div className="p-3 bg-green-50 rounded-btn border border-green-100">
                                    <span className="text-[11px] text-muted block">Bobot Hidup</span>
                                    <span className="font-bold text-green-900 tabular-nums">{animal.weight_kg} kg</span>
                                </div>
                                <div className="p-3 bg-green-50 rounded-btn border border-green-100">
                                    <span className="text-[11px] text-muted block">Asal Ternak</span>
                                    <span className="font-bold text-green-900 truncate block">{animal.region}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Right: Price & Order Action Card */}
                    <div className="space-y-5">
                        <div className="bg-white border border-border rounded-card p-6 shadow-sm space-y-5 sticky top-24">
                            <div>
                                <span className="px-2.5 py-1 rounded text-xs font-bold bg-green-50 text-green-700 capitalize">
                                    {animal.type_label} Kurban
                                </span>
                                <h1 className="text-xl font-extrabold text-green-900 mt-2">{animal.title}</h1>
                                <p className="text-xs text-muted flex items-center gap-1 mt-1">
                                    <MapPin className="w-3.5 h-3.5 text-gold-600" />
                                    <span>{animal.region} • Kandang: <strong>{animal.farm_name}</strong></span>
                                </p>
                            </div>

                            <div className="p-4 bg-surface rounded-card border border-border">
                                <span className="text-xs text-muted block">Harga Hewan Kurban</span>
                                <div className="text-3xl font-black text-green-900 tabular-nums mt-0.5">
                                    {formatRupiah(animal.price)}
                                </div>
                                <p className="text-[11px] text-muted mt-1 leading-relaxed">
                                    *Belum termasuk biaya layanan sesuai metode (sembelih tim / ongkir pengiriman).
                                </p>
                            </div>

                            {/* Privacy Notice BR-10 */}
                            <div className="p-3 bg-green-50 border border-green-100 rounded-btn text-[11px] text-green-800 leading-relaxed flex items-start gap-2">
                                <ShieldCheck className="w-4 h-4 text-green-600 flex-shrink-0 mt-0.5" />
                                <span>
                                    <strong>Amanah & Privasi:</strong> Seluruh transaksi dan pengawasan dipandu oleh tim kurator platform YuKurban tanpa kontak langsung pihak ketiga.
                                </span>
                            </div>

                            {/* Purchase CTA */}
                            {isPenabung ? (
                                <div className="space-y-3 pt-2">
                                    <div className="text-xs flex items-center justify-between">
                                        <span className="text-muted">Total Saldo Tabungan:</span>
                                        <span className="font-bold text-green-900 tabular-nums">{formatRupiah(totalSavings)}</span>
                                    </div>

                                    {animal.is_available ? (
                                        <Link
                                            href={`/beli/${animal.id}`}
                                            className="w-full py-3.5 px-4 rounded-btn bg-green-600 text-white font-bold text-sm hover:bg-green-700 shadow-md transition-colors flex items-center justify-center gap-2"
                                        >
                                            <ShoppingBag className="w-4 h-4" />
                                            <span>Lanjut ke Pemesanan</span>
                                        </Link>
                                    ) : (
                                        <div className="w-full py-3 px-4 rounded-btn bg-surface border border-border text-center text-xs font-semibold text-muted">
                                            Hewan Sedang Direservasi / Tidak Tersedia
                                        </div>
                                    )}
                                </div>
                            ) : (
                                <div className="space-y-2 pt-2">
                                    <Link
                                        href="/masuk"
                                        className="w-full py-3 px-4 rounded-btn bg-green-600 text-white font-bold text-xs hover:bg-green-700 shadow-sm transition-colors text-center block"
                                    >
                                        Masuk untuk Memesan Kurban
                                    </Link>
                                    <Link
                                        href="/daftar"
                                        className="w-full py-2.5 px-4 rounded-btn border border-green-600 text-green-700 font-semibold text-xs hover:bg-green-50 transition-colors text-center block"
                                    >
                                        Daftar Akun Baru
                                    </Link>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </Layout>
    );
}

