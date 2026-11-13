import React, { useState } from 'react';
import { Head } from '@inertiajs/react';
import PublicLayout from '@/Layouts/PublicLayout';
import { ChevronDown } from 'lucide-react';

export default function Faq() {
    const [openIndex, setOpenIndex] = useState(0);

    const faqs = [
        {
            q: 'Bagaimana status akad tabungan kurban di YuKurban?',
            a: 'Di YuKurban, tabungan dipisahkan secara tegas dari akad jual beli kurban. Penabung menabung dana secara mandiri tanpa mengikat hewan kurban tertentu di awal. Hal ini menghindari unsur ketidakpastian (gharar). Akad jual beli baru terjadi ketika penabung memilih hewan fisik yang nyata dari katalog dan melakukan pemesanan saat jendela pembelian kurban dibuka (merujuk DSN-MUI No. 110/2017 & No. 144/2021).',
        },
        {
            q: 'Apakah dana tabungan boleh ditarik sebelum kurban?',
            a: 'Boleh. Dana tabungan adalah amanah milik penabung seutuhnya dan tidak dipergunakan untuk kegiatan operasional platform atau pemutaran modal. Anda dapat mengajukan penarikan dana tabungan kapan saja sebelum hewan dibeli.',
        },
        {
            q: 'Apa perbedaan metode Sembelih Tim dan Kirim Hewan?',
            a: 'Metode Sembelih Tim menggunakan akad wakalah bil ujrah (DSN-MUI No. 113/2017), di mana penabung memberikan kuasa kepada tim YuKurban untuk menyembelihkan hewan dan menyalurkan daging kurban ke penerima manfaat. Sedangkan metode Kirim Hewan mengirimkan hewan hidup langsung ke alamat yang ditentukan penabung.',
        },
        {
            q: 'Bagaimana kriteria hewan kurban yang diizinkan tayang di katalog?',
            a: 'Semua hewan diperiksa berdasarkan umur minimum syariat (kambing/domba minimal 1 tahun, sapi minimal 2 tahun berdasarkan pengaturan rujukan resmi), bobot riil, status kesehatan, minimal 3 foto dari berbagai sudut, dan video kondisi fisik.',
        },
        {
            q: 'Apakah pembayaran di platform ini sungguhan?',
            a: 'Tidak. YuKurban saat ini merupakan sistem simulasi (prototipe MVP). Seluruh proses transfer virtual account, autodebet, dan payout mitra peternak dijalankan dalam mode simulasi menggunakan tombol simulasi.',
        },
        {
            q: 'Bagaimana jika hewan yang diterima cacat atau tidak sesuai?',
            a: 'Penabung berhak mengajukan tiket laporan masalah maksimal dalam 48 jam setelah hewan diterima atau laporan penyaluran terbit. Tim admin dapat memutuskan solusi berupa penggantian hewan, pengembalian dana (refund) penuh ke tabungan, atau penolakan dengan alasan yang dapat dipertanggungjawabkan.',
        },
    ];

    return (
        <PublicLayout>
            <Head title="FAQ & Rujukan Fikih Kurban - YuKurban" />

            <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div className="text-center mb-10">
                    <span className="text-xs font-bold text-green-600 bg-green-50 px-3 py-1 rounded-full uppercase">
                        Tanya Jawab
                    </span>
                    <h1 className="text-2xl sm:text-4xl font-extrabold text-green-900 mt-3">
                        Pertanyaan Umum & Rujukan Fikih
                    </h1>
                    <p className="text-xs sm:text-sm text-muted mt-2">
                        Penjelasan transparansi sistem, rujukan fatwa DSN-MUI, dan alur kurban amanah.
                    </p>
                </div>

                <div className="space-y-4">
                    {faqs.map((faq, idx) => {
                        const isOpen = openIndex === idx;
                        return (
                            <div
                                key={idx}
                                className="bg-white border border-border rounded-card overflow-hidden shadow-sm transition-all"
                            >
                                <button
                                    onClick={() => setOpenIndex(isOpen ? null : idx)}
                                    className="w-full p-5 text-left flex items-center justify-between gap-4 font-bold text-sm text-green-900 hover:text-green-700"
                                >
                                    <span>{faq.q}</span>
                                    <ChevronDown className={`w-5 h-5 text-muted transition-transform ${isOpen ? 'rotate-180 text-green-600' : ''}`} />
                                </button>
                                {isOpen && (
                                    <div className="px-5 pb-5 pt-1 text-xs sm:text-sm text-muted leading-relaxed border-t border-border/50">
                                        {faq.a}
                                    </div>
                                )}
                            </div>
                        );
                    })}
                </div>
            </div>
        </PublicLayout>
    );
}

