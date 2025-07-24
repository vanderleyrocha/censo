import { Head, Link } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';

export default function Home() {
    return (
        <GuestLayout>
            <Head title="Censo Escolar - Acre" />
            
            {/* Banner */}
            <div className="bg-green-600 text-white">
                <div className="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8 text-center">
                    <h1 className="text-4xl font-extrabold tracking-tight sm:text-5xl lg:text-6xl">
                        Censo Escolar do Acre
                    </h1>
                    <p className="mt-6 max-w-3xl mx-auto text-xl">
                        Plataforma oficial para coleta de dados educacionais no estado
                    </p>
                </div>
            </div>

            {/* Seção de Informações */}
            <div className="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
                <div className="bg-white shadow rounded-lg p-6">
                    <h2 className="text-2xl font-bold text-gray-800 mb-6">Sobre o Censo Escolar no Acre</h2>
                    
                    <div className="prose max-w-none text-gray-600">
                        <p className="mb-4">
                            O Censo Escolar é o principal instrumento de coleta de informações da educação básica no estado do Acre,
                            sendo realizado anualmente em todas as escolas públicas e privadas.
                        </p>
                        
                        <p className="mb-4">
                            Os dados coletados servem de base para a distribuição de recursos do FUNDEB e para o planejamento
                            e implementação de políticas públicas na área da educação.
                        </p>
                        
                        <h3 className="text-xl font-semibold text-gray-800 mt-6 mb-3">Destaques do Acre</h3>
                        <ul className="list-disc pl-5 mb-6">
                            <li className="mb-2">1.523 escolas cadastradas</li>
                            <li className="mb-2">Cobertura de todos os 22 municípios</li>
                            <li className="mb-2">Dados atualizados anualmente</li>
                            <li>Indicadores educacionais detalhados</li>
                        </ul>
                        
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
                            <div className="bg-blue-50 p-4 rounded-lg border border-blue-100">
                                <h3 className="text-lg font-semibold text-blue-800 mb-2">Primeiro Acesso?</h3>
                                <p className="text-blue-600 mb-3">
                                    Cadastre-se para ter acesso ao sistema de preenchimento do censo.
                                </p>
                                <Link href={route('register')} className="inline-block bg-blue-100 hover:bg-blue-200 text-blue-800 px-4 py-2 rounded-md text-sm font-medium">
                                    <i className="fas fa-user-plus mr-1"></i> Criar Conta
                                </Link>
                            </div>
                            
                            <div className="bg-green-50 p-4 rounded-lg border border-green-100">
                                <h3 className="text-lg font-semibold text-green-800 mb-2">Já possui cadastro?</h3>
                                <p className="text-green-600 mb-3">
                                    Acesse o sistema para preencher os dados da sua escola.
                                </p>
                                <Link href={route('login')} className="inline-block bg-green-100 hover:bg-green-200 text-green-800 px-4 py-2 rounded-md text-sm font-medium">
                                    <i className="fas fa-sign-in-alt mr-1"></i> Fazer Login
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </GuestLayout>
    );
}