import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import PublicLayout from '@/Layouts/PublicLayout';
import PenabungLayout from '@/Layouts/PenabungLayout';
import { formatRupiah, getAnimalImageUrl } from '@/Utils/format';
import { Compass, Filter, ArrowUpDown, ShieldCheck, MapPin, Sparkles, AlertCircle } from 'lucide-react';

export default function KatalogIndex({
    animals,
    regions = [],
    tiers = [],
    filters = {},
    isPenabung = false,
    activeSavingsPlans = [],
    purchaseWindows = {},
}) {
    const Layout = isPenabung ? PenabungLayout : PublicLayout;

    const handleFilterChange = (key, value) => {
        const newFilters = { ...filters, [key]: value };
        if (!value) delete newFilters[key];
        router.get('/katalog', newFilters, { preserveState: true, preserveScroll: true });
    };

    const types = [
        { key: '', label: 'Semua Hewan' },
        { key: 'kambing', label: 'Kambing' },
        { key: 'domba', label: 'Domba' },
        { key: 'sapi', label: 'Sapi' },
    ];

    return (
        <Layout>
            <Head title="Katalog Hewan Kurban - YuKurban" />

            <div className="space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 pb-16 sm:pb-24">
                {/* Header & Subtitle */}
                <div className="text-center sm:text-left border-b border-border pb-4">
                    <h1 className="text-2xl sm:text-3xl font-extrabold text-green-900">
                        Katalog Hewan Kurban Terverifikasi
                    </h1>
                    <p className="text-xs sm:text-sm text-muted mt-1">
                        Satu baris mewakili satu hewan fisik nyata dari peternak lokal terkurasi (BR-16).
                    </p>
                </div>

                {/* Filter Bar */}
                <div className="bg-white border border-border rounded-card p-4 sm:p-5 shadow-sm space-y-4">
                    {/* Animal Type Tabs */}
                    <div className="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
                        {types.map((t) => {
                            const isSelected = (filters.type || '') === t.key;
                            return (
                                <button
                                    key={t.key}
                                    onClick={() => handleFilterChange('type', t.key)}
                                    className={`px-4 py-2 rounded-btn text-xs font-semibold whitespace-nowrap transition-all ${
                                        isSelected
                                            ? 'bg-green-600 text-white shadow-sm'
                                            : 'bg-surface text-muted hover:text-text hover:bg-surface/80'
                                    }`}
                                >
                                    {t.label}
                                </button>
                            );
                        })}
                    </div>

                    {/* Secondary Filters: Region, Tier, Sort */}
                    <div className="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2 border-t border-border">
                        {/* Region Filter */}
                        <div>
                            <select
                                value={filters.region_id || ''}
                                onChange={(e) => handleFilterChange('region_id', e.target.value)}
                                className="w-full min-h-[40px] rounded-btn border-border text-xs focus:border-green-600 focus:ring-green-600"
                            >
                                <option value="">Semua Wilayah</option>
                                {regions.map((reg) => (
                                    <option key={reg.id} value={reg.id}>
                                        {reg.city}, {reg.province}
                                    </option>
                                ))}
                            </select>
                        </div>

                        {/* Price Tier Filter */}
                        <div>
                            <select
                                value={filters.tier_id || ''}
                                onChange={(e) => handleFilterChange('tier_id', e.target.value)}
                                className="w-full min-h-[40px] rounded-btn border-border text-xs focus:border-green-600 focus:ring-green-600 capitalize"
                            >
                                <option value="">Semua Tingkat Harga</option>
                                {tiers.map((tier) => (
                                    <option key={tier.id} value={tier.id}>
                                        Tier {tier.name} ({formatRupiah(tier.min_price)} - {formatRupiah(tier.max_price)})
                                    </option>
                                ))}
                            </select>
                        </div>

                        {/* Sort Option */}
                        <div>
                            <select
                                value={filters.sort || 'price_asc'}
                                onChange={(e) => handleFilterChange('sort', e.target.value)}
                                className="w-full min-h-[40px] rounded-btn border-border text-xs focus:border-green-600 focus:ring-green-600"
                            >
                                <option value="price_asc">Harga Terendah → Tertinggi</option>
                                <option value="price_desc">Harga Tertinggi → Terendah</option>
                                <option value="weight_desc">Bobot Terbesar</option>
                            </select>
                        </div>
                    </div>
                </div>

                {/* Animals Grid */}
                {animals.data.length === 0 ? (
                    <div className="bg-white border border-border rounded-card p-12 text-center shadow-sm">
                        <div className="w-12 h-12 rounded-full bg-gold-100 text-gold-600 flex items-center justify-center mx-auto mb-3">
                            <Compass className="w-6 h-6" />
                        </div>
                        <h3 className="text-base font-bold text-text">Tidak Ada Hewan yang Sesuai Filter</h3>
                        <p className="text-xs text-muted mt-1 max-w-sm mx-auto">
                            Coba ubah kriteria filter jenis, wilayah, atau tingkat harga untuk melihat hewan kurban lainnya.
                        </p>
                        <button
                            onClick={() => router.get('/katalog')}
                            className="mt-4 px-4 py-2 rounded-btn bg-green-50 text-green-700 font-semibold text-xs hover:bg-green-100"
                        >
                            Reset Semua Filter
                        </button>
                    </div>
                ) : (
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                        {animals.data.map((animal) => (
                            <Link
                                key={animal.id}
                                href={`/katalog/${animal.id}`}
                                className="group bg-white border border-border rounded-card overflow-hidden shadow-sm hover:shadow-md hover:border-green-600 transition-all flex flex-col justify-between"
                            >
                                <div>
                                    {/* Thumbnail Image */}
                                    <div className="h-48 bg-surface relative overflow-hidden flex items-center justify-center">
                                        <img
                                            src={getAnimalImageUrl(animal.thumbnail, animal.type)}
                                            alt={animal.title}
                                            className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                            onError={(e) => {
                                                const fallback = animal.type === 'sapi' ? '/animals/sapi-01.jpg' : animal.type === 'kambing' ? '/animals/kambing-01.jpg' : '/animals/domba-01.jpg';
                                                if (!e.currentTarget.src.endsWith(fallback)) {
                                                    e.currentTarget.src = fallback;
                                                }
                                            }}
                                        />

                                        <div className="absolute top-2.5 left-2.5 flex items-center gap-1.5">
                                            <span className="px-2 py-0.5 rounded text-[10px] font-bold bg-white/95 backdrop-blur text-green-900 shadow-sm capitalize">
                                                {animal.type_label}
                                            </span>
                                        </div>

                                        <div className="absolute top-2.5 right-2.5">
                                            <span className="px-2 py-0.5 rounded text-[10px] font-bold bg-green-600 text-white shadow-sm">
                                                Tersedia
                                            </span>
                                        </div>
                                    </div>

                                    {/* Body Info */}
                                    <div className="p-4 space-y-2">
                                        <h3 className="font-bold text-sm text-green-900 group-hover:text-green-700 line-clamp-1">
                                            {animal.title}
                                        </h3>

                                        <div className="flex items-center justify-between text-xs text-muted">
                                            <span>Bobot: <strong>{animal.weight_kg} kg</strong></span>
                                            <span>Umur: <strong>{animal.age_months} bln</strong></span>
                                        </div>

                                        <div className="flex items-center gap-1 text-[11px] text-muted">
                                            <MapPin className="w-3.5 h-3.5 text-gold-600 flex-shrink-0" />
                                            <span className="truncate">{animal.region}</span>
                                        </div>

                                        <div className="text-[11px] text-muted font-medium">
                                            Kandang: {animal.farm_name}
                                        </div>

                                        <div className="pt-2 border-t border-border flex items-center justify-between">
                                            <span className="text-[10px] text-muted uppercase">Harga</span>
                                            <span className="text-base font-extrabold text-green-900 tabular-nums">
                                                {formatRupiah(animal.price)}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div className="p-4 pt-0">
                                    <div className="w-full py-2 text-center rounded-btn bg-green-50 text-green-700 font-semibold text-xs group-hover:bg-green-600 group-hover:text-white transition-colors">
                                        Lihat Spesifikasi & Bukti →
                                    </div>
                                </div>
                            </Link>
                        ))}
                    </div>
                )}
            </div>
        </Layout>
    );
}

