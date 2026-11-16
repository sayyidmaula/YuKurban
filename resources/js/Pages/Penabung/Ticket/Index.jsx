import React from 'react';
import { Head, Link } from '@inertiajs/react';
import PenabungLayout from '@/Layouts/PenabungLayout';
import { formatTanggalWaktu } from '@/Utils/format';
import { MessageSquare, Plus, ArrowRight, ShieldAlert, HelpCircle } from 'lucide-react';

export default function TicketIndex({ tickets }) {
    const ticketList = tickets?.data || [];

    return (
        <PenabungLayout>
            <Head title="Pusat Bantuan & Tiket - YuKurban" />

            <div className="max-w-4xl mx-auto space-y-6">
                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h1 className="text-xl sm:text-2xl font-extrabold text-green-900 tracking-tight">
                            Pusat Bantuan & Tiket
                        </h1>
                        <p className="text-xs text-muted mt-1">
                            Ajukan pertanyaan pra-beli atau laporkan kendala terkait pesanan hewan kurban Anda.
                        </p>
                    </div>

                    <Link
                        href="/tiket/buat"
                        className="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-btn bg-green-600 text-white font-bold text-xs hover:bg-green-700 shadow-sm transition-colors self-start sm:self-auto"
                    >
                        <Plus className="w-4 h-4" />
                        <span>Buka Tiket Baru</span>
                    </Link>
                </div>

                {ticketList.length === 0 ? (
                    <div className="bg-white border border-border rounded-card p-10 text-center space-y-3 shadow-sm">
                        <div className="w-12 h-12 rounded-full bg-green-50 text-green-600 mx-auto flex items-center justify-center">
                            <MessageSquare className="w-6 h-6" />
                        </div>
                        <h3 className="text-sm font-bold text-green-900">Belum Ada Tiket</h3>
                        <p className="text-xs text-muted max-w-sm mx-auto">
                            Jika Anda memiliki pertanyaan tentang hewan kurban atau ingin melaporkan masalah pesanan, silakan buka tiket baru.
                        </p>
                    </div>
                ) : (
                    <div className="space-y-3">
                        {ticketList.map((ticket) => (
                            <Link
                                key={ticket.id}
                                href={`/tiket/${ticket.id}`}
                                className="block bg-white border border-border rounded-card p-4 hover:border-green-600 transition-colors shadow-sm"
                            >
                                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-border pb-2.5">
                                    <div className="flex items-center gap-2">
                                        <span className={`px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider ${
                                            ticket.type === 'laporan_masalah' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'
                                        }`}>
                                            {ticket.type === 'laporan_masalah' ? 'Laporan Masalah' : 'Pertanyaan Pra-Beli'}
                                        </span>
                                        <span className="text-xs text-muted font-medium">#{ticket.id}</span>
                                    </div>

                                    <span className={`px-2 py-0.5 rounded text-xs font-semibold capitalize self-start sm:self-auto ${
                                        ticket.status === 'dibuka' ? 'bg-amber-100 text-amber-800' :
                                        ticket.status === 'ditanggapi' ? 'bg-blue-100 text-blue-800' :
                                        ticket.status === 'selesai' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                                    }`}>
                                        {ticket.status}
                                    </span>
                                </div>

                                <div className="mt-3 flex items-center justify-between gap-4">
                                    <div>
                                        <h3 className="text-sm font-bold text-green-900 line-clamp-1">
                                            {ticket.subject}
                                        </h3>
                                        <p className="text-xs text-muted mt-0.5">
                                            Kategori: {ticket.category} • Dibuat: {formatTanggalWaktu(ticket.created_at)}
                                        </p>
                                    </div>
                                    <ArrowRight className="w-4 h-4 text-muted flex-shrink-0" />
                                </div>
                            </Link>
                        ))}
                    </div>
                )}
            </div>
        </PenabungLayout>
    );
}
