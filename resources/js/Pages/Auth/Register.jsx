import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Sparkles, Store } from 'lucide-react';

export default function Register() {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        phone: '',
        role: 'penabung',
        password: '',
        password_confirmation: '',
    });

    const submit = (e) => {
        e.preventDefault();

        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <GuestLayout>
            <Head title="Pendaftaran Akun Baru" />

            <div className="mb-6 text-center">
                <h1 className="text-xl font-bold text-green-900">Daftar Akun Baru</h1>
                <p className="text-xs text-muted mt-1">Pilih peran Anda dan mulai rencanakan kurban amanah</p>
            </div>

            {/* Role Selector Tabs */}
            <div className="grid grid-cols-2 gap-2 p-1 bg-surface border border-border rounded-btn mb-6">
                <button
                    type="button"
                    onClick={() => setData('role', 'penabung')}
                    className={`flex items-center justify-center gap-2 py-2 px-3 rounded-btn text-xs font-semibold transition-all ${
                        data.role === 'penabung'
                            ? 'bg-green-600 text-white shadow-sm'
                            : 'text-muted hover:text-text'
                    }`}
                >
                    <Sparkles className="w-4 h-4" />
                    <span>Penabung Kurban</span>
                </button>
                <button
                    type="button"
                    onClick={() => setData('role', 'peternak')}
                    className={`flex items-center justify-center gap-2 py-2 px-3 rounded-btn text-xs font-semibold transition-all ${
                        data.role === 'peternak'
                            ? 'bg-gold-500 text-white shadow-sm'
                            : 'text-muted hover:text-text'
                    }`}
                >
                    <Store className="w-4 h-4" />
                    <span>Mitra Peternak</span>
                </button>
            </div>

            {data.role === 'peternak' && (
                <div className="mb-4 p-3 bg-gold-100/60 border border-gold-300 rounded-btn text-xs text-gold-600 leading-relaxed">
                    <strong>Catatan Peternak:</strong> Setelah pendaftaran, peternak perlu melengkapi data profil kandang untuk diverifikasi oleh tim admin sebelum hewan kurban dapat tayang di katalog.
                </div>
            )}

            <form onSubmit={submit} className="space-y-4">
                <div>
                    <InputLabel htmlFor="name" value={data.role === 'peternak' ? 'Nama Pemilik / Pengelola' : 'Nama Lengkap'} className="text-xs font-semibold text-text" />

                    <TextInput
                        id="name"
                        name="name"
                        value={data.name}
                        className="mt-1 block w-full"
                        autoComplete="name"
                        placeholder="Contoh: H. Abdullah"
                        isFocused={true}
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
                        autoComplete="username"
                        placeholder="contoh@domain.com"
                        onChange={(e) => setData('email', e.target.value)}
                        required
                    />

                    <InputError message={errors.email} className="mt-1 text-xs" />
                </div>

                <div>
                    <InputLabel htmlFor="phone" value="Nomor WhatsApp / HP (Opsional)" className="text-xs font-semibold text-text" />

                    <TextInput
                        id="phone"
                        type="tel"
                        name="phone"
                        value={data.phone}
                        className="mt-1 block w-full"
                        autoComplete="tel"
                        placeholder="081234567890"
                        onChange={(e) => setData('phone', e.target.value)}
                    />

                    <InputError message={errors.phone} className="mt-1 text-xs" />
                </div>

                <div>
                    <InputLabel htmlFor="password" value="Kata Sandi" className="text-xs font-semibold text-text" />

                    <TextInput
                        id="password"
                        type="password"
                        name="password"
                        value={data.password}
                        className="mt-1 block w-full"
                        autoComplete="new-password"
                        placeholder="Minimal 8 karakter"
                        onChange={(e) => setData('password', e.target.value)}
                        required
                    />

                    <InputError message={errors.password} className="mt-1 text-xs" />
                </div>

                <div>
                    <InputLabel
                        htmlFor="password_confirmation"
                        value="Konfirmasi Kata Sandi"
                        className="text-xs font-semibold text-text"
                    />

                    <TextInput
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        value={data.password_confirmation}
                        className="mt-1 block w-full"
                        autoComplete="new-password"
                        placeholder="Ulangi kata sandi"
                        onChange={(e) =>
                            setData('password_confirmation', e.target.value)
                        }
                        required
                    />

                    <InputError
                        message={errors.password_confirmation}
                        className="mt-1 text-xs"
                    />
                </div>

                <div className="pt-2">
                    <PrimaryButton className="w-full" disabled={processing}>
                        {processing ? 'Mendaftarkan Akun...' : `Daftar sebagai ${data.role === 'peternak' ? 'Peternak' : 'Penabung'}`}
                    </PrimaryButton>
                </div>

                <div className="pt-4 text-center text-xs text-muted border-t border-border mt-4">
                    Sudah memiliki akun?{' '}
                    <Link href="/masuk" className="font-semibold text-green-600 hover:text-green-700">
                        Masuk di sini
                    </Link>
                </div>
            </form>
        </GuestLayout>
    );
}
