import React from 'react';
import { AlertCircle } from 'lucide-react';

export default function SimulationBanner({ className = '' }) {
    return (
        <div className={`bg-gold-100 border-b border-gold-300 text-gold-600 px-4 py-1.5 text-xs font-semibold flex items-center justify-center gap-2 ${className}`}>
            <AlertCircle className="w-3.5 h-3.5 flex-shrink-0 text-gold-600" />
            <span>MODE SIMULASI — Pembayaran dan pencairan dana adalah simulasi untuk kebutuhan demonstrasi, bukan transaksi keuangan riil.</span>
        </div>
    );
}

