import React from 'react';
import { Head } from '@inertiajs/react';
import PublicLayout from '@/Layouts/PublicLayout';

export default function Ketentuan() {
    return (
        <PublicLayout>
            <Head title="Ketentuan Layanan - YuKurban" />

            <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div className="bg-white border border-border rounded-card p-6 sm:p-10 shadow-sm space-y-6">
                    <div className="border-b border-border pb-4">
                        <h1 className="text-2xl sm:text-3xl font-extrabold text-green-900">Ketentuan Layanan</h1>
                        <p className="text-xs text-muted mt-1">Berlaku per 11 November 2026</p>
                    </div>

                    <section className="space-y-3">
                        <h2 className="text-base font-bold text-green-900">1. Ketentuan Umum</h2>
                        <p className="text-xs text-muted leading-relaxed">
                            YuKurban adalah platform perantara yang mempertemukan Penabung Kurban, Peternak, dan Tim Pengelola. Seluruh transaksi finansial di dalam sistem prototipe ini berjalan dalam mode simulasi.
                        </p>
                    </section>

                    <section className="space-y-3">
                        <h2 className="text-base font-bold text-green-900">2. Dana Tabungan dan Ledger Append-Only</h2>
                        <p className="text-xs text-muted leading-relaxed">
                            Dana tabungan kurban dicatat dalam pembukuan berarah tunggal (append-only ledger). Setiap rupiah yang disetor maupun ditarik tidak dapat diubah atau dihapus, melainkan dikoreksi dengan baris transaksi baru yang terverifikasi. Dana tabungan tidak dipergunakan untuk kebutuhan operasional perusahaan.
                        </p>
                    </section>

                    <section className="space-y-3">
                        <h2 className="text-base font-bold text-green-900">3. Pemesanan dan Reservasi Hewan</h2>
                        <p className="text-xs text-muted leading-relaxed">
                            Pemesanan hewan kurban hanya dapat dilakukan apabila jendela pembelian untuk metode yang dipilih sedang berstatus dibuka dan saldo tabungan mencukupi. Pemesanan hewan akan mengunci hewan dalam masa reservasi 24 jam. Apabila tidak dikonfirmasi dalam waktu tersebut, reservasi akan kedaluwarsa secara otomatis.
                        </p>
                    </section>

                    <section className="space-y-3">
                        <h2 className="text-base font-bold text-green-900">4. Metode Sembelih Tim dan Akad Wakalah</h2>
                        <p className="text-xs text-muted leading-relaxed">
                            Pemilihan metode Sembelih Tim mewajibkan pembeli menyetujui akad perwakilan (wakalah bil ujrah) secara elektronik. Tim pengelola bertanggung jawab melaksanakan penyembelihan sesuai syariat Islam, mendokumentasikan bukti foto/video, serta menyalurkan daging kurban kepada yang berhak.
                        </p>
                    </section>

                    <section className="space-y-3">
                        <h2 className="text-base font-bold text-green-900">5. Pengaduan dan Laporan Masalah</h2>
                        <p className="text-xs text-muted leading-relaxed">
                            Pembeli dapat mengajukan laporan masalah dalam jangka waktu maksimal 48 jam sejak status hewan dinyatakan disalurkan atau diterima. Keputusan admin atas tiket bersifat mengikat sesuai fakta dan bukti lapangan.
                        </p>
                    </section>
                </div>
            </div>
        </PublicLayout>
    );
}

