import React, { useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import PeternakLayout from '@/Layouts/PeternakLayout';
import { formatTanggalWaktu } from '@/Utils/format';
import { HelpCircle, Send, MessageSquare, Plus, ChevronDown, ChevronUp } from 'lucide-react';

export default function PeternakBantuan({ tickets }) {
    const ticketList = tickets?.data || [];
    const [showForm, setShowForm] = useState(false);

    const { data, setData, post, processing, reset, errors } = useForm({
        category: 'Operasional Kandang',
        subject: '',
        body: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/peternak/bantuan', {
            onSuccess: () => {
                reset();
                setShowForm(false);
            },
        });
    };

    return (
        <PeternakLayout>
            <Head title="Pusat Bantuan Mitra Peternak - YuKurban" />

            <div className="max-w-3xl mx-auto space-y-6">
                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h1 className="text-xl sm:text-2xl font-extrabold text-green-900 tracking-tight">
                            Pusat Bantuan Mitra Peternak
                        </h1>
                        <p className="text-xs text-muted mt-1">
                            Sampaikan kendala teknis, pertanyaan pencairan payout, atau verifikasi data kandang Anda.
                        </p>
                    </div>

                    <button
                        onClick={() => setShowForm(!showForm)}
                        className="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-btn bg-green-600 text-white font-bold text-xs hover:bg-green-700 shadow-sm transition-colors self-start sm:self-auto"
                    >
                        <Plus className="w-4 h-4" />
                        <span>{showForm ? 'Tutup Formulir' : 'Ajukan Bantuan Baru'}</span>
                    </button>
                </div>

                {/* Form Bantuan Baru */}
                {showForm && (
                    <form onSubmit={handleSubmit} className="bg-white border border-green-600 rounded-card p-6 shadow-sm space-y-4">
                        <h3 className="text-sm font-bold text-green-900 border-b border-border pb-2">
                            Formulir Bantuan Mitra Peternak
                        </h3>

                        <div>
                            <label className="text-xs font-semibold text-text block mb-1">Kategori Masalah</label>
                            <select
                                value={data.category}
                                onChange={(e) => setData('category', e.target.value)}
                                className="w-full text-xs rounded-btn border border-border p-2.5 focus:ring-2 focus:ring-green-600 focus:outline-none"
                            >
                                <option value="Operasional Kandang">Operasional Kandang & Hewan</option>
                                <option value="Pencairan Payout">Pencairan Dana Payout</option>
                                <option value="Verifikasi & Kurasi">Verifikasi Profil & Kurasi Hewan</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>

                        <div>
                            <label className="text-xs font-semibold text-text block mb-1">Subjek / Topik</label>
                            <input
                                type="text"
                                value={data.subject}
                                onChange={(e) => setData('subject', e.target.value)}
                                placeholder="Contoh: Pertanyaan jadwal penjemputan kambing pesanan #ORD..."
                                className="w-full text-xs rounded-btn border border-border p-2.5 focus:ring-2 focus:ring-green-600 focus:outline-none"
                                required
                            />
                            {errors.subject && <p className="text-xs text-red-600 mt-1">{errors.subject}</p>}
                        </div>

                        <div>
                            <label className="text-xs font-semibold text-text block mb-1">Rincian Pertanyaan / Kendala</label>
                            <textarea
                                value={data.body}
                                onChange={(e) => setData('body', e.target.value)}
                                rows={3}
                                placeholder="Jelaskan kendala Anda selengkap mungkin..."
                                className="w-full text-xs rounded-btn border border-border p-2.5 focus:ring-2 focus:ring-green-600 focus:outline-none"
                                required
                            />
                            {errors.body && <p className="text-xs text-red-600 mt-1">{errors.body}</p>}
                        </div>

                        <div className="flex justify-end gap-2">
                            <button
                                type="button"
                                onClick={() => setShowForm(false)}
                                className="px-4 py-2 border border-border rounded-btn text-xs font-semibold text-muted hover:bg-surface"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                disabled={processing}
                                className="px-5 py-2 rounded-btn bg-green-600 text-white font-bold text-xs hover:bg-green-700 shadow-sm flex items-center gap-1.5"
                            >
                                <Send className="w-3.5 h-3.5" />
                                <span>{processing ? 'Mengirim...' : 'Kirim Permohonan'}</span>
                            </button>
                        </div>
                    </form>
                )}

                {/* Daftar Tiket Peternak */}
                <div className="space-y-3">
                    <h3 className="text-xs font-bold text-green-900 uppercase tracking-wider text-[11px]">
                        Riwayat Tiket Bantuan ({ticketList.length})
                    </h3>

                    {ticketList.length === 0 ? (
                        <div className="bg-white border border-border rounded-card p-8 text-center space-y-2 shadow-sm">
                            <div className="w-10 h-10 rounded-full bg-green-50 text-green-600 mx-auto flex items-center justify-center">
                                <HelpCircle className="w-5 h-5" />
                            </div>
                            <h4 className="text-sm font-bold text-green-900">Belum Ada Tiket Bantuan</h4>
                            <p className="text-xs text-muted">
                                Ajukan pertanyaan atau kendala seputar operasional kandang Anda melalui tombol di atas.
                            </p>
                        </div>
                    ) : (
                        ticketList.map((t) => (
                            <div key={t.id} className="bg-white border border-border rounded-card p-4 shadow-sm space-y-2">
                                <div className="flex items-center justify-between border-b border-border pb-2">
                                    <div>
                                        <span className="text-[10px] font-mono text-muted uppercase">#{t.id} • {t.category}</span>
                                        <h4 className="text-xs font-bold text-green-900">{t.subject}</h4>
                                    </div>
                                    <span className={`px-2 py-0.5 rounded text-[10px] font-bold capitalize ${
                                        t.status === 'dibuka' ? 'bg-amber-100 text-amber-800' :
                                        t.status === 'ditanggapi' ? 'bg-blue-100 text-blue-800' :
                                        t.status === 'selesai' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                                    }`}>
                                        {t.status}
                                    </span>
                                </div>
                                <span className="text-[10px] text-muted block">
                                    Diajukan: {formatTanggalWaktu(t.created_at)}
                                </span>
                            </div>
                        ))
                    )}
                </div>
            </div>
        </PeternakLayout>
    );
}
