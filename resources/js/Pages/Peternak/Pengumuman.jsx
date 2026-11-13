import React from 'react';
import { Head } from '@inertiajs/react';
import PeternakLayout from '@/Layouts/PeternakLayout';
import { Bell } from 'lucide-react';

export default function Pengumuman() {
    return (
        <PeternakLayout>
            <Head title="Pengumuman Peternak - YuKurban" />
            <div className="max-w-2xl mx-auto space-y-4">
                <h1 className="text-xl font-bold text-green-900">Pengumuman & Informasi Musim</h1>
                <p className="text-xs text-muted">Informasi tanggal Idul Adha, pembukaan jendela pembelian, dan jadwal penjemputan kurban.</p>
                <div className="bg-white border border-border rounded-card p-6 shadow-sm">
                    <p className="text-xs text-muted">Belum ada pengumuman baru untuk musim kurban ini.</p>
                </div>
            </div>
        </PeternakLayout>
    );
}

