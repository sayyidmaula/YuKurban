import React from 'react';
import { Head } from '@inertiajs/react';
import PeternakLayout from '@/Layouts/PeternakLayout';
import SimulationBanner from '@/Components/SimulationBanner';
import { formatRupiah, formatTanggalWaktu } from '@/Utils/format';
import { DollarSign, Clock, CheckCircle2, AlertCircle, ArrowUpRight } from 'lucide-react';

export default function PeternakPayoutIndex({ payouts, summary }) {
    const payoutList = payouts?.data || [];

    return (
        <PeternakLayout>
            <Head title="Pencairan Dana (Payout) - YuKurban Mitra" />

            <div className="max-w-4xl mx-auto space-y-6">
                <SimulationBanner />

                <div>
                    <h1 className="text-xl sm:text-2xl font-extrabold text-green-900">
                        Pencairan Dana Peternak (Payout)
                    </h1>
                    <p className="text-xs text-muted mt-1">
                        Transparansi bagi hasil penjualan hewan kurban dan komisi platform platform (BR-09, BR-13).
                    </p>
                </div>

                {/* Summary Cards */}
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div className="bg-white border border-border rounded-card p-5 shadow-sm space-y-2">
                        <div className="flex items-center justify-between">
                            <span className="text-xs text-muted font-semibold">Dana Tertahan (Escrow)</span>
                            <Clock className="w-4 h-4 text-amber-500" />
                        </div>
                        <div className="text-2xl font-black text-amber-600 tabular-nums">
                            {formatRupiah(summary.total_tertahan)}
                        </div>
                        <p className="text-[11px] text-muted">
                            Dana ditahan sementara sampai tahap kurban selesai terverifikasi.
                        </p>
                    </div>

                    <div className="bg-white border border-border rounded-card p-5 shadow-sm space-y-2">
                        <div className="flex items-center justify-between">
                            <span className="text-xs text-muted font-semibold">Total Dana Telah Dibayarkan</span>
                            <CheckCircle2 className="w-4 h-4 text-green-600" />
                        </div>
                        <div className="text-2xl font-black text-green-900 tabular-nums">
                            {formatRupiah(summary.total_dibayarkan)}
                        </div>
                        <p className="text-[11px] text-muted">
                            Total dana payout simulasi yang telah ditransfer ke rekening peternak.
                        </p>
                    </div>
                </div>

                {/* Table Payout Records */}
                <div className="bg-white border border-border rounded-card shadow-sm overflow-hidden">
                    <div className="p-4 border-b border-border">
                        <h3 className="text-xs font-bold text-green-900 uppercase tracking-wider">
                            Riwayat Pembayaran Payout
                        </h3>
                    </div>

                    {payoutList.length === 0 ? (
                        <div className="p-8 text-center text-xs text-muted">
                            Belum ada catatan pencairan dana payout.
                        </div>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="w-full text-left text-xs">
                                <thead className="bg-surface text-muted uppercase text-[10px] font-bold border-b border-border">
                                    <tr>
                                        <th className="p-3.5">Pesanan</th>
                                        <th className="p-3.5">Hewan</th>
                                        <th className="p-3.5 text-right">Harga Hewan</th>
                                        <th className="p-3.5 text-right">Komisi Platform</th>
                                        <th className="p-3.5 text-right">Bersih Peternak</th>
                                        <th className="p-3.5 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-border">
                                    {payoutList.map((p) => {
                                        const gross = (p.amount || 0) + (p.commission || 0);
                                        return (
                                            <tr key={p.id} className="hover:bg-surface/50">
                                                <td className="p-3.5 font-mono font-bold text-green-900">
                                                    {p.order?.order_no || '-'}
                                                </td>
                                                <td className="p-3.5">
                                                    {p.order?.animal?.title || '-'}
                                                </td>
                                                <td className="p-3.5 text-right tabular-nums text-muted">
                                                    {formatRupiah(gross)}
                                                </td>
                                                <td className="p-3.5 text-right tabular-nums text-red-600">
                                                    -{formatRupiah(p.commission)}
                                                </td>
                                                <td className="p-3.5 text-right tabular-nums font-bold text-green-900">
                                                    {formatRupiah(p.amount)}
                                                </td>
                                                <td className="p-3.5 text-center">
                                                    <span className={`px-2 py-0.5 rounded text-[10px] font-bold capitalize ${
                                                        p.status === 'tertahan' ? 'bg-amber-100 text-amber-800' :
                                                        p.status === 'dibayarkan' ? 'bg-green-100 text-green-800' :
                                                        'bg-red-100 text-red-800'
                                                    }`}>
                                                        {p.status}
                                                    </span>
                                                </td>
                                            </tr>
                                        );
                                    })}
                                </tbody>
                            </table>
                        </div>
                    )}
                </div>
            </div>
        </PeternakLayout>
    );
}

