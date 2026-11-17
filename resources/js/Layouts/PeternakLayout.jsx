import React from 'react';
import { Link, usePage } from '@inertiajs/react';
import { Home, Compass, ClipboardList, CreditCard, User, HelpCircle, Bell } from 'lucide-react';

export default function PeternakLayout({ children, title }) {
    const { auth } = usePage().props;
    const currentUrl = usePage().url;

    const navItems = [
        { name: 'Beranda', href: '/peternak/beranda', icon: Home, active: currentUrl === '/peternak/beranda' },
        { name: 'Hewan', href: '/peternak/hewan', icon: Compass, active: currentUrl.startsWith('/peternak/hewan') },
        { name: 'Pesanan', href: '/peternak/pesanan', icon: ClipboardList, active: currentUrl.startsWith('/peternak/pesanan') },
        { name: 'Payout', href: '/peternak/pembayaran', icon: CreditCard, active: currentUrl.startsWith('/peternak/pembayaran') },
        { name: 'Akun', href: '/peternak/profil', icon: User, active: currentUrl.startsWith('/peternak/profil') || currentUrl.startsWith('/akun') },
    ];

    return (
        <div className="min-h-screen bg-surface flex flex-col text-text">
            {/* Header */}
            <header className="sticky top-0 z-30 bg-white border-b border-border shadow-sm">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                    <div className="flex items-center gap-6">
                        <Link href="/peternak/beranda" className="flex items-center gap-2">
                            <div className="w-9 h-9 rounded-xl bg-gold-600 flex items-center justify-center text-white font-bold text-lg shadow-sm">
                                YK
                            </div>
                            <div className="flex flex-col">
                                <div className="flex items-center gap-1.5">
                                    <span className="font-extrabold text-lg text-green-900 tracking-tight leading-none">
                                        Yu<span className="text-gold-500">Kurban</span>
                                    </span>
                                    <span className="px-1.5 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-700">
                                        Peternak
                                    </span>
                                </div>
                                <span className="text-[10px] text-muted font-medium">Mitra Peternak Kurban</span>
                            </div>
                        </Link>

                        {/* Desktop Nav Links */}
                        <nav className="hidden md:flex items-center gap-1 ml-6">
                            {navItems.map((item) => {
                                const Icon = item.icon;
                                return (
                                    <Link
                                        key={item.name}
                                        href={item.href}
                                        className={`flex items-center gap-2 px-3 py-2 rounded-btn text-sm font-medium transition-colors ${
                                            item.active
                                                ? 'bg-green-50 text-green-600 font-semibold'
                                                : 'text-muted hover:text-green-700 hover:bg-green-50/50'
                                        }`}
                                    >
                                        <Icon className="w-4 h-4" />
                                        <span>{item.name}</span>
                                    </Link>
                                );
                            })}
                        </nav>
                    </div>

                    <div className="flex items-center gap-3">
                        <Link
                            href="/peternak/bantuan"
                            className="p-2 rounded-full text-muted hover:text-green-600 hover:bg-green-50 transition-colors"
                            title="Bantuan Peternak"
                        >
                            <HelpCircle className="w-5 h-5" />
                        </Link>
                        <Link
                            href="/peternak/profil"
                            className="hidden sm:flex items-center gap-2 pl-2 border-l border-border text-sm font-medium text-text hover:text-green-600"
                        >
                            <div className="w-8 h-8 rounded-full bg-gold-100 text-gold-600 font-semibold flex items-center justify-center text-xs">
                                {auth.user?.name ? auth.user.name.charAt(0).toUpperCase() : 'P'}
                            </div>
                            <span className="max-w-[120px] truncate">{auth.user?.name || 'Peternak'}</span>
                        </Link>
                    </div>
                </div>
            </header>

            {/* Main Content Area */}
            <main className="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 pb-24 md:pb-8">
                {children}
            </main>

            {/* Desktop Footer */}
            <footer className="hidden md:block bg-white border-t border-border mt-auto py-6">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between text-xs text-muted gap-3">
                    <p>© 2026 YuKurban. Portal Mitra Peternak Amanah.</p>
                    <div className="flex items-center gap-4">
                        <Link href="/peternak/pengumuman" className="hover:text-green-600">Pengumuman</Link>
                        <Link href="/peternak/bantuan" className="hover:text-green-600">Bantuan</Link>
                    </div>
                </div>
            </footer>

            {/* Mobile Bottom Tab Bar */}
            <nav className="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-border shadow-lg">
                <div className="grid grid-cols-5 h-16 max-w-md mx-auto">
                    {navItems.map((item) => {
                        const Icon = item.icon;
                        return (
                            <Link
                                key={item.name}
                                href={item.href}
                                className={`flex flex-col items-center justify-center gap-1 text-[11px] font-medium transition-colors ${
                                    item.active
                                        ? 'text-green-600 font-semibold'
                                        : 'text-muted hover:text-green-700'
                                }`}
                            >
                                <Icon className={`w-5 h-5 ${item.active ? 'stroke-[2.5]' : 'stroke-[1.75]'}`} />
                                <span>{item.name}</span>
                            </Link>
                        );
                    })}
                </div>
            </nav>
        </div>
    );
}

