import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import PeternakLayout from '@/Layouts/PeternakLayout';
import { Store, MapPin, ShieldAlert, LogOut } from 'lucide-react';

export default function PeternakProfil({ auth }) {
    const handleLogout = (e) => {
        e.preventDefault();
        router.post('/logout');
    };

    return (
        <PeternakLayout>
            <Head title="Profil Peternak - YuKurban" />

            <div className="max-w-2xl mx-auto space-y-6">
                <div className="border-b border-border pb-3">
                    <h1 className="text-xl sm:text-2xl font-bold text-green-900">Profil Kandang & Peternak</h1>
                    <p className="text-xs text-muted">Informasi identitas dan lokasi kandang untuk verifikasi penjualan hewan</p>
                </div>

                <div className="bg-white border border-border rounded-card p-6 shadow-sm space-y-4">
                    <div className="flex items-center gap-4">
                        <div className="w-14 h-14 rounded-full bg-gold-100 text-gold-600 font-bold text-xl flex items-center justify-center">
                            {auth.user?.name ? auth.user.name.charAt(0).toUpperCase() : 'P'}
                        </div>
                        <div>
                            <h2 className="text-base font-bold text-text">{auth.user?.name}</h2>
                            <p className="text-xs text-muted">{auth.user?.email}</p>
                            <span className="inline-block mt-1.5 px-2 py-0.5 rounded text-[11px] font-semibold bg-gold-100 text-gold-600">
                                Akun Peternak
                            </span>
                        </div>
                    </div>
                </div>

                <div className="bg-white border border-border rounded-card overflow-hidden shadow-sm">
                    <button
                        onClick={handleLogout}
                        className="w-full p-4 flex items-center justify-between text-danger hover:bg-danger/5 transition-colors"
                    >
                        <span className="text-xs font-semibold flex items-center gap-2">
                            <LogOut className="w-4 h-4" />
                            Keluar dari Akun
                        </span>
                    </button>
                </div>
            </div>
        </PeternakLayout>
    );
}

