import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import Button from '@/Components/Button';

export default function ServidorShow({ servidor, auth }) {
    return (
        <AuthenticatedLayout
            headerTitle={`Detalhes do Servidor - ${servidor.nome}`}
            auth={auth}
        >
            <Head title={`Detalhes do Servidor - ${servidor.nome}`} />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 bg-white border-b border-gray-200">
                            <h2 className="text-2xl font-semibold text-gray-800 mb-6">Detalhes do Servidor</h2>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h3 className="text-lg font-medium text-gray-900 mb-4">Informações Pessoais</h3>
                                    <div className="space-y-4">
                                        <div>
                                            <label className="block text-sm font-medium text-gray-500">Nome Completo</label>
                                            <p className="mt-1 text-sm text-gray-900">{servidor.nome}</p>
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-500">Matrícula</label>
                                            <p className="mt-1 text-sm text-gray-900">{servidor.matricula}</p>
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-500">Email</label>
                                            <p className="mt-1 text-sm text-gray-900">{servidor.email}</p>
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-500">Usuário</label>
                                            <p className="mt-1 text-sm text-gray-900">{servidor.usuario}</p>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <h3 className="text-lg font-medium text-gray-900 mb-4">Informações Funcionais</h3>
                                    <div className="space-y-4">
                                        <div>
                                            <label className="block text-sm font-medium text-gray-500">Cargo</label>
                                            <p className="mt-1 text-sm text-gray-900">{servidor.cargo}</p>
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-500">Função</label>
                                            <p className="mt-1 text-sm text-gray-900">{servidor.funcao}</p>
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-500">Lotação</label>
                                            <p className="mt-1 text-sm text-gray-900">{servidor.lotacao}</p>
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-500">Contratos</label>
                                            <div className="mt-1 text-sm text-gray-900">
                                                <p>Contrato 1: {servidor.contrato1}</p>
                                                <p>Contrato 2: {servidor.contrato2}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Mostrar informações do usuário associado se existir */}
                            {servidor.user && (
                                <div className="mt-8">
                                    <h3 className="text-lg font-medium text-gray-900 mb-4">Usuário do Sistema Associado</h3>
                                    <div className="space-y-4">
                                        <div>
                                            <label className="block text-sm font-medium text-gray-500">Nome do Usuário</label>
                                            <p className="mt-1 text-sm text-gray-900">{servidor.user.name}</p>
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-500">Email do Usuário</label>
                                            <p className="mt-1 text-sm text-gray-900">{servidor.user.email}</p>
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-500">Funções</label>
                                            <p className="mt-1 text-sm text-gray-900">
                                                {servidor.user?.roles?.length > 0
                                                    ? servidor.user.roles.map(role => role.name).join(', ')
                                                    : 'Nenhuma função atribuída'}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            )}

                            <div className="mt-8">
                                <Link href={route('users.index')}
                                    className="text-gray-600 hover:text-gray-900"
                                >
                                    <Button color="secondary">
                                        Voltar
                                    </Button>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}