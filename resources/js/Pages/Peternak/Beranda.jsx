import React from 'react';
import { Head, Link } from '@inertiajs/react';
import PeternakLayout from '@/Layouts/PeternakLayout';
import { Compass, ClipboardList, CreditCard, AlertCircle, PlusCircle } from 'lucide-react';

export default function PeternakBeranda({ auth }) {
    return (
        <PeternakLayout>
            <Head title="Beranda Mitra Peternak - YuKurban" />

            <div className="space-y-6 max-w-4xl mx-auto">
                <div>
                    <h1 className="text-xl sm:text-2xl font-bold text-green-900">
                        Portal Mitra Peternak, {auth.user?.name}
                    </h1>
                    <p className="text-xs text-muted mt-0.5">Kelola data hewan kurban, pantau pesanan kurban, dan payout penjualan.</p>
                </div>

                {/* Banner Status Profil */}
                <div className="bg-gold-100 border border-gold-300 rounded-card p-4 sm:p-5 flex items-start gap-3">
                    <AlertCircle className="w-5 h-5 text-gold-600 flex-shrink-0 mt-0.5" />
                    <div>
                        <h3 className="font-bold text-xs sm:text-sm text-gold-600">Lengkapi Profil Kandang</h3>
                        <p className="text-xs text-muted mt-1 leading-relaxed">
                            Pastikan Anda telah melengkapi foto dan alamat kandang di menu Profil agar akun dapat segera diverifikasi oleh tim admin YuKurban.
                        </p>
                        <Link
                            href="/peternak/profil"
                            className="mt-3 inline-flex items-center text-xs font-bold text-green-700 hover:text-green-800 underline"
                        >
                            Buka Profil Kandang →
                        </Link>
                    </div>
                </div>

                {/* Ringkasan Statistik Singkat */}
                <div className="grid grid-cols-3 gap-3 sm:gap-4">
                    <div className="bg-white border border-border p-4 rounded-card shadow-sm">
                        <span className="text-xs text-muted font-medium">Hewan Tayang</span>
                        <div className="text-xl sm:text-2xl font-bold text-green-900 mt-1">0</div>
                    </div>

                    <div className="bg-white border border-border p-4 rounded-card shadow-sm">
                        <span className="text-xs text-muted font-medium">Pesanan Masuk</span>
                        <div className="text-xl sm:text-2xl font-bold text-green-900 mt-1">0</div>
                    </div>

                    <div className="bg-white border border-border p-4 rounded-card shadow-sm">
                        <span className="text-xs text-muted font-medium">Payout Tertahan</span>
                        <div className="text-xl sm:text-2xl font-bold text-gold-600 tabular-nums mt-1">Rp 0</div>
                    </div>
                </div>
            </div>
        </PeternakLayout>
    );
}

