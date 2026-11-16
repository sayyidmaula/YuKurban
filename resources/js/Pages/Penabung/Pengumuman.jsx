import React from 'react';
import { Head } from '@inertiajs/react';
import PenabungLayout from '@/Layouts/PenabungLayout';
import { formatTanggalWaktu } from '@/Utils/format';
import { Bell, Calendar } from 'lucide-react';

export default function PengumumanIndex({ announcements }) {
    const list = announcements?.data || [];

    return (
        <PenabungLayout>
            <Head title="Pengumuman & Informasi Musim - YuKurban" />

            <div className="max-w-3xl mx-auto space-y-6">
                <div>
                    <h1 className="text-xl sm:text-2xl font-extrabold text-green-900 tracking-tight">
                        Pengumuman & Informasi Musim
                    </h1>
                    <p className="text-xs text-muted mt-1">
                        Informasi resmi seputar penetapan Idul Adha, jendela pembelian, dan jadwal penyaluran kurban.
                    </p>
                </div>

                {list.length === 0 ? (
                    <div className="bg-white border border-border rounded-card p-8 text-center space-y-2 shadow-sm">
                        <div className="w-10 h-10 rounded-full bg-green-50 text-green-600 mx-auto flex items-center justify-center">
                            <Bell className="w-5 h-5" />
                        </div>
                        <h3 className="text-sm font-bold text-green-900">Belum Ada Pengumuman</h3>
                        <p className="text-xs text-muted">
                            Pengumuman resmi dari platform YuKurban akan dipublikasikan di halaman ini.
                        </p>
                    </div>
                ) : (
                    <div className="space-y-4">
                        {list.map((item) => (
                            <div
                                key={item.id}
                                className="bg-white border border-border rounded-card p-5 shadow-sm space-y-3"
                            >
                                <div className="flex items-center justify-between border-b border-border pb-2.5">
                                    <h3 className="text-sm font-bold text-green-900">
                                        {item.title}
                                    </h3>
                                    <span className="text-[11px] text-muted flex items-center gap-1">
                                        <Calendar className="w-3.5 h-3.5 text-gold-600" />
                                        <span>{formatTanggalWaktu(item.published_at)}</span>
                                    </span>
                                </div>
                                <p className="text-xs text-text leading-relaxed whitespace-pre-line">
                                    {item.body}
                                </p>
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </PenabungLayout>
    );
}
