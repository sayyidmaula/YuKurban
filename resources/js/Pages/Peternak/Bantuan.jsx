import React from 'react';
import { Head } from '@inertiajs/react';
import PeternakLayout from '@/Layouts/PeternakLayout';
import { HelpCircle } from 'lucide-react';

export default function Bantuan() {
    return (
        <PeternakLayout>
            <Head title="Bantuan Peternak - YuKurban" />
            <div className="max-w-2xl mx-auto space-y-4">
                <h1 className="text-xl font-bold text-green-900">Pusat Bantuan Peternak</h1>
                <p className="text-xs text-muted">Panduan pendaftaran kandang, standar kualitas foto hewan kurban, dan pencairan payout.</p>
                <div className="bg-white border border-border rounded-card p-6 shadow-sm">
                    <p className="text-xs text-muted">Gunakan tiket bantuan peternak untuk berkomunikasi langsung dengan tim kurator admin YuKurban.</p>
                </div>
            </div>
        </PeternakLayout>
    );
}

