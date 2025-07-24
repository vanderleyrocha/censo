import { useState, useEffect } from 'react';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Register() {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const [touched, setTouched] = useState({
        name: false,
        email: false,
        password: false,
        password_confirmation: false
    });
    const [passwordStrength, setPasswordStrength] = useState(0);

    useEffect(() => {
        let strength = 0;
        if (data.password.length >= 8) strength++;
        if (/[A-Z]/.test(data.password)) strength++;
        if (/[0-9]/.test(data.password)) strength++;
        if (/[^A-Za-z0-9]/.test(data.password)) strength++;
        setPasswordStrength(strength);
    }, [data.password]);

    const isEmailValid = () => {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email);
    };

    const handleBlur = (field) => {
        setTouched({...touched, [field]: true});
    };

    const submit = (e) => {
        e.preventDefault();
        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <GuestLayout>
            <Head title="Register" />

            <div className="max-w-md mx-auto py-12 px-6 sm:px-8 bg-white rounded-lg shadow-md">
                <h2 className="text-2xl font-bold text-center text-gray-800 mb-8">Crie sua conta</h2>
                
                <form onSubmit={submit} className="space-y-6">
                    <div>
                        <InputLabel htmlFor="name" value="Nome Completo" />
                        <TextInput
                            id="name"
                            name="name"
                            value={data.name}
                            className="mt-2 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                            autoComplete="name"
                            isFocused={true}
                            onChange={(e) => setData('name', e.target.value)}
                            onBlur={() => handleBlur('name')}
                            aria-invalid={errors.name ? "true" : "false"}
                            aria-describedby="name-error"
                            required
                        />
                        <InputError id="name-error" message={errors.name} className="mt-1" />
                    </div>

                    <div>
                        <InputLabel htmlFor="email" value="E-mail" />
                        <div className="relative mt-2">
                            <TextInput
                                id="email"
                                type="email"
                                name="email"
                                value={data.email}
                                className="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                autoComplete="username"
                                onChange={(e) => setData('email', e.target.value)}
                                onBlur={() => handleBlur('email')}
                                aria-invalid={errors.email ? "true" : "false"}
                                aria-describedby="email-error"
                                required
                            />
                            {data.email && (
                                <span className={`absolute right-3 top-3 text-lg ${
                                    isEmailValid() ? 'text-green-500' : 'text-red-500'
                                }`}>
                                    {isEmailValid() ? '✓' : '✗'}
                                </span>
                            )}
                        </div>
                        <InputError id="email-error" message={errors.email} className="mt-1" />
                    </div>

                    <div>
                        <InputLabel htmlFor="password" value="Senha" />
                        <TextInput
                            id="password"
                            type="password"
                            name="password"
                            value={data.password}
                            className="mt-2 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                            autoComplete="new-password"
                            onChange={(e) => setData('password', e.target.value)}
                            onBlur={() => handleBlur('password')}
                            aria-invalid={errors.password ? "true" : "false"}
                            aria-describedby="password-error"
                            required
                        />
                        {data.password && (
                            <div className="mt-3">
                                <div className="flex gap-1.5">
                                    {[1, 2, 3, 4].map((i) => (
                                        <div 
                                            key={i}
                                            className={`h-2 flex-1 rounded-full ${
                                                passwordStrength >= i 
                                                    ? i > 2 ? 'bg-green-500' : i > 1 ? 'bg-yellow-500' : 'bg-red-500'
                                                    : 'bg-gray-200'
                                            }`}
                                        />
                                    ))}
                                </div>
                                <p className="text-xs mt-2 font-medium">
                                    {passwordStrength < 2 ? 'Senha fraca' : 
                                     passwordStrength < 4 ? 'Senha média' : 'Senha forte'}
                                </p>
                            </div>
                        )}
                        <InputError id="password-error" message={errors.password} className="mt-1" />
                    </div>

                    <div>
                        <InputLabel htmlFor="password_confirmation" value="Confirme sua senha" />
                        <TextInput
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            value={data.password_confirmation}
                            className="mt-2 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                            autoComplete="new-password"
                            onChange={(e) => setData('password_confirmation', e.target.value)}
                            onBlur={() => handleBlur('password_confirmation')}
                            aria-invalid={errors.password_confirmation ? "true" : "false"}
                            aria-describedby="password-confirmation-error"
                            required
                        />
                        {data.password && data.password_confirmation && (
                            <p className={`text-sm mt-2 font-medium ${
                                data.password === data.password_confirmation 
                                    ? 'text-green-600' 
                                    : 'text-red-600'
                            }`}>
                                {data.password === data.password_confirmation 
                                    ? '✓ As senhas coincidem' 
                                    : '✗ As senhas não coincidem'}
                            </p>
                        )}
                        <InputError id="password-confirmation-error" message={errors.password_confirmation} className="mt-1" />
                    </div>

                    <div className="flex items-center justify-between pt-2">
                        <Link
                            href={route('login')}
                            className="text-sm text-green-700 hover:text-green-800 font-medium underline focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 rounded"
                        >
                            Já possui uma conta? Faça login
                        </Link>

                        <PrimaryButton 
                            disabled={processing}
                            className="px-6 py-2 text-sm"
                        >
                            {processing ? (
                                <span className="flex items-center">
                                    <svg className="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                        <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Cadastrando...
                                </span>
                            ) : (
                                'Cadastrar'
                            )}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </GuestLayout>
    );
}