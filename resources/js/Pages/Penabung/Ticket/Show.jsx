import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import PenabungLayout from '@/Layouts/PenabungLayout';
import { formatTanggalWaktu } from '@/Utils/format';
import { ArrowLeft, Send, CheckCircle2, AlertTriangle, ShieldCheck, User } from 'lucide-react';

export default function TicketShow({ ticket }) {
    const { data, setData, post, processing, reset, errors } = useForm({
        body: '',
    });

    const handleReply = (e) => {
        e.preventDefault();
        post(`/tiket/${ticket.id}/balas`, {
            onSuccess: () => reset('body'),
        });
    };

    return (
        <PenabungLayout>
            <Head title={`Tiket #${ticket.id}: ${ticket.subject} - YuKurban`} />

            <div className="max-w-3xl mx-auto space-y-6">
                <div>
                    <Link
                        href="/tiket"
                        className="inline-flex items-center gap-1.5 text-xs font-semibold text-green-600 hover:text-green-700 mb-2"
                    >
                        <ArrowLeft className="w-4 h-4" />
                        <span>Kembali ke Daftar Tiket</span>
                    </Link>

                    <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-border pb-3">
                        <div>
                            <div className="flex items-center gap-2">
                                <span className="text-xs text-muted font-mono font-bold">#{ticket.id}</span>
                                <span className={`px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider ${
                                    ticket.type === 'laporan_masalah' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'
                                }`}>
                                    {ticket.type === 'laporan_masalah' ? 'Laporan Masalah' : 'Pertanyaan Pra-Beli'}
                                </span>
                            </div>
                            <h1 className="text-lg sm:text-xl font-black text-green-900 mt-1">
                                {ticket.subject}
                            </h1>
                        </div>

                        <span className={`px-2.5 py-1 rounded text-xs font-bold capitalize self-start sm:self-auto ${
                            ticket.status === 'dibuka' ? 'bg-amber-100 text-amber-800' :
                            ticket.status === 'ditanggapi' ? 'bg-blue-100 text-blue-800' :
                            ticket.status === 'selesai' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                        }`}>
                            Status: {ticket.status}
                        </span>
                    </div>
                </div>

                {/* Putusan Admin jika status diputuskan / selesai */}
                {ticket.decision && (
                    <div className="p-4 bg-green-50 border border-green-200 rounded-card space-y-2">
                        <div className="flex items-center gap-2">
                            <CheckCircle2 className="w-4 h-4 text-green-600" />
                            <h3 className="text-xs font-bold text-green-900 uppercase tracking-wider">
                                Keputusan Tim YuKurban
                            </h3>
                        </div>
                        <p className="text-xs font-bold text-green-800 capitalize">
                            Keputusan: {ticket.decision.replace('_', ' ')}
                        </p>
                        {ticket.decision_note && (
                            <p className="text-xs text-green-900 bg-white p-3 rounded border border-green-200 leading-relaxed">
                                {ticket.decision_note}
                            </p>
                        )}
                        <span className="text-[10px] text-muted block">
                            Diputuskan pada: {formatTanggalWaktu(ticket.decided_at)}
                        </span>
                    </div>
                )}

                {/* Thread Percakapan */}
                <div className="space-y-4">
                    <h3 className="text-xs font-bold text-green-900 uppercase tracking-wider text-[11px]">
                        Diskusi Tiket ({ticket.replies?.length || 0})
                    </h3>

                    <div className="space-y-3">
                        {ticket.replies?.map((rep) => {
                            const isAdmin = rep.user?.role === 'admin';
                            return (
                                <div
                                    key={rep.id}
                                    className={`p-4 rounded-card border shadow-sm space-y-2 ${
                                        isAdmin ? 'bg-green-50/50 border-green-200 ml-4 sm:ml-8' : 'bg-white border-border mr-4 sm:mr-8'
                                    }`}
                                >
                                    <div className="flex items-center justify-between border-b border-border/50 pb-2">
                                        <div className="flex items-center gap-2">
                                            <div className={`w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold ${
                                                isAdmin ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700'
                                            }`}>
                                                {isAdmin ? 'A' : 'U'}
                                            </div>
                                            <span className="text-xs font-bold text-green-900">
                                                {rep.user?.name} {isAdmin && <span className="text-[10px] text-green-700 font-semibold">(Admin)</span>}
                                            </span>
                                        </div>
                                        <span className="text-[10px] text-muted">
                                            {formatTanggalWaktu(rep.created_at)}
                                        </span>
                                    </div>
                                    <p className="text-xs text-text leading-relaxed whitespace-pre-line">
                                        {rep.body}
                                    </p>
                                </div>
                            );
                        })}
                    </div>
                </div>

                {/* Form Kirim Balasan jika tiket belum ditutup */}
                {ticket.status !== 'selesai' && (
                    <form onSubmit={handleReply} className="bg-white border border-border rounded-card p-5 shadow-sm space-y-3">
                        <label className="text-xs font-semibold text-text block">
                            Tulis Balasan
                        </label>
                        <textarea
                            value={data.body}
                            onChange={(e) => setData('body', e.target.value)}
                            rows={3}
                            placeholder="Tulis balasan Anda..."
                            className="w-full text-xs rounded-btn border border-border p-2.5 focus:ring-2 focus:ring-green-600 focus:outline-none"
                            required
                        />
                        {errors.body && <p className="text-xs text-red-600">{errors.body}</p>}
                        <div className="flex justify-end">
                            <button
                                type="submit"
                                disabled={processing}
                                className="inline-flex items-center gap-1.5 px-4 py-2 rounded-btn bg-green-600 text-white font-bold text-xs hover:bg-green-700 shadow-sm transition-colors"
                            >
                                <Send className="w-3.5 h-3.5" />
                                <span>{processing ? 'Mengirim...' : 'Kirim Balasan'}</span>
                            </button>
                        </div>
                    </form>
                )}
            </div>
        </PenabungLayout>
    );
}
