import React from 'react';
import { Link } from '@inertiajs/react';
import SimulationBanner from '@/Components/SimulationBanner';

export default function GuestLayout({ children }) {
    return (
        <div className="min-h-screen flex flex-col bg-surface text-text">
            <SimulationBanner />

            <div className="flex flex-1 flex-col items-center justify-center px-4 py-8 sm:px-6">
                <div className="mb-6 flex flex-col items-center">
                    <Link href="/" className="flex items-center gap-2.5">
                        <div className="w-11 h-11 rounded-2xl bg-green-600 flex items-center justify-center text-white font-black text-xl shadow-md">
                            YK
                        </div>
                        <div className="flex flex-col">
                            <span className="font-extrabold text-2xl text-green-900 tracking-tight leading-none">
                                Yu<span className="text-gold-500">Kurban</span>
                            </span>
                            <span className="text-xs text-muted font-medium mt-0.5">Amanah & Transparan</span>
                        </div>
                    </Link>
                </div>

                <div className="w-full max-w-md bg-white border border-border rounded-card p-6 sm:p-8 shadow-sm">
                    {children}
                </div>

                <div className="mt-6 text-center text-xs text-muted">
                    <Link href="/" className="hover:text-green-700 font-medium">← Kembali ke Beranda</Link>
                </div>
            </div>
        </div>
    );
}
