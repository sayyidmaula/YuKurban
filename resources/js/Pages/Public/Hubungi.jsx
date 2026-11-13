import React from 'react';
import { Head, useForm } from '@inertiajs/react';
import PublicLayout from '@/Layouts/PublicLayout';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import { Mail, MessageSquare, CheckCircle2 } from 'lucide-react';

export default function Hubungi({ success }) {
    const { data, setData, post, processing, errors, reset, recentlySuccessful } = useForm({
        name: '',
        email: '',
        message: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post('/hubungi', {
            onSuccess: () => reset(),
        });
    };

    return (
        <PublicLayout>
            <Head title="Hubungi Kami - YuKurban" />

            <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div className="text-center mb-10">
                    <span className="text-xs font-bold text-green-600 bg-green-50 px-3 py-1 rounded-full uppercase">
                        Kontak & Bantuan
                    </span>
                    <h1 className="text-2xl sm:text-4xl font-extrabold text-green-900 mt-3">
                        Hubungi Tim Pengelola
                    </h1>
                    <p className="text-xs sm:text-sm text-muted mt-2">
                        Punya pertanyaan seputar cara menabung, kemitraan peternak, atau alur kurban? Kirimkan pesan Anda kepada kami.
                    </p>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                    {/* Left Info Cards */}
                    <div className="space-y-4">
                        <div className="bg-white border border-border rounded-card p-5 shadow-sm">
                            <div className="w-10 h-10 rounded-btn bg-green-50 text-green-600 flex items-center justify-center mb-3">
                                <Mail className="w-5 h-5" />
                            </div>
                            <h3 className="font-bold text-sm text-green-900">Email Layanan</h3>
                            <p className="text-xs text-muted mt-1">bantuan@yukurban.test</p>
                            <p className="text-[11px] text-muted mt-2">Dibalas dalam 1x24 jam kerja</p>
                        </div>

                        <div className="bg-white border border-border rounded-card p-5 shadow-sm">
                            <div className="w-10 h-10 rounded-btn bg-gold-100 text-gold-600 flex items-center justify-center mb-3">
                                <MessageSquare className="w-5 h-5" />
                            </div>
                            <h3 className="font-bold text-sm text-green-900">Tiket Dalam Aplikasi</h3>
                            <p className="text-xs text-muted mt-1">Bagi pengguna terdaftar (penabung & peternak), gunakan fitur Tiket untuk bantuan cepat terkait pesanan atau hewan.</p>
                        </div>
                    </div>

                    {/* Right Form Card */}
                    <div className="md:col-span-2 bg-white border border-border rounded-card p-6 sm:p-8 shadow-sm">
                        {recentlySuccessful && (
                            <div className="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-btn flex items-center gap-3 text-xs sm:text-sm">
                                <CheckCircle2 className="w-5 h-5 flex-shrink-0 text-green-600" />
                                <span>Pesan Anda telah berhasil dikirim! Tim YuKurban akan segera menghubungi Anda.</span>
                            </div>
                        )}

                        <form onSubmit={submit} className="space-y-4">
                            <div>
                                <InputLabel htmlFor="name" value="Nama Lengkap" className="text-xs font-semibold text-text" />
                                <TextInput
                                    id="name"
                                    name="name"
                                    value={data.name}
                                    className="mt-1 block w-full"
                                    placeholder="Masukkan nama Anda"
                                    onChange={(e) => setData('name', e.target.value)}
                                    required
                                />
                                <InputError message={errors.name} className="mt-1 text-xs" />
                            </div>

                            <div>
                                <InputLabel htmlFor="email" value="Alamat Email" className="text-xs font-semibold text-text" />
                                <TextInput
                                    id="email"
                                    type="email"
                                    name="email"
                                    value={data.email}
                                    className="mt-1 block w-full"
                                    placeholder="contoh@domain.com"
                                    onChange={(e) => setData('email', e.target.value)}
                                    required
                                />
                                <InputError message={errors.email} className="mt-1 text-xs" />
                            </div>

                            <div>
                                <InputLabel htmlFor="message" value="Pesan atau Pertanyaan" className="text-xs font-semibold text-text" />
                                <textarea
                                    id="message"
                                    name="message"
                                    rows={4}
                                    value={data.message}
                                    onChange={(e) => setData('message', e.target.value)}
                                    placeholder="Tuliskan pertanyaan atau kebutuhan Anda di sini..."
                                    className="mt-1 block w-full rounded-btn border-border text-sm shadow-sm focus:border-green-600 focus:ring-green-600"
                                    required
                                />
                                <InputError message={errors.message} className="mt-1 text-xs" />
                            </div>

                            <div className="pt-2">
                                <PrimaryButton className="w-full sm:w-auto" disabled={processing}>
                                    {processing ? 'Mengirim...' : 'Kirim Pesan'}
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </PublicLayout>
    );
}

