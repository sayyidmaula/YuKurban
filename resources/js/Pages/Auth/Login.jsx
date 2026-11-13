import Checkbox from '@/Components/Checkbox';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Login({ status, canResetPassword }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit = (e) => {
        e.preventDefault();

        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <GuestLayout>
            <Head title="Masuk ke Akun" />

            <div className="mb-6 text-center">
                <h1 className="text-xl font-bold text-green-900">Selamat Datang</h1>
                <p className="text-xs text-muted mt-1">Masuk untuk mengelola tabungan atau penjualan kurban</p>
            </div>

            {status && (
                <div className="mb-4 text-xs font-semibold text-green-600 bg-green-50 p-3 rounded-btn border border-green-100">
                    {status}
                </div>
            )}

            <form onSubmit={submit} className="space-y-4">
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
                        isFocused={true}
                        onChange={(e) => setData('email', e.target.value)}
                    />

                    <InputError message={errors.email} className="mt-1 text-xs" />
                </div>

                <div>
                    <div className="flex items-center justify-between">
                        <InputLabel htmlFor="password" value="Kata Sandi" className="text-xs font-semibold text-text" />
                        {canResetPassword && (
                            <Link
                                href={route('password.request')}
                                className="text-xs text-muted hover:text-green-700 underline"
                            >
                                Lupa kata sandi?
                            </Link>
                        )}
                    </div>

                    <TextInput
                        id="password"
                        type="password"
                        name="password"
                        value={data.password}
                        className="mt-1 block w-full"
                        autoComplete="current-password"
                        placeholder="••••••••"
                        onChange={(e) => setData('password', e.target.value)}
                    />

                    <InputError message={errors.password} className="mt-1 text-xs" />
                </div>

                <div className="flex items-center justify-between pt-1">
                    <label className="flex items-center cursor-pointer">
                        <Checkbox
                            name="remember"
                            checked={data.remember}
                            onChange={(e) => setData('remember', e.target.checked)}
                            className="rounded text-green-600 focus:ring-green-500"
                        />
                        <span className="ms-2 text-xs text-muted">
                            Ingat saya di perangkat ini
                        </span>
                    </label>
                </div>

                <div className="pt-2">
                    <PrimaryButton className="w-full" disabled={processing}>
                        {processing ? 'Memproses...' : 'Masuk Sekarang'}
                    </PrimaryButton>
                </div>

                <div className="pt-4 text-center text-xs text-muted border-t border-border mt-4">
                    Belum punya akun?{' '}
                    <Link href="/daftar" className="font-semibold text-green-600 hover:text-green-700">
                        Daftar sebagai Penabung / Peternak
                    </Link>
                </div>
            </form>
        </GuestLayout>
    );
}
