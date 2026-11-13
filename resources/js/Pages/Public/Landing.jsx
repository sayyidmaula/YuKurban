import React from 'react';
import { Head, Link } from '@inertiajs/react';
import PublicLayout from '@/Layouts/PublicLayout';
import { ShieldCheck, Target, HeartHandshake, Truck, ArrowRight, CheckCircle2, FileText, Sparkles } from 'lucide-react';

export default function Landing() {
    return (
        <PublicLayout>
            <Head title="YuKurban - Platform Tabungan dan Pembelian Kurban Amanah" />

            {/* Hero Section */}
            <section className="relative overflow-hidden bg-gradient-to-b from-green-900 via-green-900 to-green-700 text-white pt-12 pb-20 px-4 sm:px-6 lg:px-8">
                {/* Subtle Islamic Geometric Pattern overlay */}
                <div className="absolute inset-0 opacity-5 pointer-events-none bg-[radial-gradient(#C9A227_1px,transparent_1px)] [background-size:16px_16px]"></div>

                <div className="max-w-5xl mx-auto text-center relative z-10">
                    <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-green-500/20 border border-gold-500/30 text-gold-300 text-xs font-semibold mb-6">
                        <Sparkles className="w-3.5 h-3.5 text-gold-500" />
                        <span>Perencanaan Kurban Berbasis Syariat & Teknologi Amanah</span>
                    </div>

                    <h1 className="text-3xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-tight max-w-4xl mx-auto">
                        Niatkan Kurban Terbaikmu, <span className="text-gold-500">Rencanakan</span> Sejak Dini
                    </h1>

                    <p className="mt-5 text-sm sm:text-lg text-green-100/90 max-w-2xl mx-auto leading-relaxed">
                        Tabungan terpisah tanpa terikat hewan tertentu. Saat saldo cukup dan musim kurban tiba, pilih langsung hewan fisik dari peternak terverifikasi dengan rincian biaya yang transparan.
                    </p>

                    <div className="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3 sm:gap-4 max-w-md mx-auto">
                        <Link
                            href="/daftar"
                            className="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-btn bg-gold-500 text-green-900 font-bold text-base hover:bg-gold-300 shadow-md transition-all"
                        >
                            <span>Mulai Rencana Tabungan</span>
                            <ArrowRight className="w-4 h-4" />
                        </Link>
                        <Link
                            href="/katalog"
                            className="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3.5 rounded-btn bg-white/10 text-white font-semibold text-base hover:bg-white/20 border border-white/20 transition-all"
                        >
                            Pratinjau Katalog
                        </Link>
                    </div>

                    {/* Quick Trust Highlights */}
                    <div className="mt-12 pt-8 border-t border-green-700/60 grid grid-cols-2 md:grid-cols-4 gap-4 text-left">
                        <div className="flex items-start gap-3">
                            <ShieldCheck className="w-5 h-5 text-gold-500 flex-shrink-0 mt-0.5" />
                            <div>
                                <h4 className="text-xs font-bold text-white">Ledger Append-Only</h4>
                                <p className="text-[11px] text-green-100/70">Saldo tidak dapat dimanipulasi</p>
                            </div>
                        </div>
                        <div className="flex items-start gap-3">
                            <Target className="w-5 h-5 text-gold-500 flex-shrink-0 mt-0.5" />
                            <div>
                                <h4 className="text-xs font-bold text-white">Target Fleksibel</h4>
                                <p className="text-[11px] text-green-100/70">Cicilan simulasi per bulan</p>
                            </div>
                        </div>
                        <div className="flex items-start gap-3">
                            <HeartHandshake className="w-5 h-5 text-gold-500 flex-shrink-0 mt-0.5" />
                            <div>
                                <h4 className="text-xs font-bold text-white">Sembelih Tim</h4>
                                <p className="text-[11px] text-green-100/70">Akad wakalah & bukti foto/video</p>
                            </div>
                        </div>
                        <div className="flex items-start gap-3">
                            <Truck className="w-5 h-5 text-gold-500 flex-shrink-0 mt-0.5" />
                            <div>
                                <h4 className="text-xs font-bold text-white">Kirim Hewan Hidup</h4>
                                <p className="text-[11px] text-green-100/70">Diantar armada aman ke tujuan</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* Core Features */}
            <section className="py-16 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
                <div className="text-center max-w-2xl mx-auto mb-12">
                    <span className="text-xs font-bold uppercase tracking-wider text-green-600 bg-green-50 px-3 py-1 rounded-full">
                        Keunggulan YuKurban
                    </span>
                    <h2 className="text-2xl sm:text-3xl font-extrabold text-green-900 mt-3">
                        Menghubungkan Penabung, Peternak, dan Pengelola
                    </h2>
                    <p className="text-sm text-muted mt-2">
                        Prinsip amanah diwujudkan lewat pencatatan mutlak dan verifikasi ketat di setiap langkah.
                    </p>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div className="bg-white p-6 rounded-card border border-border shadow-sm flex flex-col justify-between">
                        <div>
                            <div className="w-12 h-12 rounded-2xl bg-green-50 text-green-600 flex items-center justify-center font-bold mb-4">
                                <Target className="w-6 h-6" />
                            </div>
                            <h3 className="text-lg font-bold text-green-900 mb-2">Goal Setting Tabungan</h3>
                            <p className="text-xs text-muted leading-relaxed">
                                Tentukan target tahun Idul Adha, tingkat harga kurban (ekonomis, standar, premium), serta nominal cicilan bulanan yang sesuai kemampuan Anda.
                            </p>
                        </div>
                        <div className="mt-6 pt-4 border-t border-border flex items-center text-xs font-semibold text-green-600">
                            <span>Bisa ditarik kapan saja</span>
                        </div>
                    </div>

                    <div className="bg-white p-6 rounded-card border border-border shadow-sm flex flex-col justify-between">
                        <div>
                            <div className="w-12 h-12 rounded-2xl bg-gold-100 text-gold-600 flex items-center justify-center font-bold mb-4">
                                <ShieldCheck className="w-6 h-6" />
                            </div>
                            <h3 className="text-lg font-bold text-green-900 mb-2">Hewan Terverifikasi Admin</h3>
                            <p className="text-xs text-muted leading-relaxed">
                                Peternak lokal melewati kurasi dan hewan diperiksa kelayakan umur, bobot, catatan kesehatan, 3 sudut foto, serta video fisik sebelum tayang.
                            </p>
                        </div>
                        <div className="mt-6 pt-4 border-t border-border flex items-center text-xs font-semibold text-gold-600">
                            <span>Satu baris = Satu hewan nyata</span>
                        </div>
                    </div>

                    <div className="bg-white p-6 rounded-card border border-border shadow-sm flex flex-col justify-between">
                        <div>
                            <div className="w-12 h-12 rounded-2xl bg-green-50 text-green-600 flex items-center justify-center font-bold mb-4">
                                <FileText className="w-6 h-6" />
                            </div>
                            <h3 className="text-lg font-bold text-green-900 mb-2">Bukti dan Transparansi</h3>
                            <p className="text-xs text-muted leading-relaxed">
                                Rincian harga hewan, ongkir zona, serta ujrah sembelih/distribusi tampil jelas tanpa biaya tersembunyi. Foto/video bukti diunggah di tiap tahapan.
                            </p>
                        </div>
                        <div className="mt-6 pt-4 border-t border-border flex items-center text-xs font-semibold text-green-600">
                            <span>Komisi platform transparan</span>
                        </div>
                    </div>
                </div>
            </section>

            {/* How it works */}
            <section className="bg-white py-16 px-4 sm:px-6 lg:px-8 border-y border-border">
                <div className="max-w-5xl mx-auto">
                    <div className="text-center mb-12">
                        <h2 className="text-2xl sm:text-3xl font-extrabold text-green-900">Alur Mudah 4 Langkah</h2>
                        <p className="text-xs text-muted mt-2">Dari rencana menabung hingga pelaporan penyaluran daging kurban</p>
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div className="p-4 rounded-card bg-surface border border-border text-center">
                            <div className="w-10 h-10 rounded-full bg-green-600 text-white font-bold flex items-center justify-center mx-auto mb-3 text-sm">
                                1
                            </div>
                            <h4 className="font-bold text-sm text-green-900 mb-1">Rencanakan</h4>
                            <p className="text-xs text-muted leading-relaxed">Buat rencana tabungan kurban dan setorkan dana secara bertahap via virtual account simulasi.</p>
                        </div>

                        <div className="p-4 rounded-card bg-surface border border-border text-center">
                            <div className="w-10 h-10 rounded-full bg-green-600 text-white font-bold flex items-center justify-center mx-auto mb-3 text-sm">
                                2
                            </div>
                            <h4 className="font-bold text-sm text-green-900 mb-1">Pilih Hewan</h4>
                            <p className="text-xs text-muted leading-relaxed">Buka katalog hewan saat jendela pembelian dibuka, pilih kambing/domba/sapi sesuai selera.</p>
                        </div>

                        <div className="p-4 rounded-card bg-surface border border-border text-center">
                            <div className="w-10 h-10 rounded-full bg-green-600 text-white font-bold flex items-center justify-center mx-auto mb-3 text-sm">
                                3
                            </div>
                            <h4 className="font-bold text-sm text-green-900 mb-1">Pilih Metode</h4>
                            <p className="text-xs text-muted leading-relaxed">Pilih Sembelih Tim (akad wakalah disalurkan) atau Kirim Hewan hidup ke alamat tujuan Anda.</p>
                        </div>

                        <div className="p-4 rounded-card bg-surface border border-border text-center">
                            <div className="w-10 h-10 rounded-full bg-green-600 text-white font-bold flex items-center justify-center mx-auto mb-3 text-sm">
                                4
                            </div>
                            <h4 className="font-bold text-sm text-green-900 mb-1">Pantau & Bukti</h4>
                            <p className="text-xs text-muted leading-relaxed">Dapatkan timeline status real-time lengkap dengan foto penyembelihan atau bukti terima.</p>
                        </div>
                    </div>
                </div>
            </section>

            {/* Sharia Reference Section */}
            <section className="py-16 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto text-center">
                <div className="bg-green-50 border border-green-100 rounded-card p-6 sm:p-8">
                    <h3 className="text-lg font-bold text-green-900 mb-2">Prinsip Fikih & Pemisahan Akad</h3>
                    <p className="text-xs text-muted leading-relaxed mb-4">
                        YuKurban memisahkan <strong>tabungan</strong> dari <strong>akad jual beli</strong>: akad terjadi saat membeli hewan yang nyata dengan harga hari itu, bukan saat mulai menabung. Konsep ini merujuk pada fatwa DSN-MUI tentang akad jual beli (No. 110/2017), wakalah bil ujrah (No. 113/2017), marketplace (No. 144/2021), dan samsarah (No. 151/2022).
                    </p>
                    <Link
                        href="/faq"
                        className="inline-flex items-center text-xs font-bold text-green-700 hover:text-green-800 underline"
                    >
                        Pelajari selengkapnya di FAQ & Dasar Syariat →
                    </Link>
                </div>
            </section>
        </PublicLayout>
    );
}

