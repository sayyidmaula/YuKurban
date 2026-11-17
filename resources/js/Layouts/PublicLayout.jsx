import React, { useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import { Menu, X, ArrowRight } from 'lucide-react';
export default function PublicLayout({ children }) {
    const { auth } = usePage().props;
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

    return (
        <div className="min-h-screen bg-surface flex flex-col text-text">
            {/* Public Header */}
            <header className="sticky top-0 z-30 bg-white/95 backdrop-blur border-b border-border shadow-sm">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                    <Link href="/" className="flex items-center gap-2">
                        <div className="w-9 h-9 rounded-xl bg-green-600 flex items-center justify-center text-white font-bold text-lg shadow-sm">
                            YK
                        </div>
                        <div className="flex flex-col">
                            <span className="font-extrabold text-lg text-green-900 tracking-tight leading-none">
                                Yu<span className="text-gold-500">Kurban</span>
                            </span>
                            <span className="text-[10px] text-muted font-medium">Amanah & Transparan</span>
                        </div>
                    </Link>

                    {/* Desktop Navigation */}
                    <nav className="hidden md:flex items-center gap-6 text-sm font-medium text-muted">
                        <Link href="/katalog" className="hover:text-green-700 transition-colors">
                            Katalog Hewan
                        </Link>
                        <Link href="/faq" className="hover:text-green-700 transition-colors">
                            FAQ & Syariat
                        </Link>
                        <Link href="/ketentuan" className="hover:text-green-700 transition-colors">
                            Ketentuan
                        </Link>
                        <Link href="/hubungi" className="hover:text-green-700 transition-colors">
                            Hubungi Kami
                        </Link>
                    </nav>

                    {/* Auth CTA */}
                    <div className="hidden md:flex items-center gap-3">
                        {auth?.user ? (
                            <Link
                                href={auth.user.role === 'peternak' ? '/peternak/beranda' : auth.user.role === 'admin' ? '/admin' : '/beranda'}
                                className="inline-flex items-center gap-2 px-4 py-2 rounded-btn bg-green-600 text-white font-semibold text-sm hover:bg-green-700 shadow-sm transition-colors"
                            >
                                <span>Buka Aplikasi</span>
                                <ArrowRight className="w-4 h-4" />
                            </Link>
                        ) : (
                            <>
                                <Link
                                    href="/masuk"
                                    className="px-4 py-2 rounded-btn text-sm font-semibold text-green-700 hover:bg-green-50 transition-colors"
                                >
                                    Masuk
                                </Link>
                                <Link
                                    href="/daftar"
                                    className="px-4 py-2 rounded-btn text-sm font-semibold bg-green-600 text-white hover:bg-green-700 shadow-sm transition-colors"
                                >
                                    Daftar Sekarang
                                </Link>
                            </>
                        )}
                    </div>

                    {/* Mobile Hamburger Button */}
                    <button
                        onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
                        className="md:hidden p-2 text-muted hover:text-green-600 focus:outline-none"
                        aria-label="Toggle Menu"
                    >
                        {mobileMenuOpen ? <X className="w-6 h-6" /> : <Menu className="w-6 h-6" />}
                    </button>
                </div>

                {/* Mobile Dropdown Menu */}
                {mobileMenuOpen && (
                    <div className="md:hidden border-b border-border bg-white px-4 pt-2 pb-6 space-y-3">
                        <Link
                            href="/katalog"
                            className="block py-2 text-base font-medium text-text hover:text-green-600"
                            onClick={() => setMobileMenuOpen(false)}
                        >
                            Katalog Hewan
                        </Link>
                        <Link
                            href="/faq"
                            className="block py-2 text-base font-medium text-text hover:text-green-600"
                            onClick={() => setMobileMenuOpen(false)}
                        >
                            FAQ & Syariat
                        </Link>
                        <Link
                            href="/ketentuan"
                            className="block py-2 text-base font-medium text-text hover:text-green-600"
                            onClick={() => setMobileMenuOpen(false)}
                        >
                            Ketentuan
                        </Link>
                        <Link
                            href="/hubungi"
                            className="block py-2 text-base font-medium text-text hover:text-green-600"
                            onClick={() => setMobileMenuOpen(false)}
                        >
                            Hubungi Kami
                        </Link>
                        <div className="pt-3 border-t border-border flex flex-col gap-2">
                            {auth?.user ? (
                                <Link
                                    href={auth.user.role === 'peternak' ? '/peternak/beranda' : auth.user.role === 'admin' ? '/admin' : '/beranda'}
                                    className="w-full text-center py-2.5 rounded-btn bg-green-600 text-white font-semibold text-sm"
                                    onClick={() => setMobileMenuOpen(false)}
                                >
                                    Buka Aplikasi
                                </Link>
                            ) : (
                                <>
                                    <Link
                                        href="/masuk"
                                        className="w-full text-center py-2.5 rounded-btn border border-green-600 text-green-700 font-semibold text-sm"
                                        onClick={() => setMobileMenuOpen(false)}
                                    >
                                        Masuk
                                    </Link>
                                    <Link
                                        href="/daftar"
                                        className="w-full text-center py-2.5 rounded-btn bg-green-600 text-white font-semibold text-sm"
                                        onClick={() => setMobileMenuOpen(false)}
                                    >
                                        Daftar Sekarang
                                    </Link>
                                </>
                            )}
                        </div>
                    </div>
                )}
            </header>

            {/* Main Content */}
            <main className="flex-1 w-full">
                {children}
            </main>

            {/* Public Footer */}
            <footer className="bg-green-900 text-white border-t border-green-700/50 mt-auto">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
                    <div className="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                        <div className="md:col-span-2">
                            <div className="flex items-center gap-2 mb-3">
                                <div className="w-8 h-8 rounded-lg bg-green-600 flex items-center justify-center text-white font-bold text-base">
                                    YK
                                </div>
                                <span className="font-extrabold text-xl text-white tracking-tight">
                                    Yu<span className="text-gold-500">Kurban</span>
                                </span>
                            </div>
                            <p className="text-sm text-green-100/80 max-w-md leading-relaxed">
                                Platform perencanaan tabungan kurban dan pembelian hewan kurban terverifikasi langsung dari peternak lokal dengan akad transparan dan amanah.
                            </p>
                        </div>
                        <div>
                            <h4 className="text-sm font-semibold text-gold-300 uppercase tracking-wider mb-3">Tautan Cepat</h4>
                            <ul className="space-y-2 text-sm text-green-100/80">
                                <li><Link href="/katalog" className="hover:text-white">Katalog Hewan</Link></li>
                                <li><Link href="/faq" className="hover:text-white">FAQ & Rujukan Fatwa</Link></li>
                                <li><Link href="/ketentuan" className="hover:text-white">Ketentuan Layanan</Link></li>
                                <li><Link href="/hubungi" className="hover:text-white">Hubungi Kami</Link></li>
                            </ul>
                        </div>
                        <div>
                            <h4 className="text-sm font-semibold text-gold-300 uppercase tracking-wider mb-3">Informasi Legal</h4>
                            <p className="text-xs text-green-100/70 leading-relaxed">
                                YuKurban adalah sistem prototipe simulasi kurban untuk demonstrasi platform web responsif & PWA. Tidak ada penarikan dana riil.
                            </p>
                        </div>
                    </div>
                    <div className="pt-6 border-t border-green-700/50 flex flex-col sm:flex-row items-center justify-between text-xs text-green-100/60 gap-3">
                        <p>© 2026 YuKurban. Hak Cipta Dilindungi.</p>
                        <p>Waktu Server: Asia/Jakarta</p>
                    </div>
                </div>
            </footer>
        </div>
    );
}

