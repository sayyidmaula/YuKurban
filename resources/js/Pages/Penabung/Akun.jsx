import React, { useState, useEffect } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import PenabungLayout from '@/Layouts/PenabungLayout';
import { User, Phone, Mail, ShieldCheck, Download, LogOut, ChevronRight, HelpCircle, FileText, Smartphone } from 'lucide-react';

export default function Akun({ auth }) {
    const [installPrompt, setInstallPrompt] = useState(null);
    const [isInstalled, setIsInstalled] = useState(false);
    const [isIos, setIsIos] = useState(false);

    useEffect(() => {
        // Detect if already installed or standalone
        if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true) {
            setIsInstalled(true);
        }

        // Detect iOS
        const userAgent = window.navigator.userAgent.toLowerCase();
        const iosDevice = /iphone|ipad|ipod/.test(userAgent);
        setIsIos(iosDevice);

        const handleBeforeInstall = (e) => {
            e.preventDefault();
            setInstallPrompt(e);
        };

        window.addEventListener('beforeinstallprompt', handleBeforeInstall);

        return () => window.removeEventListener('beforeinstallprompt', handleBeforeInstall);
    }, []);

    const handleInstallClick = async () => {
        if (!installPrompt) return;
        installPrompt.prompt();
        const { outcome } = await installPrompt.userChoice;
        if (outcome === 'accepted') {
            setIsInstalled(true);
            setInstallPrompt(null);
        }
    };

    const handleLogout = (e) => {
        e.preventDefault();
        router.post('/logout');
    };

    return (
        <PenabungLayout>
            <Head title="Akun Saya - YuKurban" />

            <div className="max-w-2xl mx-auto space-y-6">
                <div className="border-b border-border pb-3">
                    <h1 className="text-xl sm:text-2xl font-bold text-green-900">Akun Pengguna</h1>
                    <p className="text-xs text-muted">Kelola informasi profil, preferensi, dan pemasangan aplikasi</p>
                </div>

                {/* Profile Card */}
                <div className="bg-white border border-border rounded-card p-6 shadow-sm">
                    <div className="flex items-center gap-4">
                        <div className="w-16 h-16 rounded-full bg-green-100 text-green-700 font-extrabold text-2xl flex items-center justify-center">
                            {auth.user?.name ? auth.user.name.charAt(0).toUpperCase() : 'U'}
                        </div>
                        <div className="flex-1 min-w-0">
                            <h2 className="text-base font-bold text-text truncate">{auth.user?.name}</h2>
                            <p className="text-xs text-muted flex items-center gap-1.5 mt-0.5">
                                <Mail className="w-3.5 h-3.5" />
                                <span>{auth.user?.email}</span>
                            </p>
                            <div className="mt-2 flex items-center gap-2">
                                <span className="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-green-100 text-green-700">
                                    <ShieldCheck className="w-3 h-3" />
                                    <span>Penabung Kurban</span>
                                </span>
                                {auth.user?.phone && (
                                    <span className="text-xs text-muted flex items-center gap-1">
                                        <Phone className="w-3 h-3" />
                                        <span>{auth.user.phone}</span>
                                    </span>
                                )}
                            </div>
                        </div>
                    </div>
                </div>

                {/* PWA Install Section (PRD Section 10) */}
                <div className="bg-white border border-border rounded-card p-6 shadow-sm">
                    <div className="flex items-start gap-4">
                        <div className="w-10 h-10 rounded-xl bg-gold-100 text-gold-600 flex items-center justify-center flex-shrink-0">
                            <Smartphone className="w-5 h-5" />
                        </div>
                        <div className="flex-1">
                            <h3 className="font-bold text-sm text-green-900">Pasang Aplikasi YuKurban (PWA)</h3>
                            <p className="text-xs text-muted mt-1 leading-relaxed">
                                Dapatkan akses lebih cepat layaknya aplikasi mobile langsung dari layar utama ponsel Anda tanpa perlu unduh lewat toko aplikasi.
                            </p>

                            {isInstalled ? (
                                <div className="mt-4 inline-flex items-center gap-2 px-3 py-1.5 rounded-btn bg-green-50 text-green-700 text-xs font-semibold">
                                    <ShieldCheck className="w-4 h-4 text-green-600" />
                                    <span>Aplikasi sudah terpasang di perangkat ini</span>
                                </div>
                            ) : installPrompt ? (
                                <button
                                    onClick={handleInstallClick}
                                    className="mt-4 inline-flex items-center gap-2 px-4 py-2.5 rounded-btn bg-green-600 text-white font-semibold text-xs hover:bg-green-700 shadow-sm transition-all"
                                >
                                    <Download className="w-4 h-4" />
                                    <span>Pasang Aplikasi Sekarang</span>
                                </button>
                            ) : isIos ? (
                                <div className="mt-4 p-3 bg-surface rounded-btn border border-border text-xs text-muted leading-relaxed">
                                    <strong>Petunjuk iPhone/iPad (iOS):</strong> Ketuk tombol <em>Bagikan (Share)</em> di browser Safari, lalu pilih <strong>"Tambah ke Layar Utama" (Add to Home Screen)</strong>.
                                </div>
                            ) : (
                                <p className="mt-3 text-[11px] text-muted italic">
                                    Buka melalui browser Chrome/Edge di Android/Desktop untuk menginstal aplikasi ini langsung ke layar utama.
                                </p>
                            )}
                        </div>
                    </div>
                </div>

                {/* Navigation Links */}
                <div className="bg-white border border-border rounded-card divide-y divide-border overflow-hidden shadow-sm">
                    <Link
                        href="/faq"
                        className="p-4 flex items-center justify-between hover:bg-surface transition-colors"
                    >
                        <div className="flex items-center gap-3">
                            <HelpCircle className="w-4 h-4 text-muted" />
                            <span className="text-xs font-semibold text-text">FAQ & Rujukan Fatwa Syariat</span>
                        </div>
                        <ChevronRight className="w-4 h-4 text-muted" />
                    </Link>

                    <Link
                        href="/ketentuan"
                        className="p-4 flex items-center justify-between hover:bg-surface transition-colors"
                    >
                        <div className="flex items-center gap-3">
                            <FileText className="w-4 h-4 text-muted" />
                            <span className="text-xs font-semibold text-text">Ketentuan Layanan</span>
                        </div>
                        <ChevronRight className="w-4 h-4 text-muted" />
                    </Link>

                    <button
                        onClick={handleLogout}
                        className="w-full p-4 flex items-center justify-between hover:bg-danger/5 transition-colors text-danger"
                    >
                        <div className="flex items-center gap-3">
                            <LogOut className="w-4 h-4" />
                            <span className="text-xs font-semibold">Keluar dari Akun</span>
                        </div>
                        <ChevronRight className="w-4 h-4" />
                    </button>
                </div>
            </div>
        </PenabungLayout>
    );
}

